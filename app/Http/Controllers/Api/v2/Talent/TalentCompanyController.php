<?php

namespace App\Http\Controllers\Api\v2\Talent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Supervisor;
use App\Models\Goal;


class TalentCompanyController extends Controller
{

    public function index(Request $request){

        $user = $request->user();
        $companies = Employee::where('user_id', $user->id)
                        ->with('department', 'department_role', 'employer')->get();
        $default_company =  null;

        if (count($companies)) {
            // Get the first company's employer_id as the default
            $firstCompanyEmployerId = $companies->first()->id;
            // $defaultCompanyId = $request->input('employer', $firstCompanyEmployerId);

            if($request->input('employer')) {
                $user->token = $request->input('employer');
                $user->save();
            }else{
                $user->token = $firstCompanyEmployerId;
                $user->save();
            }

            $default_company = $companies->firstWhere('id', $user->token);
        }

        return response()->json([
            'status' => true,
            'data' => compact('default_company','companies'),
            'message' => ''
        ], 200);
    }

    public function supervisor(Request $request){
        $user = $request->user();
        $companies = Employee::where('user_id', $user->id)->get();

        $myinfo =  null;

        if (count($companies)) {
            // Get the first company's employer_id as the default
            $firstCompanyEmployerId = $companies->first()->id;

            // Retrieve the id from the request or use the first company's id as default
            $defaultCompanyId = $request->input('employer', $firstCompanyEmployerId);
            // Employerr
            $myinfo = $companies->firstWhere('id', $user->token);

            if(isset($myinfo->supervisor_id)){
                // Get Supervisor Info
                $supervisor = Supervisor::where('supervisor_id', $myinfo->id)->first();
                // Supervisor Detail
                $team_structure =  Employee::where('employer_id', $supervisor->employer_id)
                                     ->where('job_code_id',  $supervisor->department_id)
                                     ->whereNull('supervisor_id')->get();


                return response()->json([
                    'status' => true,
                    'data' => compact('supervisor', 'team_structure'),
                    'message' => ''
                ], 200);
            }else{
                return response()->json([
                    'status' => false,
                    'message' => 'Unautourized Access'
                ], 401);
            }
        }

        return response()->json([
            'status' => false,
            'message' => 'Supervisor not found'
        ], 404);
    }

    public function employeeInformation(Request $request, $id){
        $user = $request->user();
        $companies = Employee::where('user_id', $user->id)->get();

        $myinfo =  null;

        if (count($companies)) {
            $firstCompanyEmployerId = $companies->first()->id;
            $defaultCompanyId = $request->input('employer', $firstCompanyEmployerId);
            $myinfo = $companies->firstWhere('id', $user->token);

            if(isset($myinfo->supervisor_id)){

                if (is_numeric($id)) {
                    $employee = Employee::where('id', $id)->where('employer_id', $myinfo->employer_id)->firstOrFail();
                } else {
                    $employee = Employee::where('code', $id)->where('employer_id', $myinfo->employer_id)->firstOrFail();
                }

                $goals = Goal::where('employee_id', $employee->id)
                              ->where('employer_id', $employee->employer_id)
                              ->get();

                $employee->department;
                $employee->department_role;
                $employee->talent;
                $employee->supervisor;
                $employee->goals = $goals;

                return response()->json([
                    'status' => true,
                    'data' => $employee,
                    'message' => ''
                ], 200);
            }else{
                return response()->json([
                    'status' => false,
                    'message' => 'Unautourized Access'
                ], 401);
            }
        }

        return response()->json([
            'status' => false,
            'message' => 'Supervisor not found'
        ], 404);
    }

    public function teamPerformanceProgress(Request $request){
        $user = $request->user();
        $companies = Employee::where('user_id', $user->id)->get();

        $myinfo =  null;

        if (count($companies)) {
            $firstCompanyEmployerId = $companies->first()->id;
            $per_page = $request->input('per_page', 4);
            $defaultCompanyId = $request->input('employer', $firstCompanyEmployerId);

            $myinfo = $companies->firstWhere('id', $user->token);

            if(isset($myinfo->supervisor_id)){
                $supervisor = Supervisor::where('supervisor_id', $myinfo->id)->first();
                // Add Pagination here
                $employees = Employee::where('employer_id', $supervisor->employer_id)
                                    ->where('job_code_id', $supervisor->department_id)
                                    ->whereNull('supervisor_id')
                                    ->paginate($per_page);

                $employeeIds =  $employees->pluck('id');
                // Query the goals with the specified employee IDs
                $goals = Goal::whereIn('employee_id', $employeeIds)->get();

                $groupedGoals = $goals->groupBy('employee_id');

                // $team_goals = $groupedGoals->map(function ($goals, $employeeId) {
                //     return [
                //         'employee' => $employeeId,
                //         'goals' => $goals
                //     ];
                // });

                // Map employee details and goals
                $team_goals = $employees->map(function ($employee) use ($groupedGoals) {
                    return [
                        'employee' => $employee,
                        'goals' => $groupedGoals->get($employee->id, collect()), // Default to empty collection if no goals
                    ];
                });

                return response()->json([
                    'status' => true,
                    'data' => $team_goals,
                    'message' => ''
                ], 200);
            }else{
                return response()->json([
                    'status' => false,
                    'message' => 'Unautourized Access'
                ], 401);
            }
        }

        return response()->json([
            'status' => false,
            'message' => 'Supervisor not found'
        ], 404);
    }

}
