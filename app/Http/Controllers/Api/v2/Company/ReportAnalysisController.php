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

class ReportAnalysisController extends Controller
{
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

}
