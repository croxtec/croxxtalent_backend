<?php

namespace App\Http\Controllers\Api\v2\Company;

use App\Http\Controllers\Controller;
use App\Models\Assessment\CroxxAssessment;
use App\Models\Assessment\PeerReview;
use App\Models\Employee;
use App\Models\Goal;
use App\Models\Project\Project;
use App\Models\Competency\DepartmentMapping;
use App\Models\EmployerJobcode;
use App\Models\Project\ProjectGoal;
use Carbon\Carbon;
use Illuminate\Http\Request;
use DB;

class PerformanceController extends Controller
{
    /**
     * Get employee performance breakdown
     */
    public function getEmployeePerformance(Request $request)
    {
        try {
            $employeeId = $request->input('uid');
            $month = $request->input('month', Carbon::now()->month);
            $year = $request->input('year', Carbon::now()->year);

            $startDate = Carbon::create($year, $month, 1)->startOfMonth();
            $endDate = Carbon::create($year, $month, 1)->endOfMonth();

            // Get employee with department
            $employee = Employee::with('department')->where('code', $employeeId)->firstOrFail();

            $performance = [
                'employee' => $employee,
                'month' => $month,
                'year' => $year,
                'sections' => [
                    'assessments' => $this->calculateAssessmentPerformance($employeeId, $startDate, $endDate),
                    'peer_reviews' => $this->calculatePeerReviewPerformance($employeeId, $startDate, $endDate),
                    'goals' => $this->calculateGoalsPerformance($employeeId, $startDate, $endDate),
                    // 'projects' => $this->calculateProjectPerformance($employeeId, $startDate, $endDate),
                    'competencies' => $this->calculateCompetencyPerformance($employeeId, $employee->department_id, $startDate, $endDate)
                ]
            ];

            // Calculate overall performance score
            $performance['overall_score'] = $this->calculateOverallScore($performance['sections']);

            // Add historical tracking
            $performance['historical'] = $this->getHistoricalPerformance($employeeId, $year);

            return response()->json([
                'status' => true,
                'data' => $performance
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Error calculating performance: " . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get department performance breakdown
     */
    public function getDepartmentPerformance(Request $request)
    {
        try {
            $departmentId = $request->input('uid');
            $month = $request->input('month', Carbon::now()->month);
            $year = $request->input('year', Carbon::now()->year);

            $startDate = Carbon::create($year, $month, 1)->startOfMonth();
            $endDate = Carbon::create($year, $month, 1)->endOfMonth();

            $department = EmployerJobcode::with(['technical_skill','soft_skill'])
                                ->findOrFail($departmentId);

            // Get all employees in department
            $employees = Employee::where('job_code_id', $departmentId)->get();

            $performance = [
                'department' => $department,
                'month' => $month,
                'year' => $year,
                'employee_count' => $employees->count(),
                'sections' => [
                    'assessments' => $this->calculateDepartmentAssessments($departmentId, $startDate, $endDate),
                    'peer_reviews' => $this->calculateDepartmentPeerReviews($departmentId, $startDate, $endDate),
                    'goals' => $this->calculateDepartmentGoals($departmentId, $startDate, $endDate),
                    'projects' => $this->calculateDepartmentProjects($departmentId, $startDate, $endDate),
                    'competencies' => $this->calculateDepartmentCompetencies($departmentId, $startDate, $endDate)
                ]
            ];

            // Calculate overall department score
            $performance['overall_score'] = $this->calculateOverallScore($performance['sections']);

            // Add KPI achievement metrics
            $performance['kpi_achievement'] = $this->calculateKPIAchievement($department, $startDate, $endDate);

            // Add historical tracking
            $performance['historical'] = $this->getDepartmentHistoricalPerformance($departmentId, $year);

            return response()->json([
                'status' => true,
                'data' => $performance
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Error calculating department performance: " . $e->getMessage()
            ], 500);
        }
    }

    private function calculateAssessmentPerformance($employeeId, $startDate, $endDate)
    {
        $assessments = CroxxAssessment::whereHas('feedbacks', function($query) use ($employeeId) {
            $query->where('employee_id', $employeeId);
        })
        ->whereBetween('created_at', [$startDate, $endDate])
        ->with(['feedbacks' => function($query) use ($employeeId) {
            $query->where('employee_id', $employeeId);
        }])
        ->get();

        return [
            'count' => $assessments->count(),
            'average_score' => $assessments->avg('feedbacks.0.graded_score'),
            'completed' => $assessments->filter(fn($a) => !empty($a->feedbacks))->count(),
            'pending' => $assessments->filter(fn($a) => empty($a->feedbacks))->count(),
            'details' => $assessments->map(fn($a) => [
                'name' => $a->name,
                'score' => $a->feedbacks->first()->graded_score ?? 0,
                'date' => $a->created_at
            ])
        ];
    }

    private function calculatePeerReviewPerformance($employeeId, $startDate, $endDate)
    {
        $peerReviews = PeerReview::where('employee_id', $employeeId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with('feedback')
            ->get();

        return [
            'count' => $peerReviews->count(),
            'average_score' => $peerReviews->avg('feedback.score'),
            'completed' => $peerReviews->where('status', 'completed')->count(),
            'pending' => $peerReviews->where('status', 'pending')->count(),
            'details' => $peerReviews->map(fn($pr) => [
                'reviewer' => $pr->reviewer->name,
                'score' => $pr->feedback->avg('score'),
                'date' => $pr->completed_at
            ])
        ];
    }

    private function calculateGoalsPerformance($employeeId, $startDate, $endDate)
    {
        $goals = Goal::where('employee_id', $employeeId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        return [
            'count' => $goals->count(),
            'achieved' => $goals->where('status', 'achieved')->count(),
            'in_progress' => $goals->where('status', 'in_progress')->count(),
            'completion_rate' => $goals->count() > 0 ?
                ($goals->where('status', 'achieved')->count() / $goals->count()) * 100 : 0,
            'details' => $goals->map(fn($g) => [
                'title' => $g->title,
                'status' => $g->status,
                'progress' => $g->progress,
                'due_date' => $g->due_date
            ])
        ];
    }

    private function calculateProjectPerformance($employeeId, $startDate, $endDate)
    {
        $projects = Project::whereHas('tasks', function($query) use ($employeeId) {
            $query->where('assigned', $employeeId);
        })
        ->whereBetween('created_at', [$startDate, $endDate])
        ->with(['tasks' => function($query) use ($employeeId) {
            $query->where('assigned', $employeeId);
        }])
        ->get();

        $tasks = collect($projects->pluck('tasks')->flatten());

        return [
            'project_count' => $projects->count(),
            'task_count' => $tasks->count(),
            'completed_tasks' => $tasks->where('status', 'completed')->count(),
            'in_progress_tasks' => $tasks->where('status', 'in_progress')->count(),
            'completion_rate' => $tasks->count() > 0 ?
                ($tasks->where('status', 'completed')->count() / $tasks->count()) * 100 : 0,
            'on_time_completion_rate' => $this->calculateOnTimeCompletionRate($tasks),
            'details' => $projects->map(fn($p) => [
                'name' => $p->name,
                'tasks_total' => $p->tasks->count(),
                'tasks_completed' => $p->tasks->where('status', 'completed')->count(),
                'progress' => $p->progress
            ])
        ];
    }

    private function calculateCompetencyPerformance($employeeId, $departmentId=80, $startDate, $endDate)
    {
        $mappings = DepartmentMapping::where('department_id', $departmentId)
            ->with(['competencyKPIs', 'competency'])
            ->get();

        $competencyScores = [];
        foreach ($mappings as $mapping) {
            $kpiAchievements = $this->calculateEmployeeKPIAchievement(
                $employeeId,
                $mapping->competencyKPIs,
                $startDate,
                $endDate
            );

            $competencyScores[] = [
                'competency' => $mapping->competency->name,
                'kpi_achievement' => $kpiAchievements,
                'score' => collect($kpiAchievements)->avg('achievement_rate')
            ];
        }

        return [
            'count' => count($competencyScores),
            'average_score' => collect($competencyScores)->avg('score'),
            'details' => $competencyScores
        ];
    }

    private function calculateOverallScore($sections)
    {
        $weights = [
            'assessments' => 0.2,
            'peer_reviews' => 0.2,
            'goals' => 0.2,
            'projects' => 0.2,
            'competencies' => 0.2
        ];

        $score = 0;
        foreach ($sections as $key => $section) {
            $sectionScore = match($key) {
                'assessments' => $section['average_score'] ?? 0,
                'peer_reviews' => $section['average_score'] ?? 0,
                'goals' => $section['completion_rate'] ?? 0,
                'projects' => $section['completion_rate'] ?? 0,
                'competencies' => $section['average_score'] ?? 0,
                default => 0
            };

            $score += ($sectionScore * $weights[$key]);
        }

        return round($score, 2);
    }

    private function getHistoricalPerformance($employeeId, $year)
    {
        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $startDate = Carbon::create($year, $i, 1)->startOfMonth();
            $endDate = Carbon::create($year, $i, 1)->endOfMonth();

            if ($endDate->isFuture()) {
                break;
            }

            $sections = [
                'assessments' => $this->calculateAssessmentPerformance($employeeId, $startDate, $endDate),
                'peer_reviews' => $this->calculatePeerReviewPerformance($employeeId, $startDate, $endDate),
                'goals' => $this->calculateGoalsPerformance($employeeId, $startDate, $endDate),
                // 'projects' => $this->calculateProjectPerformance($employeeId, $startDate, $endDate),
                // 'competencies' => $this->calculateCompetencyPerformance($employeeId, $startDate, $endDate)
            ];

            $months[] = [
                'month' => $i,
                'score' => $this->calculateOverallScore($sections),
                'sections' => $sections
            ];
        }

        return $months;
    }

    private function calculateOnTimeCompletionRate($tasks)
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

    private function calculateEmployeeKPIAchievement($employeeId, $kpis, $startDate, $endDate)
    {
        return $kpis->map(function($kpi) use ($employeeId, $startDate, $endDate) {
            $actual = $this->getKPIActualValue($employeeId, $kpi, $startDate, $endDate);
            $target = $kpi->target_value;

            return [
                'kpi_name' => $kpi->name,
                'actual' => $actual,
                'target' => $target,
                'achievement_rate' => $target > 0 ? min(($actual / $target) * 100, 100) : 0
            ];
        });
    }

    private function getKPIActualValue($employeeId, $kpi, $startDate, $endDate)
    {
        // Implement your KPI measurement logic here
        // This should be customized based on your KPI types and measurement methods
        return match($kpi->type) {
            // 'task_completion' => ProjectGoal::where('assigned', $employeeId)
            //     ->whereBetween('completed_at', [$startDate, $endDate])
            //     ->count(),
            // 'project_delivery' => Project::whereHas('tasks', fn($q) => $q->where('assigned', $employeeId))
            //     ->whereBetween('completed_at', [$startDate, $endDate])
            //     ->where('status', 'completed')
            //     ->count(),
            'assessment_score' => CroxxAssessment::whereHas('feedbacks', fn($q) => $q->where('employee_id', $employeeId))
                ->whereBetween('created_at', [$startDate, $endDate])
                ->avg('feedbacks.graded_score'),
            default => 0
        };
    }
}
