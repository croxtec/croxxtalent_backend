<?php

namespace App\Http\Controllers\Api\v2\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EmployerJobcode as Department;
use App\Models\DepartmentRole;
use App\Models\Employee;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $company = $request->user();

        // $this->authorize('view-any', Cv::class);

        $per_page = $request->input('per_page', 100);
        $sort_by = $request->input('sort_by', 'created_at');
        $sort_dir = $request->input('sort_dir', 'asc');
        $search = $request->input('search');
        $datatable_draw = $request->input('draw'); // if any

        $department = Department::where('employer_id', $company->id)
        ->when($search, function($query) use ($search) {
            $query->where('id', 'LIKE', "%{$search}%");
        })->with('roles')
        ->orderBy($sort_by, $sort_dir);

        if ($per_page === 'all' || $per_page <= 0 ) {
            $results = $department->get();
            $department = new \Illuminate\Pagination\LengthAwarePaginator($results, $results->count(), -1);
        } else {
            $department = $department->paginate($per_page);
        }

        $response = collect([
            'status' => true,
            'message' => ""
        ])->merge($department)->merge(['draw' => $datatable_draw]);
        return response()->json($response, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $company = $request->user();
        $rules = [
            'job_code' => 'required',
            'job_title' => 'nullable',
            'description' => 'nullable'
        ];

        $validatedData = $request->validate($rules);
        $validatedData['employer_id'] = $company->id;

        $job_code = Department::create($validatedData);

       if ($job_code) {
            return response()->json([
                'status' => true,
                'message' => "Job Code \"{$job_code->job_code}\" created successfully.",
                'data' => Department::find($job_code->id)
            ], 201);
        } else {
            return response()->json([
                'status' => false,
                'message' => "Could not complete request.",
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
        $department = Department::findOrFail($id);


        $employees = Employee::where('employer_id', $employer->id)
                        ->where('job_code_id', $department->id)
                        ->get();


        return response()->json([
            'status' => true,
            'message' => "Successful.",
            'data' => compact('department', 'employees')
        ], 200);

    }

     /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update_managers(Request $request, $id)
    {
        $company = $request->user();
        $job_code = Department::findOrFail($id);
        $rules = [
            'managers' => 'required|array',
            'managers.*' => 'required|integer|exists:employees,id'
        ];

        $validatedData = $request->validate($rules);
        $job_code->managers = $validatedData['managers'];
        $job_code->save();

        return response()->json([
            'status' => true,
            'message' => "Successful.",
            'data' => $job_code
        ], 200);

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
        $company = $request->user();
        $rules = [
            'job_code' => 'required',
            'job_title' => 'nullable',
            'description' => 'nullable'
        ];

        $validatedData = $request->validate($rules);

        $job_code = Department::findOrFail($id);

        $job_code->update($request->all());

        return response()->json([
            'status' => true,
            'message' => "Job Code updated successfully.",
            'data' =>  Department::findOrFail($id)
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
        $job_code = Department::findOrFail($id);

        // $this->authorize('delete', [Department::class, $job_code]);

        $job_code->archived_at = now();
        $job_code->save();

        return response()->json([
            'status' => true,
            'message' => "Jobcode \"{$job_code->name}\" archived successfully.",
            'data' => Department::find($job_code->id)
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
        $job_code = Department::findOrFail($id);

        // $this->authorize('delete', [Department::class, $job_code]);

        $job_code->archived_at = null;
        $job_code->save();

        return response()->json([
            'status' => true,
            'message' => "Jobcode \"{$job_code->name}\" unarchived successfully.",
            'data' => Department::find($job_code->id)
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
        $job_code = Department::findOrFail($id);

        // $this->authorize('delete', [Department::class, $job_code]);

        $name = $job_code->name;
        // check if the record is linked to other records
        $relatedRecordsCount = related_records_count(Department::class, $job_code);

        if ($relatedRecordsCount <= 0) {
            $job_code->delete();
            return response()->json([
                'status' => true,
                'message' => "Jobcode \"{$name}\" deleted successfully.",
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => "The \"{$name}\" record cannot be deleted because it is associated with {$relatedRecordsCount} other record(s). You can archive it instead.",
            ], 400);
        }
    }
}
