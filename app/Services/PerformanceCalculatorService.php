<?php

namespace App\Models\Performance;

use App\Models\Employee;
use App\Models\EmployerJobcode;
use App\Models\PerformanceRecord;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class PerformanceCalculatorService
{
    public function calculateEmployeePerformance01(Employee $employee, $year, $month)
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        // Calculate assessment scores
        $assessmentData = DB::table('employer_assessment_feedback')
            ->where('employee_id', $employee->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('
                AVG(graded_score) as avg_score,
                COUNT(*) as total_count,
                SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed_count
            ')
            ->first();

        // Calculate peer review scores
        $peerReviewData = DB::table('peer_reviews')
            ->where('employee_id', $employee->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('
                AVG(score) as avg_score,
                COUNT(*) as total_count,
                SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed_count
            ')
            ->first();

        // Calculate goals achievement
        $goalsData = DB::table('goals')
            ->where('employee_id', $employee->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('
                COUNT(*) as total_count,
                SUM(CASE WHEN status = "achieved" THEN 1 ELSE 0 END) as achieved_count
            ')
            ->first();

        // Calculate project/task performance
        $tasksData = DB::table('tasks')
            ->where('assigned_to', $employee->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('
                COUNT(*) as total_count,
                SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed_count,
                SUM(CASE WHEN status = "completed" AND completed_at <= due_date THEN 1 ELSE 0 END) as on_time_count
            ')
            ->first();

        // Create or update performance record
        return PerformanceRecord::updateOrCreate(
            [
                'recordable_id' => $employee->id,
                'recordable_type' => Employee::class,
                'year' => $year,
                'month' => $month,
            ],
            [
                // Assessment metrics
                'assessment_score' => $assessmentData->avg_score ?? 0,
                'assessment_completion_rate' => $assessmentData->total_count > 0
                    ? ($assessmentData->completed_count / $assessmentData->total_count) * 100
                    : 0,

                // Peer review metrics
                'peer_review_score' => $peerReviewData->avg_score ?? 0,
                'peer_review_completion_rate' => $peerReviewData->total_count > 0
                    ? ($peerReviewData->completed_count / $peerReviewData->total_count) * 100
                    : 0,

                // Goals metrics
                'goals_completion_rate' => $goalsData->total_count > 0
                    ? ($goalsData->achieved_count / $goalsData->total_count) * 100
                    : 0,
                'goals_achieved_count' => $goalsData->achieved_count ?? 0,
                'goals_total_count' => $goalsData->total_count ?? 0,

                // Project metrics
                'project_completion_rate' => $tasksData->total_count > 0
                    ? ($tasksData->completed_count / $tasksData->total_count) * 100
                    : 0,
                'project_on_time_rate' => $tasksData->completed_count > 0
                    ? ($tasksData->on_time_count / $tasksData->completed_count) * 100
                    : 0,
                'tasks_completed_count' => $tasksData->completed_count ?? 0,
                'tasks_total_count' => $tasksData->total_count ?? 0,

                // Calculate overall score (weighted average)
                'overall_score' => $this->calculateOverallScore([
                    $assessmentData->avg_score ?? 0,
                    $peerReviewData->avg_score ?? 0,
                    $goalsData->total_count > 0 ? ($goalsData->achieved_count / $goalsData->total_count) * 100 : 0,
                    $tasksData->total_count > 0 ? ($tasksData->completed_count / $tasksData->total_count) * 100 : 0
                ])
            ]
        );
    }

    private function calculateOverallScore(array $scores)
    {
        $validScores = array_filter($scores, fn($score) => $score > 0);
        return count($validScores) > 0 ? array_sum($validScores) / count($validScores) : 0;
    }

    // Other methods...

    public function calculateMonthlyPerformance()
    {
        $date = Carbon::now()->subMonth(); // Calculate for previous month
        $year = $date->year;
        $month = $date->month;

        DB::beginTransaction();
        try {
            // Calculate for all employees
            Employee::chunk(100, function($employees) use ($year, $month) {
                foreach ($employees as $employee) {
                    $this->calculateEmployeePerformance($employee, $year, $month);
                }
            });

            // Calculate for all departments
            EmployerJobcode::chunk(50, function($departments) use ($year, $month) {
                foreach ($departments as $department) {
                    $this->calculateDepartmentPerformance($department, $year, $month);
                }
            });

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Error calculating monthly performance: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Calculate and store employee performance
     */
    private function calculateEmployeePerformance(Employee $employee, $year, $month)
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        // Calculate metrics for each section
        $metrics = [
            'assessments' => $this->calculateAssessmentMetrics($employee->id, $startDate, $endDate),
            'peer_reviews' => $this->calculatePeerReviewMetrics($employee->id, $startDate, $endDate),
            'goals' => $this->calculateGoalMetrics($employee->id, $startDate, $endDate),
            'projects' => $this->calculateProjectMetrics($employee->id, $startDate, $endDate),
            'competencies' => $this->calculateCompetencyMetrics($employee->id, $startDate, $endDate)
        ];

        // Calculate overall score
        $overallScore = $this->calculateOverallScore($metrics);

        // Store the performance record
        PerformanceRecord::create([
            'recordable_id' => $employee->id,
            'recordable_type' => Employee::class,
            'year' => $year,
            'month' => $month,
            'overall_score' => $overallScore,
            'metrics' => $metrics,
            'metadata' => [
                'department_id' => $employee->department_id,
                'position' => $employee->position,
                'calculated_at' => now()
            ]
        ]);
    }

    private function calculateAssessmentMetrics($employeeId, $startDate, $endDate)
    {
        // Calculate only the necessary metrics to store
        return [
            'average_score' => DB::table('employer_assessment_feedback')
                ->where('employee_id', $employeeId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->avg('graded_score') ?? 0,
            'completion_rate' => $this->calculateCompletionRate(
                'employer_assessment_feedback',
                $employeeId,
                $startDate,
                $endDate
            ),
            'count' => DB::table('employer_assessment_feedback')
                ->where('employee_id', $employeeId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count()
        ];
    }

    private function calculatePeerReviewMetrics($employeeId, $startDate, $endDate)
    {
        return [
            'average_score' => DB::table('peer_reviews')
                ->join('peer_review_feedback', 'peer_reviews.id', '=', 'peer_review_feedback.peer_review_id')
                ->where('peer_reviews.employee_id', $employeeId)
                ->whereBetween('peer_reviews.created_at', [$startDate, $endDate])
                ->avg('peer_review_feedback.score') ?? 0,
            'completion_rate' => DB::table('peer_reviews')
                ->where('employee_id', $employeeId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('status', 'completed')
                ->count() / DB::table('peer_reviews')
                ->where('employee_id', $employeeId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count() * 100 ?? 0
        ];
    }

    private function calculateGoalMetrics($employeeId, $startDate, $endDate)
    {
        $goals = DB::table('goals')
            ->where('employee_id', $employeeId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        return [
            'completion_rate' => $goals->count() > 0 ?
                ($goals->where('status', 'completed')->count() / $goals->count()) * 100 : 0,
            'count' => $goals->count(),
            'achieved_count' => $goals->where('status', 'completed')->count()
        ];
    }

    private function calculateProjectMetrics($employeeId, $startDate, $endDate)
    {
        $tasks = DB::table('tasks')
            ->where('assigned_to', $employeeId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        return [
            'completion_rate' => $tasks->count() > 0 ?
                ($tasks->where('status', 'completed')->count() / $tasks->count()) * 100 : 0,
            'on_time_completion_rate' => $this->calculateOnTimeCompletionRate($tasks),
            'task_count' => $tasks->count(),
            'completed_count' => $tasks->where('status', 'completed')->count()
        ];
    }

    /**
     * Calculate and store department performance
     */
    private function calculateDepartmentPerformance(Department $department, $year, $month)
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        // Calculate department metrics
        $metrics = [
            'overall' => $this->calculateDepartmentOverallMetrics($department->id, $startDate, $endDate),
            'kpis' => $this->calculateDepartmentKPIMetrics($department->id, $startDate, $endDate),
            'employees' => $this->calculateDepartmentEmployeeMetrics($department->id, $startDate, $endDate)
        ];

        // Store the performance record
        PerformanceRecord::create([
            'recordable_id' => $department->id,
            'recordable_type' => EmployerJobcode::class,
            'year' => $year,
            'month' => $month,
            'overall_score' => $metrics['overall']['average_score'],
            'metrics' => $metrics,
            'metadata' => [
                'employee_count' => $metrics['employees']['count'],
                'calculated_at' => now()
            ]
        ]);
    }
}
