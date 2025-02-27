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

class PerformanceCalculatorService
{
    /**
     * Weights for calculating overall score
     */
    protected $weights = [
        'assessments' => 0.2,
        'peer_reviews' => 0.2,
        'goals' => 0.2,
        'projects' => 0.2,
        'competencies' => 0.2
    ];

    /**
     * Calculate assessment metrics for an employee
     */

    public function calculateAssessmentMetrics($employeeId, $startDate, $endDate)
    {
        // Get assessments with their feedbacks for this employee
        $assessments = CroxxAssessment::whereHas('feedbacks', function($query) use ($employeeId) {
            $query->where('employee_id', $employeeId);
        })
        ->whereBetween('created_at', [$startDate, $endDate])
        ->with(['feedbacks' => function($query) use ($employeeId) {
            $query->where('employee_id', $employeeId);
        }])
        ->get();

        $completedCount = $assessments->count(); // All retrieved assessments have feedbacks
        $totalCount = $assessments->count();


        return [
            'count' => $totalCount,
            'average_score' => $assessments->avg('feedbacks.0.graded_score'),
            'completed' => $completedCount,
            'pending' => $totalCount - $completedCount,
            'completion_rate' => $totalCount > 0 ? ($completedCount / $totalCount) * 100 : 0,
            'trend' => $this->calculateTrend('assessments', $employeeId, $startDate, $endDate),
            'details' => $assessments->map(fn($a) => [
                'name' => $a->name,
                'score' => !$a->feedbacks->isEmpty() && isset($a->feedbacks[0]->graded_score) ? $a->feedbacks[0]->graded_score : 0,
                // 'score' => $a->feedbacks->first()->graded_score ?? 0,
                'date' => $a->created_at
            ])
        ];
    }

    /**
     * Calculate project metrics for an employee
     */
    public function calculateProjectMetrics($employeeId, $startDate, $endDate)
    {
        // Get projects with their project_goals that have this employee assigned
        $projects = Project::whereHas('tasks.assigned', function($query) use ($employeeId) {
            $query->where('employee_id', $employeeId);
        })
        ->whereBetween('created_at', [$startDate, $endDate])
        ->with(['tasks' => function($query) {
            $query->with('assigned');
        }])
        ->get();

        // Collect all relevant project_goals across all projects
        $allProjectGoals = collect();

        foreach ($projects as $project) {
            foreach ($project->tasks as $projectGoal) {
                // Check if this employee is assigned to this project goal
                $isAssigned = $projectGoal->assigned->where('employee_id', $employeeId)->isNotEmpty();

                if ($isAssigned) {
                    $allProjectGoals->push($projectGoal);
                }
            }
        }

        // Calculate metrics
        $completedGoals = $allProjectGoals->where('status', 'completed')->count();
        $inProgressGoals = $allProjectGoals->where('status', 'in_progress')->count();
        $totalGoals = $allProjectGoals->count();
        $completionRate = $totalGoals > 0 ? ($completedGoals / $totalGoals) * 100 : 0;
        $onTimeRate = $this->calculateOnTimeCompletionRate($allProjectGoals);

        return [
            'project_count' => $projects->count(),
            'task_count' => $totalGoals,
            'completed_tasks' => $completedGoals,
            'in_progress_tasks' => $inProgressGoals,
            'completion_rate' => $completionRate,
            'on_time_completion_rate' => $onTimeRate,
            'trend' => $this->calculateTrend('projects', $employeeId, $startDate, $endDate),
            'details' => $projects->map(function($p) use ($employeeId) {
                // Filter project goals for this project that have this employee assigned
                $projectGoals = $p->tasks->filter(function($goal) use ($employeeId) {
                    return $goal->assigned->where('employee_id', $employeeId)->isNotEmpty();
                });

                return [
                    'name' => $p->name,
                    'tasks_total' => $projectGoals->count(),
                    'tasks_completed' => $projectGoals->where('status', 'completed')->count(),
                    'progress' => $p->progress
                ];
            })
        ];
    }

