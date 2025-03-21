<?php

namespace App\Http\Controllers\Api\v2\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Goal;
use App\Models\Employee;
use App\Models\Campaign;
use App\Models\Assessment\CroxxAssessment;
use App\Models\EmployerJobcode as Department;

class ReportAnalysisController extends Controller
{
    public function gapAnalysisSummary(Request $request)
    {
        try {
            $employer = $request->user();
            $departmentId = $this->getDepartmentId($request, $employer);

            if (!$departmentId) {
                return $this->emptyResponse();
            }

            $department = Department::with('technical_skill', 'soft_skill')->find($departmentId);

            $competencyData = $this->getCompetencyData($department);
            $employeeData = $this->calculateEmployeeGaps($employer, $department, $competencyData);

            return response()->json([
                'status' => true,
                'data' => [
                    'competencies' => $competencyData['names'],
                    'employeeData' => $employeeData,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Error generating gap analysis: " . $e->getMessage()
            ], 500);
        }
    }
    /**
     * Get gap analysis report
     */
    public function gapAnalysisReport(Request $request)
    {
        try {
            $employer = $request->user();
            $departmentId = $this->getDepartmentId($request, $employer);

            if (!$departmentId) {
                return $this->emptyResponse();
            }

            $department = Department::with('technical_skill', 'soft_skill')->find($departmentId);
            $technicalCompetencyData = $this->getCompetencyData($department);
            // $competencyData = $this->getCompetencyData($department, 'all');

            $employeeData = $this->calculateEmployeeGaps($employer, $department, $technicalCompetencyData);
            $summary = $this->generateAnalysisSummary($employeeData, $technicalCompetencyData);

            return response()->json([
                'status' => true,
                'data' => [
                    'competencies' => $technicalCompetencyData['names'],
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

    /**
     * Get team competency gap analysis
     */
    public function getTeamCompetencyGap(Request $request)
    {
        try {
            $employer = $request->user();
            $employeeIds = $this->parseEmployeeIds($request);

            // Validate we have employees to process
            if (empty($employeeIds)) {
                return response()->json([
                    'status' => false,
                    'message' => 'No employees specified'
                ], 400);
            }

            $results = $this->processTeamCompetencyGaps($employer, $employeeIds);

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
     * Get individual employee competency gap
     */
    public function getEmployeeCompetencyGap(Request $request)
    {
        try {
            $employer = $request->user();
            $employeeId = $request->input('uid')
                ?: Employee::where('employer_id', $employer->id)->value('id');

            $employee = Employee::with('department.technical_skill', 'department.soft_skill')
                    ->where('code', $employeeId)
                    ->where('employer_id', $employer->id)
                    ->firstOrFail();
            // Gaps
            $department = $employee->department;
            $competencyGaps = $this->processEmployeeCompetencyGaps($employee, $department);
            $allGaps = array_merge($competencyGaps['technical_skill'], $competencyGaps['soft_skill']);
            // Distribution
            $technicalSkills = $department->technical_skill->pluck('competency')->toArray();
            $softSkills = $department->soft_skill->pluck('competency')->toArray();
            $technicalDistribution = $this->calculateSkillDistribution($employee, $technicalSkills);
            $softDistribution = $this->calculateSkillDistribution($employee, $softSkills, 'soft');

            return response()->json([
                'status' => true,
                'data' => [
                    'employee_id' => $employeeId,
                    'competency_gaps' => $competencyGaps,
                    'distributions' => [
                        'technical_skill' => $technicalDistribution,
                        'soft_skill' => $softDistribution,
                    ],
                    'average_gap' => round(collect($allGaps)->avg('gap'), 2),
                    'total_competencies' => count($allGaps),
                    'assessment_count' => $competencyGaps['assessment_count'],
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Error retrieving competency gaps: " . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get employee skill distribution data
     */
    public function getEmployeesDistribution(Request $request)
    {
        try {
            $employer = $request->user();
            $perPage = $request->input('per_page', 10);
            $departmentId = $this->getDepartmentId($request, $employer);

            // Retrieve all employees for this department with related skills
            $employees = Employee::where('employer_id', $employer->id)
                ->where('job_code_id', $departmentId)
                ->with('department.technical_skill', 'department.soft_skill')
                ->paginate($perPage);

            $employeesDistribution = [];

            foreach ($employees as $employee) {
                // Extract technical and soft competencies from the employee's department
                $technicalSkills = $employee->department->technical_skill->pluck('competency')->toArray();
                $softSkills = $employee->department->soft_skill->pluck('competency')->toArray();

                // Calculate distributions for the employee
                $technicalDistribution = $this->calculateSkillDistribution($employee, $technicalSkills);
                $softDistribution = $this->calculateSkillDistribution($employee, $softSkills, 'soft');

                // Compute average scores for technical and soft skills (prevent division by zero)
                $technicalCount = count($technicalDistribution['assessment_distribution']);
                $softCount = count($softDistribution['assessment_distribution']);

                $avgTechnical = $technicalCount > 0
                    ? array_sum($technicalDistribution['assessment_distribution']) / $technicalCount
                    : 0;

                $avgSoft = $softCount > 0
                    ? array_sum($softDistribution['assessment_distribution']) / $softCount
                    : 0;

                $insights = $this->generateDistributionInsights(
                    $technicalDistribution,
                    $softDistribution,
                    $avgTechnical,
                    $avgSoft
                );

                $employeesDistribution[] = [
                    'employee_id' => $employee->id,
                    'employee_code' => $employee->code,
                    'photo_url' => $employee->photo_url,
                    'name' => $employee->name,
                    'role' => $employee->department_role?->name,
                    'technical_distribution' => $technicalDistribution,
                    'soft_distribution' => $softDistribution,
                    'insights' => $insights,
                ];
            }

            return response()->json([
                'status' => true,
                'data' => [
                    'employees_distribution' => $employeesDistribution,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Error retrieving employees distribution: " . $e->getMessage()
            ], 500);
        }
    }



    /**
     * Calculate skill distribution for a given employee and skill set
     */
    private function calculateSkillDistribution($employee, $skills, $skillType = 'technical')
    {
        $assessment_distribution = [];
        $trainings_distribution = array_fill(0, count($skills), 0); // Initialize with zeros

        // Get assessments for relevant skills
        $assessments = CroxxAssessment::whereHas('competencies', function ($query) use ($skills) {
            $query->whereIn('competency', $skills);
        })->with([
            'competencies',
            'feedbacks' => function ($query) use ($employee) {
                $query->where('employee_id', $employee->id)
                      ->where('is_published', 1)
                      ->orderBy('created_at', 'desc');
            }
        ])->get();

        // Map skills to scores
        foreach ($skills as $skill) {
            $score = 0;

            // Find matching assessment for this skill
            foreach ($assessments as $assessment) {
                foreach ($assessment->competencies as $competency) {
                    if ($competency->competency === $skill) {
                        $feedback = $assessment->feedbacks->first();
                        if ($feedback) {
                            $score = $feedback->graded_score;
                            break 2; // Break both loops once score is found
                        }
                    }
                }
            }

            $assessment_distribution[] = $score;
        }

        return [
            'categories' => $skills,
            'assessment_distribution' => $assessment_distribution,
            'trainings_distribution' => $trainings_distribution,
        ];
    }

    /**
     * Generate insights based on skill distribution analysis
     */
    private function generateDistributionInsights($technical_distribution, $soft_distribution, $avg_technical, $avg_soft)
    {
        $insights = [];

        // Find strengths (scores above 80)
        $strengths = [];
        foreach ($technical_distribution['categories'] as $index => $skill) {
            if ($technical_distribution['assessment_distribution'][$index] >= 30) {
                $strengths[] = [
                    'skill' => $skill,
                    'score' => $technical_distribution['assessment_distribution'][$index],
                    'type' => 'technical'
                ];
            }
        }

        foreach ($soft_distribution['categories'] as $index => $skill) {
            if ($soft_distribution['assessment_distribution'][$index] >= 30) {
                $strengths[] = [
                    'skill' => $skill,
                    'score' => $soft_distribution['assessment_distribution'][$index],
                    'type' => 'soft'
                ];
            }
        }

        // Find improvement areas (scores below 60)
        $improvement_areas = [];
        foreach ($technical_distribution['categories'] as $index => $skill) {
            if ($technical_distribution['assessment_distribution'][$index] < 10 &&
                $technical_distribution['assessment_distribution'][$index] > 0) {
                $improvement_areas[] = [
                    'skill' => $skill,
                    'score' => $technical_distribution['assessment_distribution'][$index],
                    'type' => 'technical'
                ];
            }
        }

        foreach ($soft_distribution['categories'] as $index => $skill) {
            if ($soft_distribution['assessment_distribution'][$index] < 10 &&
                $soft_distribution['assessment_distribution'][$index] > 0) {
                $improvement_areas[] = [
                    'skill' => $skill,
                    'score' => $soft_distribution['assessment_distribution'][$index],
                    'type' => 'soft'
                ];
            }
        }

        // Find skills without assessments
        // $unassessed_skills = [];
        // foreach ($technical_distribution['categories'] as $index => $skill) {
        //     if ($technical_distribution['assessment_distribution'][$index] === 0) {
        //         $unassessed_skills[] = [
        //             'skill' => $skill,
        //             'type' => 'technical'
        //         ];
        //     }
        // }

        // foreach ($soft_distribution['categories'] as $index => $skill) {
        //     if ($soft_distribution['assessment_distribution'][$index] === 0) {
        //         $unassessed_skills[] = [
        //             'skill' => $skill,
        //             'type' => 'soft'
        //         ];
        //     }
        // }

        // Generate overall insights
        $insights = [
            'overall_performance' => $this->determineOverallPerformance(($avg_technical + $avg_soft) / 2),
            'strengths' => $strengths,
            'improvement_areas' => $improvement_areas,
            // 'unassessed_skills' => $unassessed_skills,
            'balance' => [
                'status' => abs($avg_technical - $avg_soft) <= 15 ? 'balanced' : 'imbalanced',
                'recommendation' => $this->getBalanceRecommendation($avg_technical, $avg_soft)
            ]
        ];

        return $insights;
    }

    /**
     * Determine overall performance category
     */
    private function determineOverallPerformance($averageScore)
    {
        if ($averageScore >= 90) return ['status' => 'excellent', 'description' => 'Performing exceptionally well across competencies'];
        if ($averageScore >= 80) return ['status' => 'strong', 'description' => 'Strong performer with good competency coverage'];
        if ($averageScore >= 70) return ['status' => 'good', 'description' => 'Good overall performance with some development opportunities'];
        if ($averageScore >= 60) return ['status' => 'satisfactory', 'description' => 'Meeting basic requirements but has several areas for improvement'];
        if ($averageScore >= 40) return ['status' => 'needs_improvement', 'description' => 'Requires significant improvement in multiple areas'];
        return ['status' => 'insufficient_data', 'description' => 'Not enough assessment data for reliable evaluation'];
    }

    /**
     * Get recommendation for balancing technical and soft skills
     */
    private function getBalanceRecommendation($avg_technical, $avg_soft)
    {
        $diff = $avg_technical - $avg_soft;

        if (abs($diff) <= 15) {
            return 'Skills are well balanced between technical and soft competencies';
        }

        if ($diff > 15) {
            return 'Focus on developing soft skills to balance with strong technical competencies';
        }

        return 'Focus on strengthening technical skills to balance with soft competencies';
    }

    /**
     * Process employee competency gaps
     */
    private function processEmployeeCompetencyGaps($employee, $department)
    {
        $technicalCompetencies = $department->technical_skill;
        $softSkillCompetencies = $department->soft_skill;
        $competencies = $technicalCompetencies->concat($softSkillCompetencies);
        $competencyIds = $competencies->pluck('id')->toArray();

        // Get assessments
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

        // Process each competency regardless of whether it has assessments
        foreach ($competencies as $competency) {
            // Calculate gap data
            $gapData = $this->calculateCompetencyGap($competency, $assessments, $employee->level);

            // If no data was found, create a consistent data structure with null values
            if (!$gapData) {
                $isCore = ($competency->level ==  $employee->level);
                $gapData = [
                    'competency_name' => $competency->competency,
                    'competency_description' => $competency->description,
                    // 'competency_level' => $competency->level,
                    'current_score' => round(0, 2),
                    'expected_score' => $competency->target_score ?? 75,
                    'gap' => round(0, 2),
                    'is_core' => $isCore,
                    'assessments_count' => 0,
                    'last_assessment_date' => null
                ];
            }

            // Categorize as technical or soft skill
            if ($technicalCompetencies->contains('id', $competency->id)) {
                $technicalGaps[] = $gapData;
            } else {
                $softSkillGaps[] = $gapData;
            }
        }

        return [
            'technical_skill' => $technicalGaps,
            'soft_skill' => $softSkillGaps,
            'assessment_count' => $assessments->count()
        ];
    }

    /**
     * Process team competency gaps for multiple employees
     */
    private function processTeamCompetencyGaps($employer, $employeeIds)
    {
        $results = [];

        foreach ($employeeIds as $employeeId) {
            $employee = Employee::with('department.technical_skill', 'department.soft_skill')
                ->where('code', $employeeId)
                ->where('employer_id', $employer->id)
                ->first();

            if (!$employee) {
                continue;
            }

            $department = $employee->department;
            $competencyGaps = $this->processEmployeeCompetencyGaps($employee, $department);
            $allGaps = array_merge($competencyGaps['technical'], $competencyGaps['soft_skill']);

            $results[] = [
                'employee_id' => $employeeId,
                'competency_gaps' => [
                    'technical_skill' => $competencyGaps['technical'],
                    'soft_skill' => $competencyGaps['soft_skill']
                ],
                'average_gap' => round(collect($allGaps)->avg('gap'), 2),
                'total_competencies' => count($allGaps),
                'assessment_count' => $competencyGaps['assessment_count'],
            ];
        }

        return $results;
    }

    /**
     * Parse employee IDs from request
     */
    private function parseEmployeeIds(Request $request)
    {
        $employeeIds = $request->input('employees', []);

        // If single employee is passed, convert to array
        if ($request->has('employee')) {
            $employeeIds = [$request->input('employee')];
        }

        return $employeeIds;
    }


    private function getDepartmentId($request, $employer)
    {
        $departmentId = $request->input('department') ?? $request->input('uid') ?? $employer->default_company_id;

        if (is_numeric($departmentId)) {
            $department = Department::where('employer_id', $employer->id)
                                    ->where('id', $departmentId)
                                    ->select('id')
                                    ->first();
        } else {
            $department = Department::where('employer_id', $employer->id)
                                    ->where('job_title', $departmentId)
                                    ->select('id')
                                    ->first();
        }

        if ($department && $department->id !== $employer->default_company_id) {
            $employer->default_company_id = $department->id;
            $employer->save();
        }

        return $department ? $department->id : Department::where('employer_id', $employer->id)->value('id');
    }


   private function getCompetencyData($department, $type = 'technical_skill')
    {
        if ($type === 'all') {
            $competencies = $department->technical_skill->concat($department->soft_skill);
        } elseif ($type === 'technical_skill') {
            $competencies = $department->technical_skill;
        } elseif ($type === 'soft_skill') {
            $competencies = $department->soft_skill;
        } else {
            $competencies = $department->technical_skill;
        }

        return [
            'ids'             => $competencies->pluck('id')->toArray(),
            'names'           => $competencies->pluck('competency')->toArray(),
            'expected_scores' => array_fill_keys($competencies->pluck('id')->toArray(), 10),
            'roles'           => $competencies->pluck('competency_role')->toArray(),
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
               'average_gap' => count($gaps) > 0 ? array_sum($gaps) / count($gaps) : 0,
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
            // 'competency_level' => $competency->level,
            'current_score' => round($averageScore, 2),
            'expected_score' => $expectedScore,
            'gap' => round($gap, 2),
            'is_core' => $isCore,
            'assessments_count' => $relevantAssessments->count(),
            'last_assessment_date' => $relevantAssessments->max('created_at'),
        ];
    }

    private function generateAnalysisSummary($employeeData, $competencyData)
    {
        return [
            // 'critical_gaps' => $this->identifyCriticalGaps($employeeData, $competencyData),
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
        $criticalThreshold = 4; // Example threshold
        $criticalGaps = [];

        foreach ($competencyData['names'] as $index => $competency) {
            $averageGap = collect($employeeData)->avg(function($employee) use ($index) {
                return $employee['gaps'][$index];
            });

            if ($averageGap >= $criticalThreshold) {
                $criticalGaps[] = [
                    'competency' => $competency,
                    'role'        => $competencyData['roles'][$index] ?? null,
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
            info($gap);
            $recommendations[] = [
                'competency' => $gap['competency'],
                'role' => $gap['role'],
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
