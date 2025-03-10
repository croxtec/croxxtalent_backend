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
            $employer = $request->user();
            $employeeId = $request->input('uid')
                ?: Employee::where('employer_id', $employer->id)->value('id');
            $month = $request->input('month', Carbon::now()->month);
            $year = $request->input('year', Carbon::now()->year);

            // $employeePerformance = $this->performanceMetric->employeeKPIPerformance ($employee, $year, $month);

            // Get employee with department
            $employee = Employee::with('department')->where('code', $employeeId)->firstOrFail();

            $performance = $this->performanceMetric->calculateEmployeeHistoricalPerformance($employee, $year, $month);


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

    public function getEmployeeFeedbackPerformance(Request $request)
    {
        try {
            $employer = $request->user();
            $employeeId = $request->input('uid')
                ?: Employee::where('employer_id', $employer->id)->value('id');
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

    public function getEmployeeHistoricalPerformance(Request $request)
    {
        try {
            $employer = $request->user();
            $employeeId = $request->input('uid')
                ?: Employee::where('employer_id', $employer->id)->value('id');
            $month = $request->input('month', Carbon::now()->month);
            $year = $request->input('year', Carbon::now()->year);


            $employee = Employee::with('department')->where('code', $employeeId)->firstOrFail();

            $performance = $this->performanceMetric->calculateEmployeeHistoricalPerformance($employee, $year, $month);

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


    /**
     * Get department performance breakdown
     */
    public function getDepartmentSkillAnalysis(Request $request)
    {
        try {
            $employer = $request->user();
            $departmentId = $request->input('uid');
            $month = $request->input('month', Carbon::now()->month);
            $year = $request->input('year', Carbon::now()->year);

            $departmentId = $this->getDepartmentId($request, $employer);
            $department = EmployerJobcode::with(['technical_skill','soft_skill'])
                                ->findOrFail($departmentId);

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
            $employer = $request->user();
            $departmentId = $request->input('uid');
            $month = $request->input('month', Carbon::now()->month);
            $year = $request->input('year', Carbon::now()->year);

            $departmentId = $this->getDepartmentId($request, $employer);
            $department = EmployerJobcode::with(['technical_skill','soft_skill'])
                                ->findOrFail($departmentId);

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

    public function getDepartmentHistoricalPerformance(Request $request)
    {
        try {
            $employer = $request->user();
            $departmentId = $request->input('uid');
            $month = $request->input('month', Carbon::now()->month);
            $year = $request->input('year', Carbon::now()->year);

            $departmentId = $this->getDepartmentId($request, $employer);
            $department = EmployerJobcode::with(['technical_skill','soft_skill'])
                                ->findOrFail($departmentId);

            $performance = $this->performanceMetric->calculateDepartmentHistoricalPerformance($department, $year, $month);

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

    private function getDepartmentId($request, $employer)
    {
        $departmentId = $request->input('department') ?? $request->input('uid') ?? $employer->default_company_id;

        if (is_numeric($departmentId)) {
            $department = EmployerJobcode::where('employer_id', $employer->id)
                                    ->where('id', $departmentId)
                                    ->select('id')
                                    ->first();
        } else {
            $department = EmployerJobcode::where('employer_id', $employer->id)
                                    ->where('job_title', $departmentId)
                                    ->select('id')
                                    ->first();
        }

        if ($department && $department->id !== $employer->default_company_id) {
            $employer->default_company_id = $department->id;
            $employer->save();
        }

        return $department ? $department->id : EmployerJobcode::where('employer_id', $employer->id)->value('id');
    }


}
