<?php

namespace App\Http\Controllers\Api\v2\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Helpers\EmployeeImport;
use App\Http\Requests\EmployeeRequest;
use App\Mail\WelcomeEmployee;
use App\Models\User;
use App\Models\Verification;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\EmployerJobcode as Department;
use App\Models\DepartmentRole;
use Illuminate\Support\Str;
use App\Models\Goal;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $employer = $request->user();
        // $this->authorize('view-any', Employee::class);
        $per_page = $request->input('per_page', 100);
        $sort_by = $request->input('sort_by', 'created_at');
        $sort_dir = $request->input('sort_dir', 'desc');
        $search = $request->input('search');
        $archived = $request->input('archived');
        $role = $request->input('role');
        $department = $request->input('department');
        $datatable_draw = $request->input('draw'); // if any

        $archived = $archived == 'yes' ? true : ($archived == 'no' ? false : null);

        $employees = Employee::when($employer->type  == 'employer',
            function($query) use($employer){
            return  $query->where('employer_id', $employer->id);
        })->when($department || $role,function ($query) use ($department, $role) {
            if ($department !== null  && is_numeric($department)) {
               $query->where('job_code_id', $department);
               if ($role !== null  && is_numeric($role)) {

               }
            }
        })
        ->when( $archived ,function ($query) use ($archived) {
            if ($archived !== null ) {
                if ($archived === true ) {
                    $query->whereNotNull('archived_at');
                } else {
                    $query->whereNull('archived_at');
                }
            }
        })->where( function($query) use ($search) {
            $query->where('name', 'LIKE', "%{$search}%")
                ->orWhere('email', 'LIKE', "%{$search}%");
        })->with('department','department_role', 'talent')
        ->orderBy($sort_by, $sort_dir);

        if ($per_page === 'all' || $per_page <= 0 ) {
            $results = $employees->get();
            $employees = new \Illuminate\Pagination\LengthAwarePaginator($results, $results->count(), -1);
        } else {
            $employees = $employees->paginate($per_page);
        }

        foreach($employees as $employee){
            if(!$employee->code){
                $code =  $employee->id . Str::random(15);
                $employee->code = strtolower($code);
                $employee->save();
            }
        }

        $response = collect([
            'status' => true,
            'message' => ""
        ])->merge($employees)->merge(['draw' => $datatable_draw]);
        return response()->json($response, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(EmployeeRequest $request)
    {
        $employer = $request->user();
        $validatedData = $request->validated();
        $validatedData['employer_id'] = $employer->id;

        $isEmployer = Employee::where('email', $validatedData['email'])
                            ->where('employer_id', $employer->id)->first();

        if(!$isEmployer){
            if (isset($validatedData['job_code']) && strlen($validatedData['job_code']) > 0) {
                $department = Department::firstOrCreate([
                    'employer_id' => $validatedData['employer_id'],
                    'job_code' => $validatedData['job_code']
                ]);
                $validatedData['job_code_id'] = $department->id;
            }

            if (isset($validatedData['department_role']) &&  strlen($validatedData['department_role']) > 0) {
                $department_role = DepartmentRole::firstOrCreate([
                    'employer_id' => $validatedData['employer_id'],
                    'department_id' => $validatedData['job_code_id'],
                    'name' => $validatedData['department_role']
                ]);
                $validatedData['department_role_id'] = $department_role->id;
            }

            $employee =  Employee::create($validatedData);

            if(isset($employer->onboarding_stage) && $employer->onboarding_stage == 1){
                $employer->onboarding_stage = 2;
                $employer->save();
            }

            if($employee){
                $verification = new Verification();
                $verification->action = "employee";
                $verification->sent_to = $employee->email;
                $verification->metadata = null;
                $verification->is_otp = false;
                $verification = $employee->verifications()->save($verification);

                if ($verification) {
                    Mail::to($validatedData['email'])->send(new WelcomeEmployee($employee, $employer, $verification));
                }

                return response()->json([
                    'status' => true,
                    'message' => "Employee created successfully.",
                    'verification' => $verification,
                    'data' => Employee::find($employee->id)
                ], 201);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => "Could not complete request.",
                ], 400);
            }
        } else{
            return response()->json([
                "status" => false,
                "message" => 'Employee already exist'
            ], 422);
        }
    }


    public function importEmployees(Request $request)
    {
        $employer = $request->user();
        // info('FILE UPLOAD');importEmployee
        $this->validate($request, [
            'file' => 'required|file|mimes:xlsx,xls'
        ]);


        if ($request->hasFile('file')){
            $path = $request->file('file');
            $data = Excel::import(new EmployeeImport($employer), $request->file);

            if(isset($employer->onboarding_stage) && $employer->onboarding_stage == 1){
                $employer->onboarding_stage = 2;
                $employer->save();
            }

            return response()->json([
                'status' => true,
                'message' => 'Data imported successfully.'
            ], 200);
        }else{
            return response()->json([
                'status' => true,
                'message' => "Could not upload file, please try again.",
            ], 400);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        $employer = $request->user();

        if (is_numeric($id)) {
            $employee = Employee::where('id', $id)->where('employer_id', $employer->id)->firstOrFail();
        } else {
            $employee = Employee::where('code', $id)->where('employer_id', $employer->id)->firstOrFail();
        }

        // $this->authorize('view', [Employee::class, $employee]);

        // $goals = Goal::where('employee_id', $employee->id)
        //             ->where('employer_id', $employee->employer_id)
        //             ->get();
        // $employee->goals = $goals;

        $employee->department;
        $employee->department_role;
        $employee->talent;
        $employee->supervisor;

        $technical_skills = array_column($employee->department->technical_skill->toArray(0),'competency');
        $assessment_distribution = [];
        $trainings_distribution = [];

        if(count($technical_skills)){
            foreach($technical_skills as $skill){
                array_push($assessment_distribution, mt_rand(0, 10));
            }
        }

        $employee->technical_distribution = [
            'categories' => $technical_skills,
            'assessment_distribution' =>  $assessment_distribution,
            'trainings_distribution' =>  $assessment_distribution,
        ];


        $goals_taken =  Goal::where('employee_id', $employee->id)
                            ->where('employer_id', $employee->employer_id)->count();

        $employee->proficiency = [
            'total' =>  '0%',
            'assessment' => [
                'taken' => 0,
                'performance' => '0%'
            ],
            'goals' => [
                'taken' => $goals_taken,
                'performance' => '0%'
            ],
            'trainings' => [
                'taken' => 0,
                'performance' => '0%'
            ],
        ];

        return response()->json([
            'status' => true,
            'message' => "Successful.",
            'data' => $employee
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(EmployeeRequest $request, $id)
    {
        $employer = $request->user();
        $validatedData = $request->validated();

        $employee = Employee::where('id',$id)->where('employer_id', $employer->id)->firstOrFail();
        $employee->update($validatedData);

        return response()->json([
            'status' => true,
            'message' => "Employee \"{$employee->name}\" updated successfully.",
            'data' => Employee::find($employee->id)
        ], 201);
    }

    /**
     * Archive the specified resource from active list.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function archive($id)
    {
        $employee = Employee::findOrFail($id);

        // $this->authorize('delete', [Employee::class, $employee]);

        $employee->archived_at = now();
        $employee->save();

        return response()->json([
            'status' => true,
            'message' => "Employee \"{$employee->name}\" archived successfully.",
            'data' => Employee::find($employee->id)
        ], 200);
    }

    /**
     * Unarchive the specified resource from archived storage.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function unarchive($id)
    {
        $employee = Employee::findOrFail($id);

        // $this->authorize('delete', [Employee::class, $employee]);

        $employee->archived_at = null;
        $employee->save();

        return response()->json([
            'status' => true,
            'message' => "Employee \"{$employee->name}\" unarchived successfully.",
            'data' => Employee::find($employee->id)
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $employee = Employee::findOrFail($id);

        // $this->authorize('delete', [Employee::class, $employee]);

        $name = $employee->name;
        // check if the record is linked to other records
        $relatedRecordsCount = related_records_count(Employee::class, $employee);

        if ($relatedRecordsCount <= 0) {
            $employee->delete();
            return response()->json([
                'status' => true,
                'message' => "Employee \"{$name}\" deleted successfully.",
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => "The \"{$name}\" record cannot be deleted because it is associated with {$relatedRecordsCount} other record(s). You can archive it instead.",
            ], 400);
        }
    }
}
