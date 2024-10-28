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
        $expectedScores = array_fill_keys($competenciesIds->toArray(), 10);

        $employees = Employee::where('employer_id', $employer->id)
                            ->where('job_code_id', $department->id)
                            ->get();

        $employeeData = [];
        foreach ($employees as $employee) {
            $scores = [];
            $gaps = [];

            foreach ($competenciesIds as $competencyId) {
                // Get all assessments for this competency
                $assessments = CroxxAssessment::whereHas('competencies', function ($query) use ($competencyId) {
                    $query->where('competency_id', $competencyId);
                })->with(['feedbacks' => function ($query) use ($employee) {
                    $query->where('employee_id', $employee->id)
                          ->where('is_published', 1)
                          ->orderBy('created_at', 'desc');
                }])->get();

                // Calculate average score across all assessments for this competency
                $totalScore = 0;
                $assessmentCount = 0;

                foreach ($assessments as $assessment) {
                    $feedback = $assessment->feedbacks->first();
                    if ($feedback) {
                        $totalScore += ($feedback->graded_score / 100) * 10; // Convert to 0-10 scale
                        $assessmentCount++;
                    }
                }

                $actualScore = $assessmentCount > 0 ? $totalScore / $assessmentCount : 0;
                $expectedScore = $expectedScores[$competencyId];

                $gap = max(0, min(10, $expectedScore - $actualScore));

                $scores[] = round($actualScore, 2);
                $gaps[] = round($gap, 2);
            }

            $employeeData[] = [
                'name' => $employee->name,
                'data' => $scores,
                'gaps' => $gaps,
            ];
        }

        return response()->json([
            'status' => true,
            'data' => compact('competencies', 'employeeData'),
            'message' => ''
        ], 200);
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
