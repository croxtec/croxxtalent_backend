<?php

namespace App\Http\Controllers\Api\v2\Learning;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Training\CroxxTraining;
use App\Models\Employee;


class TrainingHubController extends Controller
{

      /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function employee(Request $request, $code)
    {
        $user = $request->user();
        $show = $request->input('show', "personal");
        $per_page = $request->input('per_page', 12);

        $employee = Employee::where('code', $code)->firstOrFail();

        if($user->type == 'talent'){
           if(!$this->validateEmployee($user,$employee)){
                return response()->json([
                    'status' => false,
                    'message' => 'Unautourized Access'
                ], 401);
           }
        }

        $trainings = CroxxTraining::
                        join('employee_learning_paths', 'croxx_trainings.id', '=', 'employee_learning_paths.training_id')
                        ->where('croxx_trainings.employer_id', $employee->employer_id)
                        ->where('employee_learning_paths.employee_id', $employee->id)
                        ->latest()
                        ->select('croxx_trainings.*')
                        ->paginate($per_page);


        return response()->json([
            'status' => true,
            'message' => "",
            'data' => $trainings
        ], 200);
    }

    private function validateEmployee($user, $employee){
        // Get The current employee information
        $current_company = Employee::where('id', $user->default_company_id)
                    ->where('user_id', $user->id)->with('supervisor')->first();

        if($current_company->id === $employee->id){
            return true;
        }
        if($current_company->supervisor) {
            $supervisor =  $current_company->supervisor;
            // info([$supervisor, $employee]);
            return true;
            if($supervisor->type == 'role' && $employee->department_role_id === $supervisor->department_role_id){
                return true;
            }
            if($supervisor->type == 'department' && $employee->job_code_id === $supervisor->department_id){
                return true;
            }
            return false;
        }
        return false;
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $per_page = $request->input('per_page', 12);
        $employeeIds = Employee::where('user_id', $user->id)->pluck('id')->toArray();

        $trainings = CroxxTraining::all();



        return response()->json([
            'status' => true,
            'message' => "",
            'data' => $trainings
        ], 200);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function paths(Request $request)
    {
        $user = $request->user();
        $per_page = $request->input('per_page', 12);
        $employeeIds = Employee::where('user_id', $user->id)->pluck('employer_id')->toArray();

        $trainings = CroxxTraining::
                        join('employee_learning_paths', 'croxx_trainings.id', '=', 'employee_learning_paths.training_id')
                        ->whereIn('employee_learning_paths.employer_user_id', $employeeIds)
                        ->latest()
                        ->select('croxx_trainings.*')
                        ->paginate($per_page);


        return response()->json([
            'status' => true,
            'message' => "",
            'data' => $trainings
        ], 200);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
