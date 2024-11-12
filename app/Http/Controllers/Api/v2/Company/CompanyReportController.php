<?php

namespace App\Http\Controllers\Api\v2\Company;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Goal;
use App\Models\Employee;
use App\Models\Campaign;
use App\Models\Assessment\CroxxAssessment;
use App\Models\Training\CroxxTraining;
use App\Models\EmployerJobcode as Department;
use App\Models\Competency\DepartmentMapping as CompentencyMapping;
use App\Models\Assessment\EmployerAssessmentFeedback;
use App\Models\Assessment\EmployeeLearningPath;
use App\Services\RefreshCompanyPerformance;

class CompanyReportController extends Controller
{

    protected $companyPerformanceService;

    public function __construct(RefreshCompanyPerformance $companyPerformanceService){
        $this->companyPerformanceService = $companyPerformanceService;
    }

    public function overview(Request $request){
        $employer = $request->user();
        $competency_matched  = CompentencyMapping::where('employer_id', $employer->id)
                                    ->whereNull('archived_at')->count();

        $employees = Employee::where('employer_id', $employer->id)
                     ->select(['id', 'gender'])->get();

        $top_employees = Employee::where('employer_id', $employer->id)
                            ->select(['id', 'name', 'photo_url','code', 'performance'])
                            ->orderBy('performance', 'desc')
                            ->limit(12)->get();

        $total_campaigns = Campaign::where('user_id', $employer->id)->count();
        $total_employees = $employees->count();

        $gender_distribution = $employees->groupBy('gender')
            ->reduce(function ($carry, $group) use ($employees) {
            $count = $group->count();
            $total = $employees->count();

            // Handle empty or null genders by setting them to 'others'
            $gender = strtolower($group->first()->gender) ?: 'others';
            $carry[$gender] = $count;

            return $carry;
        }, ['male' => 0, 'female' => 0, 'others' => 0]);

        // Retrieve total goals (tasks) completed
        $totalGoalsCompleted = Goal::where('employer_id', $employer->id)
                ->where('status', 'done')->whereNull('archived_at')
                ->count();

        // Retrieve goals (tasks) completed this month
        $currentMonthGoalsCompleted = Goal::where('employer_id', $employer->id)
                ->where('status', 'done')->whereNull('archived_at')
                ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
                ->count();

        // Retrieve goals (tasks) completed last month
        $lastMonthGoalsCompleted = Goal::where('employer_id', $employer->id)
                ->where('status', 'done')->whereNull('archived_at')
                ->whereBetween('created_at', [  now()->subMonth()->startOfMonth(),now()->subMonth()->endOfMonth()])
                ->count();

        $differences = $currentMonthGoalsCompleted - $lastMonthGoalsCompleted;
        $totalPerformance = $employees->sum('performance');
        $averagePerformance = $total_employees > 0 ? $totalPerformance / $total_employees : 0;

        $competency_summary = [
            'current_rating' => $averagePerformance,
            'task_completed' => $currentMonthGoalsCompleted,
            'differences' => $differences,
            'summary' => $differences > 0
                ? "Improved by $differences tasks this month."
                : ($differences < 0
                    ? "Completed " . abs($differences) . " fewer tasks than last month."
                    : "Performance is consistent with last month."),
        ];

        $data = compact(
                    'total_employees','total_campaigns', 'competency_matched', 'competency_summary',
                    'gender_distribution', 'top_employees'
                );

        return response()->json([
            'status' => true,
            'data' => $data,
            'message' => ''
        ], 200);

    }

    public function departmentOverview(Request $request){
        $employer = $request->user();
        $per_page = $request->input('per_page', 12);
        $default_department = $request->input('department') ?? $employer->default_company_id;

        if($request->input('department')) {
            $employer->default_company_id = $request->input('department');
            $employer->save();
        }

        $department = Department::find($default_department) ?? Department::where('employer_id', $employer->id)->first();

        if(!$department){
            return response()->json([
                'status' => true,
                'data' => [
                    'title' => '',
                    'technical_distribution' => [],
                    'softskill_distribution' => [],
                ],
                'message' => ''
            ], 200);
        }

        $all_skills = array_merge(
            $department->technical_skill->toArray(0),
            $department->soft_skill->toArray(0)
        );

        $assessment_distribution = [];
        $training_distribution = [];
        $categories = [];

        if (count($all_skills)) {
            foreach ($all_skills as $skill) {
                $categories[] = $skill['competency'];
                $assessment_distribution[] = $skill['performance'] ?? 0; // Adjust based on your data structure
                $training_distribution[] = $skill['training'] ?? 0; // Adjust based on your data structure
            }
        }

        // Now split the distributions into technical and soft skill
        $technical_skills = array_column($department->technical_skill->toArray(0), 'competency');
        $soft_skills = array_column($department->soft_skill->toArray(0), 'competency');

        $technical_distribution = [
            'categories' => $technical_skills,
            'assessment_distribution' => array_slice($assessment_distribution, 0, count($technical_skills)),
            'trainings_distribution' => array_slice($training_distribution, 0, count($technical_skills)),
        ];

        $softskill_distribution = [
            'categories' => $soft_skills,
            'assessment_distribution' => array_slice($assessment_distribution, count($technical_skills)),
            'trainings_distribution' => array_slice($training_distribution, count($technical_skills)),
        ];

        $title = $department->job_code;
        $data = compact('title', 'technical_distribution', 'softskill_distribution');

        return response()->json([
            'status' => true,
            'data' => $data,
            'message' => ''
        ], 200);

    }

