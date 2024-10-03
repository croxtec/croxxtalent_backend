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

class CompanyReportController extends Controller
{

    public function overview(Request $request){
        $employer = $request->user();
        $competency_matched  = CompentencyMapping::where('employer_id', $employer->id)
                                    ->whereNull('archived_at')->count();

        $employees = Employee::where('employer_id', $employer->id)
                     ->select(['id', 'gender'])->get();

        $top_employees = Employee::where('employer_id', $employer->id)
                            ->select(['id', 'name', 'photo_url','code', 'performance'])
                            ->orderBy('performance', 'asc')
                            ->limit(12)->get();

        $total_campaigns = Campaign::where('user_id', $employer->id)->count();

        $total_employees = $employees->count();

        $gender_distribution = $employees->groupBy('gender')
        ->reduce(function ($carry, $group) use ($employees) {
            $count = $group->count();
            $total = $employees->count();

            // $carry[$group->first()->gender] = $total > 0
            //     ? round(($count / $total) * 100, 2) . '%'
            //     : '0%';

            $gender = strtolower($group->first()->gender);
            $carry[$gender] = $count;

            return $carry;
        }, ['male' => 0, 'female' => 0, 'others' => 0]);


        $competency_summary = [
            'current_rating' => 0,
            'task_completed' => 0,
            'diffrences' => 0,
            'summary' => "",
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

        $technical_skills = array_column($department->technical_skill->toArray(0),'competency');
        $assessment_distribution = [];
        if(count($technical_skills)){
            foreach($technical_skills as $skill){
                array_push($assessment_distribution, mt_rand(0, 100));
            }
        }

        $technical_distribution = [
            'categories' => $technical_skills,
            'assessment_distribution' =>  $assessment_distribution,
            'trainings_distribution' =>  $assessment_distribution,
        ];

        $soft_skills = array_column($department->soft_skill->toArray(0),'competency');
        $softskill_distribution = [
            'categories' => $soft_skills,
            'assessment_distribution' =>  $assessment_distribution,
            'trainings_distribution' =>  $assessment_distribution,
        ];

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

        $feedbacks = EmployerAssessmentFeedback::where('employer_user_id', $employer->id)
                        ->with('employee','supervisor')
                        ->latest()->limit(8) ->get();

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

        // Fetch the department assessments
        $department_assessments = Department::join('croxx_assessments', 'employer_jobcodes.id', '=', 'croxx_assessments.department_id')
            ->where('croxx_assessments.employer_id', $employer->id)
            ->select('croxx_assessments.id', 'croxx_assessments.employer_id', 'croxx_assessments.level', 'croxx_assessments.is_published', 'croxx_assessments.expected_percentage', 'employer_jobcodes.job_code')
            ->get();

        // Calculate department scores and prepare data for charting
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
                    $dataset['data'][$i] = null;
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

    public function gapAnalysisReport(Request $request)
    {
        $employer = $request->user();
        $perPage = $request->input('per_page', 12);
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
                    'competencies' => [],
                    'employeeData' => []
                ],
                'message' => ''
            ], 200);
        }

        $competenciesIds = $department->technical_skill->pluck('id');
        $competencies = $department->technical_skill->pluck('competency')->toArray();

        $employees = Employee::where('employer_id', $employer->id)
            ->where('job_code_id', $department->id)
            ->get();

        $employeeData = [];

        foreach ($employees as $employee) {
            $employeeAssessments = CroxxAssessment::whereHas('competencies', function ($query) use ($competenciesIds) {
                $query->whereIn('competency_id', $competenciesIds);
            })->with(['feedbacks' => function ($query) use ($employee) {
                $query->where('employee_id', $employee->id);
            }])->get();

            $scores = [];

            foreach ($competenciesIds as $competencyId) {
                $feedback = $employeeAssessments->map->feedbacks->flatten()->firstWhere('competency_id', $competencyId);
                $scores[] = $feedback ? $feedback->graded_score : 0;
            }

            $employeeData[] = [
                'name' => $employee->name,
                'data' => $scores
            ];
        }

        return response()->json([
            'status' => true,
            'data' => compact('competencies', 'employeeData'),
            'message' => ''
        ], 200);
    }

    protected function getDepartment(Request $request, $employer)
    {
        $default_department = $request->input('department') ?? $employer->default_company_id;
        return Department::findOrFail($default_department) ?? Department::where('employer_id', $employer->id)->firstOrFail();
    }

    protected function getCompetencyIds($department)
    {
        return $department->technical_skill->pluck('id');
    }

    protected function getEmployees($employer, $department, $per_page)
    {
        return Employee::where('employer_id', $employer->id)
                    ->where('job_code_id', $department->id)
                    ->paginate($per_page);
    }

    protected function getAssessmentsByCompetencies($competenciesIds)
    {
        return CroxxAssessment::with(['feedbacks' => function($query) {
            $query->select('assessment_id', 'employee_id', 'graded_score');
        }])->whereHas('competencies', function ($query) use ($competenciesIds) {
            $query->whereIn('competency_id', $competenciesIds);
        })->get();
    }

    protected function generateGapAnalysisData($employees, $assessments)
    {
        $gapAnalysisData = [];

        foreach ($employees as $employee) {
            $employeeData = [
                'employee_id' => $employee->id,
                'employee_name' => $employee->name,
                'assessments' => $this->getEmployeeAssessments($employee, $assessments)
            ];

            $gapAnalysisData[] = $employeeData;
        }

        return $gapAnalysisData;
    }

    protected function getEmployeeAssessments($employee, $assessments)
    {
        $employeeAssessments = [];

        foreach ($assessments as $assessment) {
            $feedback = $assessment->feedbacks->where('employee_id', $employee->id)->first();
            if ($feedback) {
                $employeeAssessments[] = [
                    'assessment_id' => $assessment->id,
                    'graded_score' => $feedback->graded_score
                ];
            }
        }

        return $employeeAssessments;
    }

}
