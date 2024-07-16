<?php

namespace App\Http\Controllers\Api\v2\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Training\CroxxTraining;
use App\Models\Employee;
use App\Models\Campaign;
use App\Models\EmployerJobcode as Department;
use App\Models\Competency\DepartmentMapping as CompentencyMapping;
use App\Models\Goal;


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

}