    public function recentFeedback(Request $request){
        $employer = $request->user();
        $per_page = $request->input('per_page', 12);

        $feedbacks = EmployerAssessmentFeedback::where('employer_user_id', $employer->id)
                        ->where('is_published', true)->whereNotNull('supervisor_id')
                        ->with('employee','supervisor', 'assessment')
                        ->latest();

        $feedbacks = $feedbacks->paginate($per_page);
                        // ->limit(8) ->get();

        return response()->json([
            'status' => true,
            'data' => $feedbacks,
            'message' => ''
        ], 200);
    }

    public function assessmentChart(Request $request){
        $employer = $request->user();

       // Initialize arrays to hold departments and datasets
        $departments = [];
        $datasets = [];

        $department_assessments = Department::join('croxx_assessments', 'employer_jobcodes.id', '=', 'croxx_assessments.department_id')
            ->where('croxx_assessments.employer_id', $employer->id)
            ->select('croxx_assessments.id', 'croxx_assessments.employer_id', 'croxx_assessments.level', 'croxx_assessments.is_published', 'croxx_assessments.expected_percentage', 'employer_jobcodes.job_code')
            ->get();

        foreach ($department_assessments as $item) {
            $item->department_score = EmployerAssessmentFeedback::where('assessment_id', $item->id)->avg('graded_score');
        }

        // Initialize datasets array with level keys
        $levels = ['beginner', 'intermediate', 'advance', 'expert'];
        foreach ($levels as $level) {
            $datasets[$level] = [
                'label' => $level,
                'data' => []
            ];
        }

        // Populate departments and datasets arrays
        foreach ($department_assessments as $assessment) {
            if (!in_array($assessment->job_code, $departments)) {
                $departments[] = $assessment->job_code;
            }

            // Initialize data array for each level if not already set
            if (!isset($datasets[$assessment->level]['data'])) {
                $datasets[$assessment->level]['data'] = array_fill(0, count($departments), null);
            }

            // Find the index of the current job_code
            $jobCodeIndex = array_search($assessment->job_code, $departments);

            // Set the score for the appropriate level and job_code
            $datasets[$assessment->level]['data'][$jobCodeIndex] = $assessment->department_score;
        }

        // Fill null values for levels that don't have scores for some job codes
        foreach ($datasets as $level => &$dataset) {
            for ($i = 0; $i < count($departments); $i++) {
                if (!isset($dataset['data'][$i])) {
                    $dataset['data'][$i] = 0;
                }
            }
        }

        $chartData = [
            'labels' => $departments,
            'datasets' => array_values($datasets),
        ];

        return response()->json([
            'status' => true,
            'data' => $chartData,
            'message' => ''
        ], 200);
    }

    public function coursesChart(Request $request){
        $employer = $request->user();

        $takenTrainings = CroxxTraining::select('croxx_trainings.id', 'croxx_trainings.title', DB::raw('COUNT(employee_learning_paths.id) AS taken_count'))
                        ->join('employee_learning_paths', 'croxx_trainings.id', '=', 'employee_learning_paths.training_id')
                        ->where('croxx_trainings.user_id', $employer->id)
                        ->where('croxx_trainings.type', 'company')
                        ->groupBy('croxx_trainings.id', 'croxx_trainings.title')
                        ->orderBy('taken_count', 'DESC') // Order by taken_count in descending order (most taken first)
                        ->limit(10) // Limit to top 10 most taken trainings
                        ->get();

        $chartData = [
            'labels' => $takenTrainings->pluck('title')->toArray(),
            'datasets' => $takenTrainings->pluck('taken_count')->toArray(),
        ];

        return response()->json([
            'status' => true,
            'data' => $chartData,
            'message' => ''
        ], 200);
    }


    protected function refreshPerformance(Request $request)
    {
        $employer = $request->user();
        $perPage = $request->input('per_page', 12);
        $period = [now()->startOfMonth(), now()->endOfMonth()];
        $default_department = $request->input('department');

        $competencies = $this->companyPerformanceService->refreshCompetenciesPerformance($employer);
        $employees = $this->companyPerformanceService->refreshEmployeesPerformance($employer);

        return response()->json([
            'status' => true,
            'data' => compact('employees', 'competencies'),
            'message' => 'Company perfoemance refreshed'
        ], 200);
    }

}

 // $employees = Employee::where('employer_id', $employer->id)
        //                     ->where('job_code_id', $department->id)
        //                     ->paginate($per_page);

        // $employeeIds =  $employees->pluck('id');
        // $goals = Goal::whereIn('employee_id', $employeeIds)
        //                     ->orderBy('created_at', 'desc')
        //                     ->limit(3)->get();

        // $groupedGoals = $goals->groupBy('employee_id');

         // Map employee details and goals
        // $department_goals = $employees->map(function ($employee) use ($groupedGoals) {
        //     return [
        //         'employee' => $employee,
        //         'goals' => $groupedGoals->get($employee->id, collect()), // Default to empty collection if no goals
        //     ];
        // });department_goals
