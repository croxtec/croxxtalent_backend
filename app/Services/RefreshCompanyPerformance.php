<?php

namespace App\Services;

use App\Models\Assessment\CroxxAssessment;
use App\Models\Assessment\EmployerAssessmentFeedback;
use App\Models\Competency\DepartmentMapping;
use App\Models\Employee;
use App\Models\Goal;

class RefreshCompanyPerformance {

    public function refreshEmployeesPerformance($employer)
    {
        try {
            $period = [now()->startOfMonth(), now()->endOfMonth()];
            // $default_department = $request->input('department');

            $employees = Employee::where('employer_id', $employer->id)
                ->select(['id', 'name', 'photo_url', 'code', 'performance'])
                ->get();

            $goals = Goal::where('employer_id', $employer->id)
                ->whereNull('archived_at')
                // ->whereBetween('created_at', $period)
                ->select(['id', 'employer_id', 'employee_id', 'type', 'status'])
                ->get();

            $feedbacks = EmployerAssessmentFeedback::where('employer_user_id', $employer->id)
                ->whereNull('archived_at')
                // ->whereBetween('created_at', $period)
                ->select(['id', 'employer_user_id', 'employee_id', 'graded_score', 'employee_score', 'total_score'])
                ->get();

            // Group goals by employee
            $goalsByEmployee = $goals->groupBy('employee_id');
            // Group feedbacks by employee
            $feedbackByEmployee = $feedbacks->groupBy('employee_id');

            // return compact('goalsByEmployee', 'feedbackByEmployee');

            foreach ($employees as $employee) {
                $employeeGoals = $goalsByEmployee->get($employee->id, collect());
                $totalGoals = $employeeGoals->count();
                $completedGoals = $employeeGoals->where('status', 'done')->count();

                $goalPerformance = $totalGoals > 0 ? ($completedGoals / $totalGoals) * 100 : 0;

                // Calculate feedback score
                $employeeFeedbacks = $feedbackByEmployee->get($employee->id, collect());
                $totalFeedbackScore = $employeeFeedbacks->sum('graded_score');
                $feedbackCount = $employeeFeedbacks->count();

                $feedbackPerformance = $feedbackCount > 0 ? ($totalFeedbackScore / $feedbackCount) : 0;

                // Combine goal performance and feedback score (50% weight for each)
                $combinedPerformance = ($goalPerformance * 0.5) + ($feedbackPerformance * 0.5);

                $employee->performance = (int)$combinedPerformance;
                $employee->save();
            }

            return $employees;
        }  catch (\Exception $e) {
            // info('Error refreshing competencies performance: ' . $e->getMessage(), [
            //     'employer_id' => $employer->id,
            //     'trace' => $e->getTraceAsString()
            // ]);

            throw new \Exception("Error updating competency performances: " . $e->getMessage());
        }
    }

    public function refreshCompetenciesPerformance($employer)
    {
        try {
            $period = [now()->startOfMonth(), now()->endOfMonth()];

            $competencies = DepartmentMapping::where('employer_id', $employer->id)->get();

            foreach ($competencies as $competency) {
                // Get assessments for this specific competency
                $assessments = CroxxAssessment::whereHas('competencies', function ($query) use ($competency) {
                    $query->where('competency', $competency->competency);
                })->with(['feedbacks' => function ($query) use ($period) {
                    $query->whereBetween('created_at', $period)
                        ->where('is_published', 1)
                        ->orderBy('created_at', 'desc');
                }])->get();

                // Calculate performance metrics for this competency
                $totalScore = 0;
                $assessmentCount = 0;

                foreach ($assessments as $assessment) {
                    $competencyFeedbacks = $assessment->feedbacks;

                    if ($competencyFeedbacks->isNotEmpty()) {
                        // Use the latest feedback for each assessment
                        $latestFeedback = $competencyFeedbacks->first();
                        $totalScore += $latestFeedback->graded_score;
                        $assessmentCount++;
                    }
                }

                // Calculate and update competency performance
                $competencyPerformance = $assessmentCount > 0
                    ? round($totalScore / $assessmentCount, 2)
                    : 0;

                // Update the competency performance
                $competency->update([
                    'performance' => $competencyPerformance
                ]);

                // Log the performance update
                \Log::info("Competency {$competency->competency} performance updated", [
                    'competency_id' => $competency->id,
                    'department_id' => $competency->department_id,
                    'performance' => $competencyPerformance,
                    'total_assessments' => $assessmentCount,
                    'period' => $period
                ]);
            }

            return $competencies;

        } catch (\Exception $e) {
            // info('Error refreshing competencies performance: ' . $e->getMessage(), [
            //     'employer_id' => $employer->id,
            //     'trace' => $e->getTraceAsString()
            // ]);

            throw new \Exception("Error updating competency performances: " . $e->getMessage());
        }
    }


}
