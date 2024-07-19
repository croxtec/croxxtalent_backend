<?php

namespace App\Http\Controllers\Api\v2\Company;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Goal;
use App\Models\Employee;
use App\Models\Campaign;
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
                            ->select(['id', 'name', 'code', 'performance'])
                            ->orderBy('performance', 'asc')
                            ->limit(12)->get();

        $total_campaigns = Campaign::where('user_id', $employer->id)->count();

        $total_employees = $employees->count();
        $genderCount = $employees->groupBy('gender')->map(function ($row) {
            return count($row);
        });

        $gender_percentage = $genderCount->map(function ($count) use ($total_employees) {
            return round(($count / $total_employees) * 100, 2) . '%';
        });

        $gender_distribution = $gender_percentage->toArray();

        $competency_summary = [
            'current_rating' => 70,
            'task_completed' => 12,
            'diffrences' => 10,
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

    public function summary(Request $request){
        $employer = $request->user();
        $default_department = $request->input('department') ?? $employer->default_company_id;
        $per_page = $request->input('per_page', 12);

        if($request->input('department')) {
            $employer->default_company_id = $request->input('department');
            $employer->save();
        }

        $department = Department::findOrFail($default_department) ?? Department::where('employer_id', $employer->id)->firstOrFail();

        $technical_skills = array_column($department->technical_skill->toArray(0),'competency');
        $assessment_distribution = [];
        if(count($technical_skills)){
            foreach($technical_skills as $skill){
                array_push($assessment_distribution, mt_rand(0, 10));
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

        $employees = Employee::where('employer_id', $employer->id)
                            ->where('job_code_id', $department->id)
                            ->paginate($per_page);

        $employeeIds =  $employees->pluck('id');
        $goals = Goal::whereIn('employee_id', $employeeIds)
                            ->orderBy('created_at', 'desc')
                            ->limit(3)->get();

        $groupedGoals = $goals->groupBy('employee_id');

         // Map employee details and goals
        $department_goals = $employees->map(function ($employee) use ($groupedGoals) {
            return [
                'employee' => $employee,
                'goals' => $groupedGoals->get($employee->id, collect()), // Default to empty collection if no goals
            ];
        });

        $data = compact('technical_distribution', 'softskill_distribution','department_goals');

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

    public function gapAnalysisReport(Request $request){
        $employer = $request->user();

        $groups = array();
        $department = $request->input('department');
        $per_page = $request->input('per_page', 12);

        $assessmentGap = Employee::join('employer_assessment_feedback', 'employees.id', '=', 'employer_assessment_feedback.employee_id')
        ->where('employees.employer_id', $employer->id)
        ->select('employees.id', 'employees.name', 'employees.status', 'employees.performance', 'employer_assessment_feedback.employee_id', 'employer_assessment_feedback.graded_score')
        ->when($department, function ($query) use ($department) {
            // Filter by department
            $query->where('employees.job_code_id', $department);
        })
        ->paginate($per_page);

        $categories = [];
        $series = [];

        foreach ($assessmentGap as $gap) {
            $employeeName = $gap['name'];
            $score = $gap['graded_score'];

            // Add employee name to categories if not already present
            if (!in_array($employeeName, $categories)) {
                $categories[] = $employeeName;
                $series[$employeeName] = []; // Initialize an empty series for the employee
            }

            // Add score to the employee's series
            $series[$employeeName][] = $score;
        }
        $alternative = array_values($series);
        $chartData = compact('categories', 'series', 'alternative');

        return response()->json([
            'status' => true,
            'data' => $chartData,
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
        $levels = ['beginner', 'intermediate', 'advanced', 'expert'];
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

        // Prepare the final chart data
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
                        ->where('croxx_trainings.user_id', $employer->id)
                        ->where('croxx_trainings.type', 'company')
                        ->join('employee_learning_paths', 'croxx_trainings.id', '=', 'employee_learning_paths.training_id')
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
}
