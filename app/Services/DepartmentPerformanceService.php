<?php

namespace App\Services;

use App\Models\Assessment\CroxxAssessment;
use App\Models\Assessment\PeerReview;
use App\Models\Competency\DepartmentMapping;
use App\Models\Employee;
use App\Models\EmployerJobcode;
use App\Models\Goal;
use App\Models\PerformanceRecord;
use App\Models\Project\Project;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DepartmentPerformanceService
{

    protected $calculator;

    public function __construct(PerformanceCalculatorService $calculator)
    {
        $this->calculator = $calculator;
    }

    /**
     * Calculate department assessment metrics
     */
    public function calculateDepartmentAssessmentMetrics($departmentId, $startDate, $endDate)
    {
        $employeeIds = Employee::where('job_code_id', $departmentId)->pluck('id');

        $assessments = CroxxAssessment::whereHas('feedbacks', function($query) use ($employeeIds) {
            $query->whereIn('employee_id', $employeeIds);
        })
        ->whereBetween('created_at', [$startDate, $endDate])
        ->with('feedbacks')
        ->get();

        $completedCount = $assessments->filter(fn($a) => !empty($a->feedbacks))->count();
        $totalCount = $assessments->count();

        // Calculate average scores per employee first
        $employeeScores = [];
        foreach ($employeeIds as $empId) {
            $empAssessments = $assessments->filter(function($a) use ($empId) {
                return $a->feedbacks->where('employee_id', $empId)->count() > 0;
            });

            if ($empAssessments->isNotEmpty()) {
                $empScore = $empAssessments->avg(function($a) use ($empId) {
                    $feedback = $a->feedbacks->where('employee_id', $empId)->first();
                    return $feedback ? $feedback->graded_score : 0;
                });
                $employeeScores[] = $empScore;
            }
        }

        // Department average is the average of all employee averages
        $averageScore = count($employeeScores) > 0 ? array_sum($employeeScores) / count($employeeScores) : 0;

        return [
            'count' => $totalCount,
            'average_score' => $averageScore,
            'completed' => $completedCount,
            'pending' => $totalCount - $completedCount,
            'completion_rate' => $totalCount > 0 ? ($completedCount / $totalCount) * 100 : 0,
            'trend' => $this->calculateDepartmentTrend('assessments', $departmentId, $startDate, $endDate),
            'employee_participation_rate' => count($employeeScores) > 0 ?
                (count($employeeScores) / count($employeeIds)) * 100 : 0
        ];
    }

    /**
     * Calculate department peer review metrics
     */
    public function calculateDepartmentPeerReviewMetrics($departmentId, $startDate, $endDate)
    {
        $employeeIds = Employee::where('job_code_id', $departmentId)->pluck('id');

        $peerReviews = PeerReview::whereIn('employee_id', $employeeIds)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with('feedback')
            ->get();

        $completedCount = $peerReviews->where('status', 'completed')->count();
        $totalCount = $peerReviews->count();

        // Calculate average scores per employee first
        $employeeScores = [];
        foreach ($employeeIds as $empId) {
            $empReviews = $peerReviews->where('employee_id', $empId);

            if ($empReviews->isNotEmpty()) {
                $empScore = $empReviews->avg(function($pr) {
                    return $pr->feedback->avg('score') ?? 0;
                });
                $employeeScores[$empId] = $empScore;
            }
        }

        // Department average is the average of all employee averages
        $averageScore = count($employeeScores) > 0 ? array_sum($employeeScores) / count($employeeScores) : 0;

        return [
            'count' => $totalCount,
            'average_score' => $averageScore,
            'completed' => $completedCount,
            'pending' => $totalCount - $completedCount,
            'completion_rate' => $totalCount > 0 ? ($completedCount / $totalCount) * 100 : 0,
            'trend' => $this->calculateDepartmentTrend('peer_reviews', $departmentId, $startDate, $endDate),
            'employee_participation_rate' => count($employeeScores) > 0 ?
                (count($employeeScores) / count($employeeIds)) * 100 : 0
        ];
    }

    /**
     * Calculate department goal metrics
     */
    public function calculateDepartmentGoalMetrics($departmentId, $startDate, $endDate)
    {
        $employeeIds = Employee::where('job_code_id', $departmentId)->pluck('id');

        $goals = Goal::whereIn('employee_id', $employeeIds)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $achievedCount = $goals->where('status', 'achieved')->count();
        $inProgressCount = $goals->where('status', 'in_progress')->count();
        $totalCount = $goals->count();

        // Calculate completion rate per employee
        $employeeCompletionRates = [];
        foreach ($employeeIds as $empId) {
            $empGoals = $goals->where('employee_id', $empId);

            if ($empGoals->isNotEmpty()) {
                $empAchieved = $empGoals->where('status', 'achieved')->count();
                $empRate = ($empAchieved / $empGoals->count()) * 100;
                $employeeCompletionRates[$empId] = $empRate;
            }
        }

        // Calculate department average completion rate
        $avgCompletionRate = count($employeeCompletionRates) > 0 ?
            array_sum($employeeCompletionRates) / count($employeeCompletionRates) : 0;

        return [
            'count' => $totalCount,
            'achieved' => $achievedCount,
            'in_progress' => $inProgressCount,
            'completion_rate' => $avgCompletionRate,
            'trend' => $this->calculateDepartmentTrend('goals', $departmentId, $startDate, $endDate),
            'employee_participation_rate' => count($employeeCompletionRates) > 0 ?
                (count($employeeCompletionRates) / count($employeeIds)) * 100 : 0,
            'goal_distribution' => $this->calculator->calculateGoalDistribution($goals)
        ];
    }


    public function calculateDepartmentProjectMetrics($departmentId, $startDate, $endDate)
    {
        // Get IDs of all employees in the department
        $employeeIds = Employee::where('job_code_id', $departmentId)->pluck('id');

        // Fetch projects that have tasks assigned to these employees
        $projects = Project::whereHas('tasks.assigned', function($query) use ($employeeIds) {
                // Use the correct column "employee_id" instead of "assigned"
                $query->whereIn('employee_id', $employeeIds);
            })
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with(['tasks' => function($query) use ($employeeIds) {
                $query->whereHas('assigned', function($q) use ($employeeIds) {
                    $q->whereIn('employee_id', $employeeIds);
                });
            }])
            ->get();

        $tasks = collect($projects->pluck('tasks')->flatten());

        $completedCount = $tasks->where('status', 'completed')->count();
        $inProgressCount = $tasks->where('status', 'in_progress')->count();
        $totalCount = $tasks->count();

        // Calculate on-time completion rate
        $onTimeRate = $this->calculator->calculateOnTimeCompletionRate($tasks);

        // Calculate completion rate per employee
        $employeeCompletionRates = [];
        foreach ($employeeIds as $empId) {
            // Filter tasks where the assigned employee matches
            $empTasks = $tasks->filter(function($task) use ($empId) {
                // Assuming each task has a relationship "assigned" which is a collection of AssignedEmployee
                // and each AssignedEmployee has an "employee_id" field.
                return $task->assigned->contains('employee_id', $empId);
            });

            if ($empTasks->isNotEmpty()) {
                $empCompleted = $empTasks->where('status', 'completed')->count();
                $empRate = ($empCompleted / $empTasks->count()) * 100;
                $employeeCompletionRates[$empId] = $empRate;
            }
        }

        // Calculate department average completion rate
        $avgCompletionRate = count($employeeCompletionRates) > 0 ?
            array_sum($employeeCompletionRates) / count($employeeCompletionRates) : 0;

        return [
            'project_count' => $projects->count(),
            'task_count' => $totalCount,
            'completed_tasks' => $completedCount,
            'in_progress_tasks' => $inProgressCount,
            'completion_rate' => $avgCompletionRate,
            'on_time_completion_rate' => $onTimeRate,
            'trend' => $this->calculateDepartmentTrend('projects', $departmentId, $startDate, $endDate),
            'employee_participation_rate' => count($employeeCompletionRates) > 0 ?
                (count($employeeCompletionRates) / count($employeeIds)) * 100 : 0,
            'project_details' => $projects->map(fn($p) => [
                'name' => $p?->title,
                'status' => $p->status,
                'progress' => $p->progress,
                'task_count' => $p->tasks->count()
            ])
        ];
    }

    /**
     * Calculate department competency metrics.
     */
    public function calculateDepartmentCompetencyMetrics($departmentId, $startDate, $endDate)
    {
        $employeeIds = Employee::where('job_code_id', $departmentId)->pluck('id');

        // Retrieve only mappings with a non-null competency
        $mappings = DepartmentMapping::where('department_id', $departmentId)
                    ->whereNotNull('competency')
                    ->get();

        $competencySummary = [];
        foreach ($mappings as $mapping) {
            // Skip if competency is still not set (extra safety check)
            if (!$mapping->competency) {
                continue;
            }

            $employeeScores = [];
            foreach ($employeeIds as $empId) {
                $kpiAchievement = $this->calculator->calculateEmployeeKPIAchievement(
                    $empId,
                    $mapping,
                    $startDate,
                    $endDate
                );

                $score = $kpiAchievement['achievement_rate'];
                $employeeScores[] = $score;
            }

            $avgScore = count($employeeScores) > 0 ? array_sum($employeeScores) / count($employeeScores) : 0;

            $competencySummary[] = [
                'competency' => $mapping->competency,
                'average_score' => $avgScore,
                'kpi_count' => 1, // Assuming one KPI per mapping per your sample
                'employee_count' => count($employeeScores),
                'status' => $this->calculator->getCompetencyStatus($avgScore)
            ];
        }

        $overallScore = count($competencySummary) > 0 ?
            collect($competencySummary)->avg('average_score') : 0;

        return [
            'count' => count($competencySummary),
            'average_score' => $overallScore,
            'trend' => $this->calculateDepartmentTrend('competencies', $departmentId, $startDate, $endDate),
            'details' => $competencySummary
        ];
    }



    /**
     * Calculate KPI achievement for a department
     *
     * @param object $department Department object
     * @param string $startDate
     * @param string $endDate
     * @return array Department KPI achievement data
     */
    public function calculateDepartmentKPIAchievement($department, $startDate, $endDate)
    {
        $employeeIds = Employee::where('job_code_id', $department->id)->pluck('id');

        // Get department mappings and categorize them by competency_role
        $mappings = DepartmentMapping::where('department_id', $department->id)->get();

        // Group mappings by competency_role (technical_skill or soft_skill)
        $technicalMappings = $mappings->where('competency_role', 'technical_skill');
        $softMappings = $mappings->where('competency_role', 'soft_skill');

        $kpiResults = [];

        // Process technical KPIs
        foreach ($technicalMappings as $mapping) {
            $employeeAchievements = [];

            foreach ($employeeIds as $empId) {
                // Get KPI achievement for this employee
                $achievement = $this->calculator->calculateEmployeeKPIAchievement($empId, $mapping, $startDate, $endDate);

                $employeeAchievements[] = [
                    'employee_id' => $empId,
                    'actual' => $achievement['actual'],
                    'achievement_rate' => $achievement['achievement_rate']
                ];
            }

            $avgAchievement = count($employeeAchievements) > 0 ?
                collect($employeeAchievements)->avg('achievement_rate') : 0;

            $kpiResults[] = [
                'kpi_name' => $mapping->competency,
                'kpi_type' => $mapping->competency_role,
                'category' => 'technical',
                'target' => $mapping->target_score,
                'department_achievement_rate' => $avgAchievement,
                'status' => $this->calculator->getKPIStatus($avgAchievement),
                'employee_participation' => count($employeeAchievements),
                'employee_achievements' => $employeeAchievements
            ];
        }

        // Process soft skill KPIs
        foreach ($softMappings as $mapping) {
            $employeeAchievements = [];

            foreach ($employeeIds as $empId) {
                // Get KPI achievement for this employee
                $achievement = $this->calculator->calculateEmployeeKPIAchievement($empId, $mapping, $startDate, $endDate);

                $employeeAchievements[] = [
                    'employee_id' => $empId,
                    'actual' => $achievement['actual'],
                    'achievement_rate' => $achievement['achievement_rate']
                ];
            }

            $avgAchievement = count($employeeAchievements) > 0 ?
                collect($employeeAchievements)->avg('achievement_rate') : 0;

            $kpiResults[] = [
                'kpi_name' => $mapping->competency,
                'kpi_type' => $mapping->competency_role,
                'category' => 'soft',
                'target' => $mapping->target_score,
                'department_achievement_rate' => $avgAchievement,
                'status' => $this->calculator->getKPIStatus($avgAchievement),
                'employee_participation' => count($employeeAchievements),
                'employee_achievements' => $employeeAchievements
            ];
        }

        // Calculate overall achievement
        $overallAchievement = count($kpiResults) > 0 ?
            collect($kpiResults)->avg('department_achievement_rate') : 0;

        return [
            'overall_achievement' => $overallAchievement,
            'technical_achievement' => collect($kpiResults)->where('category', 'technical')->avg('department_achievement_rate') ?? 0,
            'soft_achievement' => collect($kpiResults)->where('category', 'soft')->avg('department_achievement_rate') ?? 0,
            'kpi_details' => $kpiResults
        ];
    }

      /**
     * Calculate performance trend for department
     */
    public function calculateDepartmentTrend($metricType, $departmentId, $currentStartDate, $currentEndDate)
    {
        // Get previous period
        $previousStartDate = (clone $currentStartDate)->subMonth();
        $previousEndDate = (clone $currentEndDate)->subMonth();

        // Get current period value
        $currentValue = $this->getDepartmentTrendValue($metricType, $departmentId, $currentStartDate, $currentEndDate);

        // Get previous period value
        $previousValue = $this->getDepartmentTrendValue($metricType, $departmentId, $previousStartDate, $previousEndDate);

        // Calculate trend
        $trend = $previousValue > 0 ?
            (($currentValue - $previousValue) / $previousValue) * 100 : 0;

        return [
            'current_value' => $currentValue,
            'previous_value' => $previousValue,
            'percentage_change' => round($trend, 1),
            'direction' => $trend > 0 ? 'up' : ($trend < 0 ? 'down' : 'stable')
        ];
    }


    /**
     * Get trend value for department based on metric type
     */
    public function getDepartmentTrendValue($metricType, $departmentId, $startDate, $endDate)
    {
        $employeeIds = Employee::where('job_code_id', $departmentId)->pluck('id');

        return match($metricType) {
            'assessments' => $this->getDepartmentAssessmentTrend($employeeIds, $startDate, $endDate),
            'peer_reviews' => PeerReview::whereIn('employee_id', $employeeIds)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->with('feedback')
                ->get()
                ->avg(fn($pr) => $pr->feedback->avg('score') ?? 0) ?? 0,
            'goals' => Goal::whereIn('employee_id', $employeeIds)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('status', 'achieved')
                ->count(),
            'projects' => $this->getDepartmentProjectTrend($employeeIds, $startDate, $endDate),
            'competencies' => $this->getDepartmentCompetencyAverage($departmentId, $startDate, $endDate),
            default => 0
        };
    }


    /**
     * Get assessment trend value for a department
     */
    private function getDepartmentAssessmentTrend($employeeIds, $startDate, $endDate)
    {
        $assessments = CroxxAssessment::whereHas('feedbacks', function($query) use ($employeeIds) {
            $query->whereIn('employee_id', $employeeIds);
        })
        ->whereBetween('created_at', [$startDate, $endDate])
        ->with(['feedbacks' => function($query) use ($employeeIds) {
            $query->whereIn('employee_id', $employeeIds);
        }])
        ->get();

        if ($assessments->isEmpty()) {
            return 0;
        }

        $totalScore = 0;
        $scoreCount = 0;

        foreach ($assessments as $assessment) {
            foreach ($assessment->feedbacks as $feedback) {
                if (isset($feedback->graded_score)) {
                    $totalScore += $feedback->graded_score;
                    $scoreCount++;
                }
            }
        }

        return $scoreCount > 0 ? $totalScore / $scoreCount : 0;
    }

    /**
     * Get project trend value for a department
     */
    private function getDepartmentProjectTrend($employeeIds, $startDate, $endDate)
    {
        $projects = Project::whereHas('tasks.assigned', function($query) use ($employeeIds) {
            $query->whereIn('employee_id', $employeeIds);
        })
        ->with(['tasks' => function($query) {
            $query->with('assigned');
        }])
        ->get();

        $completedCount = 0;

        foreach ($projects as $project) {
            foreach ($project->tasks as $task) {
                $isAssignedToAnyDeptEmployee = $task->assigned->whereIn('employee_id', $employeeIds)->isNotEmpty();

                if ($isAssignedToAnyDeptEmployee &&
                    $task->status === 'completed' &&
                    $task->completed_at &&
                    $task->completed_at >= $startDate &&
                    $task->completed_at <= $endDate) {
                    $completedCount++;
                }
            }
        }

        return $completedCount;
    }


    /**
     *
    * Get department competency average
    */
   public function getDepartmentCompetencyAverage($departmentId, $startDate, $endDate)
   {
       $employeeIds = Employee::where('job_code_id', $departmentId)->pluck('id');
       info(['calculateDepartmentCompetencyAverage', $departmentId]);
       $mappings = DepartmentMapping::where('department_id', $departmentId)
           // ->with(['competencyKPIs', 'competency'])
           ->get();

       $competencyScores = [];
       foreach ($mappings as $mapping) {
           $employeeScores = [];

           foreach ($employeeIds as $empId) {
               $kpiAchievements = $this->calculator->calculateEmployeeKPIAchievement(
                   $empId,
                   $mapping->competencyKPIs,
                   $startDate,
                   $endDate
               );

               $score = collect($kpiAchievements)->avg('achievement_rate') ?? 0;
               $employeeScores[] = $score;
           }

           $competencyScores[] = $employeeScores ? array_sum($employeeScores) / count($employeeScores) : 0;
       }

       return $competencyScores ? array_sum($competencyScores) / count($competencyScores) : 0;
   }


    /**
     * Generate insights for department performance
     */
    public function generateDepartmentInsights($department, $sections, $overallScore, $kpiAchievement = '')
    {
        $insights = [];

        // Assessment insights
        if (isset($sections['assessments'])) {
            $assessmentScore = $sections['assessments']['average_score'] ?? 0;
            $participationRate = $sections['assessments']['employee_participation_rate'] ?? 0;

            if ($assessmentScore >= 85) {
                $insights[] = "Department showing strong technical knowledge in assessments.";
            } elseif ($assessmentScore < 70) {
                $insights[] = "Department-wide training may improve technical knowledge scores.";
            }

            if ($participationRate < 75) {
                $insights[] = "Increase assessment participation to better evaluate department skills.";
            }
        }

        // Peer review insights
        if (isset($sections['peer_reviews'])) {
            $reviewScore = $sections['peer_reviews']['average_score'] ?? 0;
            $trend = $sections['peer_reviews']['trend']['direction'] ?? 'stable';

            if ($reviewScore >= 85) {
                $insights[] = "Strong collaborative culture reflected in high peer review scores.";
            } elseif ($reviewScore < 65) {
                $insights[] = "Team-building activities may improve interdepartmental collaboration.";
            }

            if ($trend === 'down') {
                $insights[] = "Declining peer review trend suggests potential team dynamics issues.";
            }
        }

        // Goal insights
        if (isset($sections['goals'])) {
            $completionRate = $sections['goals']['completion_rate'] ?? 0;
            $participationRate = $sections['goals']['employee_participation_rate'] ?? 0;

            if ($completionRate >= 85) {
                $insights[] = "Department effectively achieves set goals and objectives.";
            } elseif ($completionRate < 60) {
                $insights[] = "Consider reviewing goal-setting processes for better achievement.";
            }

            if ($participationRate < 80) {
                $insights[] = "Encourage broader participation in departmental goal-setting.";
            }
        }

        // Project insights
        if (isset($sections['projects'])) {
            $completionRate = $sections['projects']['completion_rate'] ?? 0;
            $onTimeRate = $sections['projects']['on_time_completion_rate'] ?? 0;

            if ($onTimeRate >= 85) {
                $insights[] = "Department consistently delivers projects on schedule.";
            } elseif ($onTimeRate < 65) {
                $insights[] = "Project management practices may need improvement.";
            }

            if ($completionRate < 70) {
                $insights[] = "Focus on task completion to improve project delivery rates.";
            }
        }

        // Competency insights
        if (isset($sections['competencies'])) {
            $competencyScore = $sections['competencies']['average_score'] ?? 0;

            if ($competencyScore >= 85) {
                $insights[] = "Department demonstrates strong proficiency in key competencies.";
            } elseif ($competencyScore < 65) {
                $insights[] = "Targeted training in core competencies recommended.";
            }
        }

        // KPI insights
        if (isset($kpiAchievement)) {
            $overallKPIAchievement = $kpiAchievement['overall_achievement'] ?? 0;
            $technicalAchievement = $kpiAchievement['technical_achievement'] ?? 0;
            $softAchievement = $kpiAchievement['soft_achievement'] ?? 0;

            if ($overallKPIAchievement >= 85) {
                $insights[] = "Department successfully meeting or exceeding key performance indicators.";
            } elseif ($overallKPIAchievement < 65) {
                $insights[] = "KPI targets may need review or additional support for achievement.";
            }

            if ($technicalAchievement > $softAchievement + 15) {
                $insights[] = "Department stronger in technical skills than soft skills; consider soft skill development.";
            } elseif ($softAchievement > $technicalAchievement + 15) {
                $insights[] = "Department stronger in soft skills than technical skills; consider technical training.";
            }
        }

        // Overall performance insights
        if ($overallScore >= 85) {
            $insights[] = "High-performing department with strong results across all areas.";
        } elseif ($overallScore >= 75) {
            $insights[] = "Solid departmental performance with specific areas for enhancement.";
        } elseif ($overallScore < 65) {
            $insights[] = "Department may benefit from a comprehensive improvement plan.";
        }

        // Check for balanced or unbalanced performance
        $scores = [
            $sections['assessments']['average_score'] ?? 0,
            $sections['peer_reviews']['average_score'] ?? 0,
            $sections['goals']['completion_rate'] ?? 0,
            $sections['projects']['completion_rate'] ?? 0,
            $sections['competencies']['average_score'] ?? 0
        ];

        $stdDev = $this->calculator->calculateStandardDeviation($scores);

        if ($stdDev > 15) {
            $insights[] = "Performance varies significantly across different areas; consider more balanced approach.";
        } elseif ($stdDev < 8 && $overallScore >= 75) {
            $insights[] = "Department shows consistently strong performance across all evaluation areas.";
        }

        return $insights;
    }

    /**
     * Get department historical performance
     */
    public function getDepartmentHistoricalPerformance($departmentId, $year)
    {
        $historicalRecords = PerformanceRecord::where('recordable_id', $departmentId)
            ->where('recordable_type', EmployerJobcode::class)
            ->where('year', $year)
            ->orderBy('month')
            ->get();

        if ($historicalRecords->isEmpty()) {
            return [
                'monthly_scores' => [],
                'average_score' => 0,
                'trend' => 'stable'
            ];
        }

        $monthlyScores = $historicalRecords->map(function($record) {
            return [
                'month' => $record->month,
                'score' => $record->overall_score
            ];
        });

        $averageScore = $historicalRecords->avg('overall_score');

        // Calculate trend
        $firstHalf = $historicalRecords->filter(fn($r) => $r->month <= 6)->avg('overall_score') ?? 0;
        $secondHalf = $historicalRecords->filter(fn($r) => $r->month > 6)->avg('overall_score') ?? 0;

        $trend = 'stable';
        if ($secondHalf > $firstHalf + 5) {
            $trend = 'improving';
        } elseif ($firstHalf > $secondHalf + 5) {
            $trend = 'declining';
        }

        return [
            'monthly_scores' => $monthlyScores,
            'average_score' => $averageScore,
            'trend' => $trend
        ];
    }

}
