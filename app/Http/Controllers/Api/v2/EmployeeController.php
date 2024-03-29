<?php

namespace App\Http\Controllers\Api\v2;

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

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $this->authorize('view-any', Campaign::class);

        $per_page = $request->input('per_page', 100);
        $sort_by = $request->input('sort_by', 'created_at');
        $sort_dir = $request->input('sort_dir', 'desc');
        $search = $request->input('search');
        $archived = $request->input('archived');
        $datatable_draw = $request->input('draw'); // if any

        $archived = $archived == 'yes' ? true : ($archived == 'no' ? false : null);

        $employees = Employee::where('employer_id', $user->id)
        ->when( $archived ,function ($query) use ($archived) {
            if ($archived !== null ) {
                if ($archived === true ) {
                    $query->whereNotNull('archived_at');
                } else {
                    $query->whereNull('archived_at');
                }
            }
        })->where( function($query) use ($search) {
            $query->where('name', 'LIKE', "%{$search}%");
        })->with('job_code', 'employer', 'talent')
        ->orderBy($sort_by, $sort_dir);

        if ($per_page === 'all' || $per_page <= 0 ) {
            $results = $employees->get();
            $employees = new \Illuminate\Pagination\LengthAwarePaginator($results, $results->count(), -1);
        } else {
            $employees = $employees->paginate($per_page);
        }

        $response = collect([
            'status' => true,
            'message' => "Successful."
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
        $user = $request->user();
        $validatedData = $request->validated();
        $validatedData['employer_id'] = $user->id;
        $isEmployer = Employee::where('email', $validatedData['email'])->first();
        // $ $isEmployer = User::where('email', $validatedData['email'])->first(); = User::where('email', $validatedData['email'])->first();
        // $validatedData['user_id'] = $talent->id;

        $employee = Employee::create($validatedData);

        if($employee && !$isEmployer){
            $verification = new Verification();
            $verification->action = "employee";
            $verification->sent_to = $employee->email;
            $verification->metadata = null;
            $verification->is_otp = false;
            $verification = $employee->verifications()->save($verification);
            if ($verification) {
                Mail::to($validatedData['email'])->send(new WelcomeEmployee($employee, $user, $verification));
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
    }


    public function importEmployee(Request $request)
    {
        $user = $request->user();
        $this->validate($request, [
            'file' => 'required|file|mimes:xlsx,xls'
        ]);

        if ($request->hasFile('file')){
            $path = $request->file('file');
            $data = Excel::import(new EmployeeImport($user), $request->file);

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

        // file_get_contents();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        $employee = Employee::with('job_code', 'employer', 'talent')->findOrFail($id);

        // $this->authorize('view', [Employee::class, $employee]);

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
        $user = $request->user();
        $validatedData = $request->validated();

        $employee = Employee::findOrFail($id);
        $employee->update($validatedData);

        return response()->json([
            'status' => true,
            'message' => "Employee \"{$employee->name}\" updated successfully.",
            'data' => Employee::find($employee->id)
        ], 200);
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