    /**
     * Calculate peer review metrics for an employee
     */
    public function calculatePeerReviewMetrics($employeeId, $startDate, $endDate)
    {
        $peerReviews = PeerReview::where('employee_id', $employeeId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with('feedback')
            ->get();

        $completedCount = $peerReviews->where('status', 'completed')->count();
        $totalCount = $peerReviews->count();
        $averageScore = $peerReviews->isEmpty() ? 0 :
            $peerReviews->avg(function($pr) {
                return $pr->feedback->avg('score') ?? 0;
            });

        return [
            'count' => $totalCount,
            'average_score' => $averageScore,
            'completed' => $completedCount,
            'pending' => $totalCount - $completedCount,
            'completion_rate' => $totalCount > 0 ? ($completedCount / $totalCount) * 100 : 0,
            'trend' => $this->calculateTrend('peer_reviews', $employeeId, $startDate, $endDate),
            'details' => $peerReviews->map(fn($pr) => [
                'reviewer' => $pr->reviewer->name ?? 'Unknown',
                'score' => $pr->feedback->avg('score') ?? 0,
                'date' => $pr->completed_at
            ])
        ];
    }

    /**
     * Calculate goal metrics for an employee
     */
    public function calculateGoalMetrics($employeeId, $startDate, $endDate)
    {
        $goals = Goal::where('employee_id', $employeeId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $achievedCount = $goals->where('status', 'done')->count();
        $inProgressCount = $goals->where('status', 'pending')->count();
        $totalCount = $goals->count();
        $completionRate = $totalCount > 0 ? ($achievedCount / $totalCount) * 100 : 0;

        // Calculate average progress for in-progress goals
        $avgProgress = $goals->where('status', 'pending')->avg('pending') ?? 0;

        return [
            'count' => $totalCount,
            'achieved' => $achievedCount,
            'in_progress' => $inProgressCount,
            'completion_rate' => $completionRate,
            'average_progress' => $avgProgress,
            'trend' => $this->calculateTrend('goals', $employeeId, $startDate, $endDate),
            'details' => $goals->map(fn($g) => [
                'title' => $g->title,
                'status' => $g->status,
                'progress' => $g->progress,
                'due_date' => $g->due_date
            ])
        ];
    }


    /**
     * Calculate competency metrics for an employee
     */
    public function calculateCompetencyMetrics($employeeId, $departmentId, $startDate, $endDate)
    {
        // info(['calculateCompetencyMetrics', $employeeId, $departmentId]);
        $mappings = DepartmentMapping::where('department_id', $departmentId)
            ->get();

        $competencyScores = [];

        foreach ($mappings as $mapping) {
            // Get single KPI achievement for this mapping
            $kpiAchievement = $this->calculateEmployeeKPIAchievement(
                $employeeId,
                $mapping,
                $startDate,
                $endDate
            );

            // Add to competency scores array
            $competencyScores[] = [
                'competency' => $mapping->competency, // Using competency field from the mapping
                'kpi_achievement' => [$kpiAchievement], // Wrap in array since we just have one achievement per mapping
                'score' => $kpiAchievement['achievement_rate'] // Use achievement rate directly
            ];
        }

        $averageScore = count($competencyScores) > 0 ?
            collect($competencyScores)->avg('score') : 0;

        return [
            'count' => count($competencyScores),
            'average_score' => $averageScore,
            'trend' => $this->calculateTrend('competencies', $employeeId, $startDate, $endDate),
            'details' => $competencyScores
        ];
    }

    /**
     * Calculate KPI achievement for an employee
     */
    public function calculateEmployeeKPIAchievement($employeeId, $mapping, $startDate, $endDate)
    {
        // Calculate actual value for this KPI
        $actual = $this->getKPIActualValue($employeeId, $mapping, $startDate, $endDate);
        // Use target_score from the mapping object based on the sample data
        $target = $mapping->target_score ?? 75;

        // Calculate achievement rate (capped at 100%)
        $achievementRate = $target > 0 ? min(($actual / $target) * 100, 100) : 0;

        // Return a single KPI achievement array
        return [
            'kpi_name' => $mapping?->competency,
            'kpi_type' => $mapping?->competency_role,
            'actual' => $actual,
            'target' => $target,
            'achievement_rate' => $achievementRate,
            'status' => $this->getKPIStatus($achievementRate)
        ];
    }

    /**
     * Get KPI actual value based on type
     */
    public function getKPIActualValue($employeeId, $kpi, $startDate, $endDate)
    {
        return match($kpi?->type) {
            'task_completion' => DB::table('tasks')
                ->where('assigned_to', $employeeId)
                ->whereBetween('completed_at', [$startDate, $endDate])
                ->where('status', 'completed')
                ->count(),
            'project_delivery' => DB::table('projects')
                ->whereHas('tasks', fn($q) => $q->where('assigned_to', $employeeId))
                ->whereBetween('completed_at', [$startDate, $endDate])
                ->where('status', 'completed')
                ->count(),
            'assessment_score' => CroxxAssessment::whereHas('feedbacks', fn($q) => $q->where('employee_id', $employeeId))
                ->whereBetween('created_at', [$startDate, $endDate])
                ->avg('feedbacks.graded_score') ?? 0,
            'peer_review_score' => PeerReview::where('employee_id', $employeeId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->with('feedback')
                ->get()
                ->avg(fn($pr) => $pr->feedback->avg('score') ?? 0) ?? 0,
            'goal_achievement' => Goal::where('employee_id', $employeeId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('status', 'achieved')
                ->count(),
            default => 0
        };
    }


    /**
     * Calculate overall score based on weighted sections
     */
    public function calculateOverallScore($sections)
    {
        $weightedScore = 0;

        foreach ($this->weights as $section => $weight) {
            if (isset($sections[$section])) {
                $sectionScore = match($section) {
                    'assessments', 'peer_reviews' => $sections[$section]['average_score'] ?? 0,
                    'goals' => $sections[$section]['completion_rate'] ?? 0,
                    'projects' => ($sections[$section]['completion_rate'] ?? 0) * 0.7 +
                                 ($sections[$section]['on_time_completion_rate'] ?? 0) * 0.3,
                    'competencies' => $sections[$section]['average_score'] ?? 0,
                    default => 0
                };

                $weightedScore += $sectionScore * $weight;
            }
        }

        return round($weightedScore, 1);
    }

    /**
     * Calculate on-time completion rate
     */
    public function calculateOnTimeCompletionRate($tasks)
    {
        $completedTasks = $tasks->where('status', 'completed');

        if ($completedTasks->isEmpty()) {
            return 0;
        }

        $onTimeTasks = $completedTasks->filter(function($task) {
            return Carbon::parse($task->completed_at)->lte(Carbon::parse($task->due_date));
        });

        return ($onTimeTasks->count() / $completedTasks->count()) * 100;
    }

    /**
     * Calculate goal distribution by type/category
     */
    public function calculateGoalDistribution($goals)
    {
        $categories = $goals->groupBy('category')->map->count();
        $statuses = $goals->groupBy('status')->map->count();

        return [
            'by_category' => $categories,
            'by_status' => $statuses
        ];
    }

    /**
     * Get KPI status based on achievement rate
     */
    public function getKPIStatus($achievementRate)
    {
        return match(true) {
            $achievementRate >= 95 => 'outstanding',
            $achievementRate >= 90 => 'excellent',
            $achievementRate >= 80 => 'very_good',
            $achievementRate >= 75 => 'good',
            $achievementRate >= 60 => 'satisfactory',
            $achievementRate >= 50 => 'average',
            $achievementRate >= 40 => 'below_average',
            $achievementRate >= 30 => 'poor',
            default => 'needs_improvement'
        };
    }

    /**
     * Get competency status based on score
     *
     * @param float $score The competency score (0-100)
     * @return string The competency status
     */
    public function getCompetencyStatus($score)
    {
        return match(true) {
            $score >= 95 => 'expert',
            $score >= 90 => 'highly_proficient',
            $score >= 85 => 'advanced',
            $score >= 75 => 'proficient',
            $score >= 65 => 'intermediate',
            $score >= 50 => 'developing',
            $score >= 40 => 'basic',
            $score >= 30 => 'beginner',
            default => 'needs_improvement'
        };
    }

    /**
     * Calculate performance trend for employee
     */
    public function calculateTrend($metricType, $employeeId, $currentStartDate, $currentEndDate)
    {
        // Get previous period
        $previousStartDate = (clone $currentStartDate)->subMonth();
        $previousEndDate = (clone $currentEndDate)->subMonth();

        // Get current period value
        $currentValue = $this->getTrendValue($metricType, $employeeId, $currentStartDate, $currentEndDate);

        // Get previous period value
        $previousValue = $this->getTrendValue($metricType, $employeeId, $previousStartDate, $previousEndDate);

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
     * Get trend value for employee based on metric type
     */
    public function getTrendValue($metricType, $employeeId, $startDate, $endDate)
    {
        return match($metricType) {
            'assessments' => $this->getEmployeeAssessmentTrend($employeeId, $startDate, $endDate),
            'peer_reviews' => PeerReview::where('employee_id', $employeeId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->with('feedback')
                ->get()
                ->avg(fn($pr) => $pr->feedback->avg('score') ?? 0) ?? 0,
            'goals' => Goal::where('employee_id', $employeeId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('status', 'achieved')
                ->count(),
            'projects' => $this->getEmployeeProjectTrend($employeeId, $startDate, $endDate),
            'competencies' => $this->getEmployeeCompetencyAverage($employeeId, $startDate, $endDate),
            default => 0
        };
    }

    /**
     * Get assessment trend value for an employee
     */
    private function getEmployeeAssessmentTrend($employeeId, $startDate, $endDate)
    {
        $assessments = CroxxAssessment::whereHas('feedbacks', function($query) use ($employeeId) {
            $query->where('employee_id', $employeeId);
        })
        ->whereBetween('created_at', [$startDate, $endDate])
        ->with(['feedbacks' => function($query) use ($employeeId) {
            $query->where('employee_id', $employeeId);
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
     * Get project trend value for an employee
     */
    private function getEmployeeProjectTrend($employeeId, $startDate, $endDate)
    {
        $projects = Project::whereHas('tasks.assigned', function($query) use ($employeeId) {
            $query->where('employee_id', $employeeId);
        })
        ->with(['tasks' => function($query) {
            $query->with('assigned');
        }])
        ->get();

        $completedCount = 0;

        foreach ($projects as $project) {
            foreach ($project->tasks as $task) {
                $isAssigned = $task->assigned->where('employee_id', $employeeId)->isNotEmpty();

                if ($isAssigned &&
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
     * Get employee competency average
     */
    public function getEmployeeCompetencyAverage($employeeId, $startDate, $endDate)
    {
        $employee = Employee::find($employeeId);

        if (!$employee) {
            return 0;
        }
        info(['calculateCompetencyAverage', $employeeId, $employee->job_code_id]);
        $mappings = DepartmentMapping::where('department_id', $employee->job_code_id)
            ->get();

        $competencyScores = [];
        foreach ($mappings as $mapping) {
            $kpiAchievements = $this->calculateEmployeeKPIAchievement(
                $employeeId,
                $mapping,
                $startDate,
                $endDate
            );

            $competencyScores[] = collect($kpiAchievements)->avg('achievement_rate') ?? 0;
        }

        return $competencyScores ? array_sum($competencyScores) / count($competencyScores) : 0;
    }


    /**
     * Generate insights for employee performance
     */
    public function generateEmployeeInsights($employee, $sections, $overallScore, $startDate, $endDate)
    {
        $insights = [];

        // Assessment insights
        if (isset($sections['assessments'])) {
            $assessmentScore = $sections['assessments']['average_score'] ?? 0;
            $completionRate = $sections['assessments']['completion_rate'] ?? 0;

            if ($assessmentScore >= 90) {
                $insights[] = "Exceptional assessment scores indicating strong technical knowledge.";
            } elseif ($assessmentScore < 70) {
                $insights[] = "Consider focused skill development to improve assessment scores.";
            }

            if ($completionRate < 80) {
                $insights[] = "Improve assessment completion rate to better evaluate skills.";
            }
        }

        // Peer review insights
        if (isset($sections['peer_reviews'])) {
            $reviewScore = $sections['peer_reviews']['average_score'] ?? 0;
            $trend = $sections['peer_reviews']['trend']['direction'] ?? 'stable';

            if ($reviewScore >= 85) {
                $insights[] = "Well-regarded by peers with consistently positive feedback.";
            } elseif ($reviewScore < 65) {
                $insights[] = "Consider improving collaboration and communication with peers.";
            }

            if ($trend === 'down') {
                $insights[] = "Declining peer review scores may indicate interpersonal challenges.";
            }
        }

        // Goal insights
        if (isset($sections['goals'])) {
            $completionRate = $sections['goals']['completion_rate'] ?? 0;
            $inProgressCount = $sections['goals']['in_progress'] ?? 0;

            if ($completionRate >= 90) {
                $insights[] = "Excellent at achieving set goals and objectives.";
            } elseif ($completionRate < 60) {
                $insights[] = "Focus on goal completion to improve overall performance.";
            }

            if ($inProgressCount > 5) {
                $insights[] = "Consider prioritizing current goals before taking on new ones.";
            }
        }

        // Project insights
        if (isset($sections['projects'])) {
            $taskCompletion = $sections['projects']['completion_rate'] ?? 0;
            $onTimeRate = $sections['projects']['on_time_completion_rate'] ?? 0;

            if ($onTimeRate >= 90) {
                $insights[] = "Consistently delivers project tasks on or ahead of schedule.";
            } elseif ($onTimeRate < 70) {
                $insights[] = "Work on time management to improve on-time task delivery.";
            }

            if ($taskCompletion < 60) {
                $insights[] = "Focus on completing assigned project tasks to improve contribution.";
            }
        }

        // Competency insights
        if (isset($sections['competencies'])) {
            $competencyScore = $sections['competencies']['average_score'] ?? 0;
            $trend = $sections['competencies']['trend']['direction'] ?? 'stable';

            if ($competencyScore >= 85) {
                $insights[] = "Demonstrates strong proficiency in key competencies for the role.";
            } elseif ($competencyScore < 65) {
                $insights[] = "Targeted development in core competencies could enhance performance.";
            }

            if ($trend === 'up') {
                $insights[] = "Showing positive development in role-specific competencies.";
            }
        }

        // Overall performance insights
        if ($overallScore >= 90) {
            $insights[] = "Outstanding overall performance across all evaluation areas.";
        } elseif ($overallScore >= 80) {
            $insights[] = "Strong performer with consistent results across multiple areas.";
        } elseif ($overallScore >= 70) {
            $insights[] = "Solid performance with opportunities for targeted improvement.";
        } elseif ($overallScore < 60) {
            $insights[] = "Performance improvement plan recommended to address key areas.";
        }

        // Check for balanced or unbalanced performance
        $scores = [
            $sections['assessments']['average_score'] ?? 0,
            $sections['peer_reviews']['average_score'] ?? 0,
            $sections['goals']['completion_rate'] ?? 0,
            $sections['projects']['completion_rate'] ?? 0,
            $sections['competencies']['average_score'] ?? 0
        ];

        $stdDev = $this->calculateStandardDeviation($scores);

        if ($stdDev > 20) {
            $insights[] = "Performance varies significantly across different areas; consider more balanced development.";
        } elseif ($stdDev < 10 && $overallScore >= 75) {
            $insights[] = "Shows consistently strong performance across all evaluation categories.";
        }

        return $insights;
    }


    /**
     * Get employee historical performance
     */
    public function getEmployeeHistoricalPerformance($employeeId, $year)
    {
        $historicalRecords = PerformanceRecord::where('recordable_id', $employeeId)
            ->where('recordable_type', Employee::class)
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


    /**
     * Calculate standard deviation of an array of values
     */
    public function calculateStandardDeviation($values)
    {
        $count = count($values);

        if ($count === 0) {
            return 0;
        }

        $average = array_sum($values) / $count;
        $squaredDiffs = array_map(fn($v) => pow($v - $average, 2), $values);
        $variance = array_sum($squaredDiffs) / $count;

        return sqrt($variance);
    }
}

// Waste


    /**
     * Calculate department project metrics
     */
    // public function calculateDepartmentProjectMetrics($departmentId, $startDate, $endDate)
    // {
    //     $employeeIds = Employee::where('job_code_id', $departmentId)->pluck('id');

    //     $projects = Project::whereHas('tasks.assigned', function($query) use ($employeeIds) {
    //         $query->whereIn('assigned', $employeeIds);
    //     })
    //     ->whereBetween('created_at', [$startDate, $endDate])
    //     ->with(['tasks' => function($query) use ($employeeIds) {
    //         $query->whereIn('assigned', $employeeIds);
    //     }])
    //     ->get();

    //     $tasks = collect($projects->pluck('tasks')->flatten());

    //     $completedCount = $tasks->where('status', 'completed')->count();
    //     $inProgressCount = $tasks->where('status', 'in_progress')->count();
    //     $totalCount = $tasks->count();

    //     // Calculate on-time completion rate
    //     $onTimeRate = $this->calculator->calculateOnTimeCompletionRate($tasks);

    //     // Calculate completion rate per employee
    //     $employeeCompletionRates = [];
    //     foreach ($employeeIds as $empId) {
    //         $empTasks = $tasks->where('assigned', $empId);

    //         if ($empTasks->isNotEmpty()) {
    //             $empCompleted = $empTasks->where('status', 'completed')->count();
    //             $empRate = ($empCompleted / $empTasks->count()) * 100;
    //             $employeeCompletionRates[$empId] = $empRate;
    //         }
    //     }

    //     // Calculate department average completion rate
    //     $avgCompletionRate = count($employeeCompletionRates) > 0 ?
    //         array_sum($employeeCompletionRates) / count($employeeCompletionRates) : 0;

    //     return [
    //         'project_count' => $projects->count(),
    //         'task_count' => $totalCount,
    //         'completed_tasks' => $completedCount,
    //         'in_progress_tasks' => $inProgressCount,
    //         'completion_rate' => $avgCompletionRate,
    //         'on_time_completion_rate' => $onTimeRate,
    //         'trend' => $this->calculateDepartmentTrend('projects', $departmentId, $startDate, $endDate),
    //         'employee_participation_rate' => count($employeeCompletionRates) > 0 ?
    //             (count($employeeCompletionRates) / count($employeeIds)) * 100 : 0,
    //         'project_details' => $projects->map(fn($p) => [
    //             'name' => $p?->title,
    //             'status' => $p->status,
    //             'progress' => $p->progress,
    //             'task_count' => $p->tasks->count()
    //         ])
    //     ];
    // }

    // /**
    //  * Calculate department competency metrics
    //  */
    // public function calculateDepartmentCompetencyMetrics($departmentId, $startDate, $endDate)
    // {
    //     $employeeIds = Employee::where('job_code_id', $departmentId)->pluck('id');

    //     $mappings = DepartmentMapping::where('department_id', $departmentId)
    //         ->get();

    //     $competencySummary = [];
    //     foreach ($mappings as $mapping) {
    //         $employeeScores = [];

    //         foreach ($employeeIds as $empId) {
    //             $kpiAchievement = $this->calculator->calculateEmployeeKPIAchievement(
    //                 $empId,
    //                 $mapping,
    //                 $startDate,
    //                 $endDate
    //             );

    //             // Get the achievement rate directly from the single result
    //             $score = $kpiAchievement['achievement_rate'];
    //             $employeeScores[] = $score;
    //         }

    //         $avgScore = count($employeeScores) > 0 ? array_sum($employeeScores) / count($employeeScores) : 0;

    //         // Access competency directly from the mapping based on sample data
    //         $competencySummary[] = [
    //             'competency' => $mapping->competency, // Changed from $mapping->competency->name
    //             'average_score' => $avgScore,
    //             'kpi_count' => 1, // Each mapping has one KPI according to the sample
    //             'employee_count' => count($employeeScores),
    //             'status' => $this->calculator->getCompetencyStatus($avgScore)
    //         ];
    //     }

    //     $overallScore = count($competencySummary) > 0 ?
    //         collect($competencySummary)->avg('average_score') : 0;

    //     return [
    //         'count' => count($competencySummary),
    //         'average_score' => $overallScore,
    //         'trend' => $this->calculateDepartmentTrend('competencies', $departmentId, $startDate, $endDate),
    //         'details' => $competencySummary
    //     ];
    // }
