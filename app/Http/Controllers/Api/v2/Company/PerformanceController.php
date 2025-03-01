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
use App\Services\PerformanceMetricsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use DB;

class PerformanceController extends Controller
{

    protected $performanceMetric;

    public function __construct(PerformanceMetricsService $performanceMetric)
    {
        $this->performanceMetric = $performanceMetric;
    }

    /**
     * Get employee performance breakdown
     */
    public function getEmployeeKPIPerformance(Request $request)
    {
        try {
            $employeeId = $request->input('uid');
            $month = $request->input('month', Carbon::now()->month);
            $year = $request->input('year', Carbon::now()->year);

            // Get employee with department
            $employee = Employee::with('department')->where('code', $employeeId)->firstOrFail();

            $employeePerformance = $this->performanceMetric->employeeKPIPerformance ($employee, $year, $month);

            return response()->json([
                'status' => true,
                'data' => $employeePerformance
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Error calculating performance: " . $e->getMessage()
            ], 500);
        }
    }

    public function getEmployeeFeedbackPerformance(Request $request)
    {
        try {
            $employeeId = $request->input('uid');
            $month = $request->input('month', Carbon::now()->month);
            $year = $request->input('year', Carbon::now()->year);

            // Get employee with department
            $employee = Employee::with('department')->where('code', $employeeId)->firstOrFail();

            $employeePerformance = $this->performanceMetric->employeeFeedbackPerformance ($employee, $year, $month);

            return response()->json([
                'status' => true,
                'data' => $employeePerformance
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
    public function getDepartmentSkillAnalysis(Request $request)
    {
        try {
            $departmentId = $request->input('uid');
            $month = $request->input('month', Carbon::now()->month);
            $year = $request->input('year', Carbon::now()->year);

            $department = EmployerJobcode::with(['technical_skill','soft_skill'])
                                ->findOrFail($departmentId);

            // Get all employees in department
            // $employees = Employee::where('job_code_id', $departmentId)->get();

            $overview = $this->performanceMetric->calculateDepartmentAnalysis($department, $year, $month);

            return response()->json([
                'status' => true,
                'data' => $overview
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Error calculating department performance: " . $e->getMessage()
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

            $department = EmployerJobcode::with(['technical_skill','soft_skill'])
                                ->findOrFail($departmentId);

            // Get all employees in department
            // $employees = Employee::where('job_code_id', $departmentId)->get();

            $performance = $this->performanceMetric->calculateDepartmentPerformance($department, $year, $month);

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


}
