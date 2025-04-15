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
use App\Models\Competency\CompetencySetup;
use App\Models\Competency\DepartmentMapping;

class ReportAnalysisController extends Controller
{

        // private function notifyAssignedUsers($employeeInstances, $supervisorInstances, $assessment)
    // {
    //     // Notify employees
    //     if (!empty($employeeInstances)) {
    //         $employees = collect();

    //         foreach ($employeeInstances as $assignedEmployee) {
    //             $employee = Employee::find($assignedEmployee->employee_id);
    //             if ($employee) {
    //                 $employees->push($employee);
    //             }
    //         }

    //         if ($employees->isNotEmpty()) {
    //             // Send batch notifications to employees
    //             foreach ($employees as $employee) {
    //                 if($employee->talent)  Notification::send($employee->talent, new AssessmentPublishedNotification($assessment, $employee, 'employee'));
    //             }
    //         }
    //     }

    //     // Notify supervisors
    //     if (!empty($supervisorInstances)) {
    //         $supervisors = collect();

    //         foreach ($supervisorInstances as $assignedEmployee) {
    //             $supervisor = Employee::find($assignedEmployee->employee_id);
    //             if ($supervisor) {
    //                 $supervisors->push($supervisor);
    //             }
    //         }

    //         if ($supervisors->isNotEmpty()) {
    //             // Send batch notifications to supervisors
    //             foreach ($supervisors as $supervisor) {
    //                if($supervisor->talent) Notification::send($supervisor->talent, new AssessmentPublishedNotification($assessment, $supervisor, 'supervisor'));
    //             }
    //         }
    //     }
    // }

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

        // Get expected scores for each competency (assuming max score is 5)
        $expectedScores = $department->technical_skill->pluck('expected_score', 'id')
            ->map(function($score) {
                return $score ?? 10;
            })->toArray();

        $employees = Employee::where('employer_id', $employer->id)
            ->where('job_code_id', $department->id)
            ->get();

        $employeeData = [];
        $departmentAverages = array_fill_keys($competenciesIds->toArray(), 0);
        $competencyGaps = [];

        foreach ($employees as $employee) {
            $employeeAssessments = CroxxAssessment::whereHas('competencies', function ($query) use ($competenciesIds) {
                $query->whereIn('competency_id', $competenciesIds);
            })->with(['feedbacks' => function ($query) use ($employee) {
                $query->where('employee_id', $employee->id);
            }])->get();

            $scores = [];
            $gaps = [];

            foreach ($competenciesIds as $competencyId) {
                $feedback = $employeeAssessments->map->feedbacks->flatten()
                    ->firstWhere('competency_id', $competencyId);

                $actualScore = $feedback ? $feedback->graded_score : 0;
                $expectedScore = $expectedScores[$competencyId];

                $scores[$competencyId] = $actualScore;
                $gaps[$competencyId] = max(0, $expectedScore - $actualScore);

                // Add to department averages
                $departmentAverages[$competencyId] += $actualScore;
            }

            $employeeData[] = [
                'employee_id' => $employee->id,
                'name' => $employee->name,
                'scores' => $scores,
                'gaps' => $gaps,
                'average_score' => array_sum($scores) / count($scores),
                'total_gap' => array_sum($gaps),
                // 'development_needs' => $this->identifyDevelopmentNeeds($gaps, $competencies)
            ];
        }

        // Calculate department averages and gaps
        $employeeCount = count($employees);
        foreach ($departmentAverages as $competencyId => &$total) {
            $total = $employeeCount > 0 ? $total / $employeeCount : 0;
            $competencyGaps[$competencyId] = max(0, $expectedScores[$competencyId] - $total);
        }

        return [
            // 'department' => [
            //     'name' => $department->name,
            //     'averages' => $departmentAverages,
            //     'gaps' => $competencyGaps
            // ],
            'competencies' => $competencies,
            'expected_scores' => $expectedScores,
            'employees' => $employeeData,
            // 'summary' => $this->generateGapAnalysisSummary($employeeData, $competencies)
        ];
    }

    private function identifyDevelopmentNeeds(array $gaps, array $competencies)
    {
        $needs = [];
        $threshold = 2; // Consider gaps >= 2 as significant development needs

        foreach ($gaps as $competencyId => $gap) {
            if ($gap >= $threshold) {
                $needs[] = [
                    'competency' => $competencies[$competencyId],
                    'gap' => $gap,
                    'priority' => $this->calculatePriority($gap)
                ];
            }
        }

        return collect($needs)->sortByDesc('gap')->values()->all();
    }

    private function calculatePriority($gap)
    {
        if ($gap >= 4) return 'High';
        if ($gap >= 2) return 'Medium';
        return 'Low';
    }

    private function generateGapAnalysisSummary($employeeData, $competencies)
    {
        $totalEmployees = count($employeeData);
        $criticalGaps = [];
        $strengthAreas = [];

        // Analyze overall department performance
        foreach ($competencies as $id => $competency) {
            $totalGap = 0;
            $employeesWithGap = 0;

            foreach ($employeeData as $employee) {
                if (isset($employee['gaps'][$id]) && $employee['gaps'][$id] > 0) {
                    $totalGap += $employee['gaps'][$id];
                    $employeesWithGap++;
                }
            }

            $averageGap = $totalEmployees > 0 ? $totalGap / $totalEmployees : 0;

            if ($averageGap >= 2) {
                $criticalGaps[] = [
                    'competency' => $competency,
                    'average_gap' => $averageGap,
                    'affected_percentage' => ($employeesWithGap / $totalEmployees) * 100
                ];
            }

            if ($averageGap <= 1) {
                $strengthAreas[] = [
                    'competency' => $competency,
                    'average_gap' => $averageGap
                ];
            }
        }

        return [
            'critical_gaps' => $criticalGaps,
            'strength_areas' => $strengthAreas,
            'overall_status' => $this->calculateOverallStatus($criticalGaps)
        ];
    }

    private function calculateOverallStatus($criticalGaps)
    {
        $criticalCount = count($criticalGaps);

        if ($criticalCount === 0) return 'Excellent';
        if ($criticalCount <= 2) return 'Good';
        if ($criticalCount <= 4) return 'Needs Improvement';
        return 'Critical';
    }
}
