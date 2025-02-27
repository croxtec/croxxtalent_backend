<?php

namespace App\Http\Controllers\Api\v2\Company;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Goal;
use App\Models\Employee;
use App\Models\Campaign;
use App\Models\Assessment\CroxxAssessment;
use App\Models\EmployerJobcode as Department;

class ReportAnalysisController extends Controller
{
    protected $assessmentService;
    protected $reportingService;

    public function __construct(
        // ReportingService $reportingService
    ) {
        // $this->reportingService = $reportingService;
    }

    public function gapAnalysisReport001(Request $request)
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

    public function gapAnalysisReport(Request $request)
    {
        try {
            $employer = $request->user();
            $departmentId = $this->getDepartmentId($request, $employer);

            if (!$departmentId) {
                return $this->emptyResponse();
            }

            $department = Department::with('technical_skill')->find($departmentId);
            $competencyData = $this->getCompetencyData($department);
            $employeeData = $this->calculateEmployeeGaps($employer, $department, $competencyData);

            $summary = $this->generateAnalysisSummary($employeeData, $competencyData);

            return response()->json([
                'status' => true,
                'data' => [
                    'competencies' => $competencyData['names'],
                    'employeeData' => $employeeData,
                    'summary' => $summary,
                    'department' => $department->only(['id', 'job_code', 'job_title']),
                    'total_employees' => count($employeeData)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Error generating gap analysis: " . $e->getMessage()
            ], 500);
        }
    }

    public function getTeamCompetencyGap(Request $request)
    {
        try {
            $employer = $request->user();
            $employeeIds = $request->input('employees', []);

            // If single employee is passed, convert to array
            if ($request->has('employee')) {
                $employeeIds = [$request->input('employee')];
            }

            // Validate we have employees to process
            if (empty($employeeIds)) {
                return response()->json([
                    'status' => false,
                    'message' => 'No employees specified'
                ], 400);
            }

            $results = [];

            foreach ($employeeIds as $employeeId) {
                $employee = Employee::with('department.technical_skill', 'department.soft_skill')
                    ->where('code', $employeeId)
                    ->where('employer_id', $employer->id)
                    ->first();

                if (!$employee) {
                    continue; // Skip invalid employees
                }

                $department = $employee->department;
                $technicalCompetencies = $department->technical_skill;
                $softSkillCompetencies = $department->soft_skill;

                // Get all competency IDs
                $competencyIds = $technicalCompetencies->pluck('id')
                    ->concat($softSkillCompetencies->pluck('id'))
                    ->toArray();

                // Get assessments with proper relationship loading
                $assessments = CroxxAssessment::whereHas('competencies', function ($query) use ($competencyIds) {
                    $query->whereIn('competency_id', $competencyIds);
                })
                ->with([
                    'competencies',
                    'feedbacks' => function ($query) use ($employee) {
                        $query->where('employee_id', $employee->id)
                            ->where('is_published', 1)
                            ->orderBy('created_at', 'desc');
                    }
                ])
                ->get();

                $technicalGaps = [];
                $softSkillGaps = [];

                // Process technical competencies
                foreach ($technicalCompetencies as $competency) {
                    $gapData = $this->calculateCompetencyGap($competency, $assessments, $employee->level);
                    if ($gapData) {
                        $technicalGaps[] = $gapData;
                    }
                }

                // Process soft skill competencies
                foreach ($softSkillCompetencies as $competency) {
                    $gapData = $this->calculateCompetencyGap($competency, $assessments, $employee->level);
                    if ($gapData) {
                        $softSkillGaps[] = $gapData;
                    }
                }

                $allGaps = array_merge($technicalGaps, $softSkillGaps);

                $results[] = [
                    'employee_id' => $employeeId,
                    'competency_gaps' => [
                        'technical' => $technicalGaps,
                        'soft_skill' => $softSkillGaps
                    ],
                    'average_gap' => round(collect($allGaps)->avg('gap'), 2),
                    'total_competencies' => count($allGaps),
                    'assessment_count' => $assessments->count(),
                ];
            }

            return response()->json([
                'status' => true,
                'data' => count($results) === 1 ? $results[0] : $results
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Error retrieving competency gaps: " . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper method to calculate competency gap for a specific competency
     */
    private function calculateCompetencyGap($competency, $assessments, $employeeLevel)
    {
        // Find relevant assessments for this competency
        $relevantAssessments = $assessments->filter(function ($assessment) use ($competency) {
            return $assessment->competencies->contains('id', $competency->id);
        });

        if ($relevantAssessments->isEmpty()) {
            return null;
        }

        // Calculate average scores and gaps
        $scores = $relevantAssessments->map(function ($assessment) {
            return $assessment->feedbacks->first()->graded_score ?? 0;
        });

        $averageScore = $scores->avg() ?? 0;
        $expectedScore = $relevantAssessments->first()->expected_percentage ?? 100;
        $gap = max(0, $expectedScore - $averageScore);

        // Check if competency is core (same level as employee)
        $isCore = ($competency->level == $employeeLevel);

        return [
            'competency_name' => $competency->competency,
            'competency_description' => $competency->description,
            'current_score' => round($averageScore, 2),
            'expected_score' => $expectedScore,
            'gap' => round($gap, 2),
            'is_core' => $isCore,
            'assessments_count' => $relevantAssessments->count(),
            'last_assessment_date' => $relevantAssessments->max('created_at'),
        ];
    }

    public function getEmployeeCompetencyGap(Request $request)
    {
        try {
            $employer = $request->user();
            $employeeId = $request->input('uid');
            // $departmentId = $request->input('department_id');

            $employee = Employee::with('department.technical_skill', 'department.soft_skill')
                    ->where('code', $employeeId)
                    ->where('employer_id', $employer->id)
                    ->firstOrFail();


            $department = $employee->department;
            $technicalCompetencies = $department->technical_skill;
            $softSkillCompetencies = $department->soft_skill;

            // Get all competencies
            $competencies = $technicalCompetencies->concat($softSkillCompetencies);
            $competencyIds = $competencies->pluck('id')->toArray();

            // Log with proper structure
            \Log::info('Competencies', ['data' => $competencies->toArray()]);

            // Get assessments with proper relationship loading
            $assessments = CroxxAssessment::whereHas('competencies', function ($query) use ($competencyIds) {
                $query->whereIn('competency_id', $competencyIds);
            })
            ->with([
                'competencies',
                'feedbacks' => function ($query) use ($employee) {
                    $query->where('employee_id', $employee->id)
                        ->where('is_published', 1)
                        ->orderBy('created_at', 'desc');
                }
            ])
            ->get();

            $technicalGaps = [];
            $softSkillGaps = [];

            foreach ($competencies as $competency) {
                // Find relevant assessments for this competency
                $relevantAssessments = $assessments->filter(function ($assessment) use ($competency) {
                    return $assessment->competencies->contains('id', $competency->id);
                });

                // Calculate average scores and gaps
                $scores = $relevantAssessments->map(function ($assessment) {
                    return $assessment->feedbacks->first()->graded_score ?? 0;
                });

                $averageScore = $scores->avg() ?? 0;
                $expectedScore = $relevantAssessments->first()->expected_percentage ?? 100;
                $gap = max(0, $expectedScore - $averageScore);

                // Check if competency is core (same level as employee)
                $isCore = ($competency->level == $employee->level);

                $gapData = [
                    'competency_name' => $competency->competency,
                    'competency_description' => $competency->description,
                    'current_score' => round($averageScore, 2),
                    'expected_score' => $expectedScore,
                    'gap' => round($gap, 2),
                    'is_core' => $isCore,
                    'assessments_count' => $relevantAssessments->count(),
                    'last_assessment_date' => $relevantAssessments->max('created_at'),
                ];

                // Categorize as technical or soft skill
                if ($technicalCompetencies->contains('id', $competency->id)) {
                    $technicalGaps[] = $gapData;
                } else {
                    $softSkillGaps[] = $gapData;
                }
            }

            $competencyGaps = array_merge($technicalGaps, $softSkillGaps);

            return response()->json([
                'status' => true,
                'data' => [
                    'employee_id' => $employeeId,
                    'competency_gaps' =>[
                        'technical' => $technicalGaps,
                        'soft_skill' => $softSkillGaps
                    ],
                    'average_gap' => round(collect($competencyGaps)->avg('gap'), 2),
                    'total_competencies' => count($competencyGaps),
                    'assessment_count' => $assessments->count(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Error retrieving competency gaps: " . $e->getMessage()
            ], 500);
        }
    }

    private function getDepartmentId($request, $employer)
    {
        $departmentId = $request->input('department') ?? $employer->default_company_id;

        if ($request->input('department')) {
            $employer->default_company_id = $departmentId;
            $employer->save();
        }

        return $departmentId ?: Department::where('employer_id', $employer->id)
            ->value('id');
    }

    private function getCompetencyData($department)
    {
        $competencies = $department->technical_skill;
        return [
            'ids' => $competencies->pluck('id'),
            'names' => $competencies->pluck('competency')->toArray(),
            'expected_scores' => array_fill_keys($competencies->pluck('id')->toArray(), 10)
        ];
    }

    private function calculateEmployeeGaps($employer, $department, $competencyData)
    {
        $employees = Employee::where('employer_id', $employer->id)
            ->where('job_code_id', $department->id)
            ->get();

        return $employees->map(function($employee) use ($competencyData) {
            $scores = [];
            $gaps = [];

            foreach ($competencyData['ids'] as $competencyId) {
                $assessmentData = $this->getAssessmentData($employee, $competencyId);
                $scores[] = $assessmentData['score'];
                $gaps[] = $assessmentData['gap'];
            }

            return [
                'employee_id' => $employee->id,
                'name' => $employee->name,
                'data' => $scores,
                'gaps' => $gaps,
                'average_gap' => array_sum($gaps) / count($gaps),
                // 'development_needs' => $this->identifyDevelopmentNeeds($gaps, $competencyData['names'])
            ];
        })->toArray();
    }

    private function getAssessmentData($employee, $competencyId)
    {
        $assessments = CroxxAssessment::whereHas('competencies', function ($query) use ($competencyId) {
                $query->where('competency_id', $competencyId);
            })
            ->with(['feedbacks' => function ($query) use ($employee) {
                $query->where('employee_id', $employee->id)
                    ->where('is_published', 1)
                    ->orderBy('created_at', 'desc');
            }])
            ->get();

        $scores = $assessments->map(function($assessment) {
            $feedback = $assessment->feedbacks->first();
            return $feedback ? ($feedback->graded_score / 100) * 10 : 0;
        })->filter();

        $actualScore = $scores->avg() ?: 0;
        $expectedScore = 10;
        $gap = max(0, min(10, $expectedScore - $actualScore));

        return [
            'score' => round($actualScore, 2),
            'gap' => round($gap, 2)
        ];
    }

    private function generateAnalysisSummary($employeeData, $competencyData)
    {
        // info('Summary', ['data' => $employeeData, 'competency_data' => $competencyData]);
        return [
            'critical_gaps' => $this->identifyCriticalGaps($employeeData, $competencyData),
            'strength_areas' => $this->identifyStrengthAreas($employeeData, $competencyData),
            'overall_status' => $this->calculateOverallStatus($employeeData),
            'recommendations' => $this->generateRecommendations($employeeData, $competencyData)
        ];
    }

    private function emptyResponse()
    {
        return response()->json([
            'status' => false,
            'message' => 'No department found',
            'data' => null
        ], 404);
    }

    private function identifyCriticalGaps($employeeData, $competencyData)
    {
        $criticalThreshold = 5; // Example threshold
        $criticalGaps = [];

        foreach ($competencyData['names'] as $index => $competency) {
            $averageGap = collect($employeeData)->avg(function($employee) use ($index) {
                return $employee['gaps'][$index];
            });

            if ($averageGap >= $criticalThreshold) {
                $criticalGaps[] = [
                    'competency' => $competency,
                    'average_gap' => round($averageGap, 2)
                ];
            }
        }

        return $criticalGaps;
    }

    private function identifyStrengthAreas($employeeData, $competencyData)
    {
        $strengthThreshold = 2; // Example threshold
        $strengths = [];

        foreach ($competencyData['names'] as $index => $competency) {
            $averageGap = collect($employeeData)->avg(function($employee) use ($index) {
                return $employee['gaps'][$index];
            });

            if ($averageGap <= $strengthThreshold) {
                $strengths[] = [
                    'competency' => $competency,
                    'average_score' => round(10 - $averageGap, 2)
                ];
            }
        }

        return $strengths;
    }

    private function calculateOverallStatus($employeeData)
    {
        $averageGap = collect($employeeData)->avg(function($employee) {
            return $employee['average_gap'];
        });

        return [
            'average_gap' => round($averageGap, 2),
            'status' => $this->determineStatusLevel($averageGap),
            'total_employees' => count($employeeData)
        ];
    }

    private function generateRecommendations($employeeData, $competencyData)
    {
        $recommendations = [];
        $criticalGaps = $this->identifyCriticalGaps($employeeData, $competencyData);

        foreach ($criticalGaps as $gap) {
            $recommendations[] = [
                'competency' => $gap['competency'],
                'recommendation' => "Implement training programs for " . $gap['competency'],
                'priority' => $gap['average_gap'] >= 7 ? 'High' : 'Medium'
            ];
        }

        return $recommendations;
    }

    private function determineStatusLevel($averageGap)
    {
        if ($averageGap >= 7) return 'Critical';
        if ($averageGap >= 5) return 'Needs Improvement';
        if ($averageGap >= 3) return 'Satisfactory';
        return 'Good';
    }

}
