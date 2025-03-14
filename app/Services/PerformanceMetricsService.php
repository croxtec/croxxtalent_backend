<?php

namespace App\Services;

use App\Models\Competency\DepartmentMapping;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use  App\Services\PerformanceCalculatorService;
use  App\Services\DepartmentPerformanceService;


class PerformanceMetricsService {

    protected $calculator;
    protected $teamCalculator;

    public function __construct(PerformanceCalculatorService $calculator,
         DepartmentPerformanceService $teamCalculator)
    {
        $this->calculator = $calculator;
        $this->teamCalculator = $teamCalculator;
    }

    /**
     * Calculate employee performance for a specific month
     */
    public function employeeKPIPerformance(Employee $employee, $year, $month)
    {
        $startDate = Carbon::create(Carbon::now()->year - 1, 1, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        // Calculate metrics for each section
        $sections = [
            'assessments' => $this->calculator->calculateAssessmentMetrics($employee->id, $startDate, $endDate),
            'projects' => $this->calculator->calculateProjectMetrics($employee->id, $startDate, $endDate),
        ];

        $overallScore = $this->calculator->calculateOverallScore($sections);

        $insights = $this->calculator->generateEmployeeInsights($employee, $sections, $overallScore, $startDate, $endDate);


        return [
            // 'employee' => $employee,
            'month' => $month,
            'year' => $year,
            'overall_score' => $overallScore,
            'sections' => $sections,
            'insights' => $insights,
            'historical' => $historical ?? []
        ];
    }

    public function employeeFeedbackPerformance(Employee $employee, $year, $month)
    {
        $startDate = Carbon::create('2024', $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();
        $mappings = DepartmentMapping::where('department_id', $employee->job_code_id)
                ->whereNotNull('competency')
                ->get();

        // Calculate metrics for each section
        $sections = [
            'peer_reviews' => $this->calculator->calculatePeerReviewMetrics($employee->id, $startDate, $endDate),
            'goals' => $this->calculator->calculateGoalMetrics($employee->id, $startDate, $endDate),
            'competencies' => $this->calculator->calculateCompetencyMetrics($employee->id, $employee->job_code_id, $startDate, $endDate)
        ];

        // Calculate overall score
        $overallScore = $this->calculator->calculateOverallScore($sections);
        // Add KPI achievement metrics
        $kpiAchievement = []; //$this->calculator->calculateEmployeeKPIAchievement($employee->id, $mappings, $startDate, $endDate);
        // Add performance insights
        $insights = $this->calculator->generateEmployeeInsights($employee, $sections, $overallScore, $startDate, $endDate);

        return [
            'employee' => $employee,
            'month' => $month,
            'year' => $year,
            'overall_score' => $overallScore,
            'sections' => $sections,
            'kpi_achievement' => $kpiAchievement,
            'insights' => $insights,
        ];
    }

     /**
     * Calculate department performance for a specific month
     */
    public function calculateDepartmentAnalysis($department, $year, $month)
    {
        $startDate = Carbon::create(Carbon::now()->year - 1, 1, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        $sections = [
            'competencies' => $this->teamCalculator->calculateDepartmentCompetencyMetrics($department->id, $startDate, $endDate),
            'trainings' => $this->teamCalculator->calculateDepartmentTrainingMetrics($department->id, $startDate, $endDate),
        ];

        $overallScore = $this->calculator->calculateOverallScore($sections);

        $insights = $this->teamCalculator->generateDepartmentInsights($department, $sections, $overallScore);

        return [
            // 'department' => $department,
            'month' => $month,
            'year' => $year,
            'overall_score' => $overallScore,
            'sections' => $sections,
            'insights' => $insights,
        ];
    }

    /**
     * Calculate department performance for a specific month
     */
    public function calculateDepartmentPerformance($department, $year, $month)
    {
       $startDate = Carbon::create(Carbon::now()->year - 1, 1, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        // Get all employees in department
        $employees = Employee::where('job_code_id', $department->id)->get();
        $employeeCount = $employees->count();

        // Calculate department sections
        $sections = [
            // 'assessments' => $this->teamCalculator->calculateDepartmentAssessmentMetrics($department->id, $startDate, $endDate),
            'peer_reviews' => $this->teamCalculator->calculateDepartmentPeerReviewMetrics($department->id, $startDate, $endDate),
            'goals' => $this->teamCalculator->calculateDepartmentGoalMetrics($department->id, $startDate, $endDate),
            'projects' => $this->teamCalculator->calculateDepartmentProjectMetrics($department->id, $startDate, $endDate),
            'competencies' => $this->teamCalculator->calculateDepartmentCompetencyMetrics($department->id, $startDate, $endDate)
        ];

        // Calculate overall score
        $overallScore = $this->calculator->calculateOverallScore($sections);

        // Add KPI achievement metrics
        $kpiAchievement = $this->teamCalculator->calculateDepartmentKPIAchievement($department, $startDate, $endDate);

        // Add performance insights
        $insights = $this->teamCalculator->generateDepartmentInsights($department, $sections, $overallScore, $kpiAchievement);

        return [
            // 'department' => $department,
            'month' => $month,
            'year' => $year,
            'employee_count' => $employeeCount,
            'overall_score' => $overallScore,
            'sections' => $sections,
            'kpi_achievement' => $kpiAchievement,
            'insights' => $insights,
        ];
    }

    public function calculateDepartmentHistoricalPerformance($department, $year){
        // $historical = $this->teamCalculator->getDepartmentHistoricalPerformance($department->id, $year);
        $summary = $this->teamCalculator->getDepartmentHistoricalSummary($department->id, $year);
        return $summary;
    }

    public function calculateEmployeeHistoricalPerformance($employee, $year){
        $categories = [
            'overall_score' => 'Overall',
            'goal_completion_rate' => 'Goal Completion',
            'peer_review_score' => 'Peer Review',
            'kpi_overall_achievement' => 'KPI Achievement'
        ];
        $summary = $this->calculator->getEmployeeHistoricalSummary($employee->id, $year, $categories);
        return $summary;
    }

    public function calculateEmployeeHistoricalKpi($employee, $year){
        $categories = [
            'overall_score' => 'Overall',
            'assessment_score' => 'Assessment',
            'project_completion_rate' => 'Project Completion',
            'training_score' => 'Training'
        ];
        $summary = $this->calculator->getEmployeeHistoricalSummary($employee->id, $year, $categories);
        return $summary;
    }


    /**
     * Run monthly performance calculations for all employees and departments
     */
    // public function calculateMonthlyPerformance($year = null, $month = null)
    // {
    //     // Default to previous month
    //     if (!$year || !$month) {
    //         $date = Carbon::now()->subMonth();
    //         $year = $date->year;
    //         $month = $date->month;
    //     }

    //     DB::beginTransaction();
    //     try {
    //         // Calculate for all employees
    //         Employee::chunk(100, function($employees) use ($year, $month) {
    //             foreach ($employees as $employee) {
    //                 $this->calculator->calculateAssessmentMetrics($employee, $year, $month);
    //             }
    //         });

    //         // Calculate for all departments
    //         EmployerJobcode::chunk(50, function($departments) use ($year, $month) {
    //             foreach ($departments as $department) {
    //                 $this->calculateDepartmentPerformance($department, $year, $month);
    //             }
    //         });

    //         DB::commit();
    //         return true;
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         \Log::error("Error calculating monthly performance: " . $e->getMessage());
    //         throw $e;
    //     }
    // }
}
   // Create or update performance record
        // $performanceRecord = PerformanceRecord::updateOrCreate(
        //     [
        //         'recordable_id' => $employee->id,
        //         'recordable_type' => Employee::class,
        //         'year' => $year,
        //         'month' => $month,
        //     ],
        //     [
        //         'overall_score' => $overallScore,
        //         'sections' => $sections,
        //         'insights' => $insights,
        //         'historical' => $historical,
        //         'metadata' => [
        //             'department_id' => $employee->job_code_id,
        //             'position' => $employee->position,
        //             'calculated_at' => now()
        //         ]
        //     ]
        // );
