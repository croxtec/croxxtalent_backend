<?php

namespace App\Http\Controllers\Api\v2\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EmployerJobcode as Department;
use App\Models\DepartmentRole;
use App\Models\Employee;
use App\Models\Supervisor;
use Illuminate\Support\Str;

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

        $departments = Department::where('employer_id', $company->id)
        ->when($search, function($query) use ($search) {
            $query->where('id', 'LIKE', "%{$search}%");
        })
        ->orderBy($sort_by, $sort_dir);

        if ($per_page === 'all' || $per_page <= 0 ) {
            $results = $departments->get();
            $departments = new \Illuminate\Pagination\LengthAwarePaginator($results, $results->count(), -1);
        } else {
            $departments = $departments->paginate($per_page);
        }

        foreach($departments as $department){
            if(!$department->job_title){
                $title =  $department->id . Str::random(12);
                $department->job_title = strtolower($title);
                $department->save();
            }
            $department->roles;
            foreach($department->roles as $role){
                $role->total_employees = Employee::where('department_role_id', $role->id)->count();
            }
        }


        $response = collect([
            'status' => true,
            'message' => ""
        ])->merge($departments)->merge(['draw' => $datatable_draw]);
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

        if (is_numeric($id)) {
            $department = Department::where('id', $id)->where('employer_id', $employer->id)
                ->select(['id','job_code', 'job_title', 'description'])->firstOrFail();
        } else {
            $department = Department::where('job_title', $id)->where('employer_id', $employer->id)
                ->select(['id','job_code', 'job_title', 'description'])->firstOrFail();
        }

        $department->roles;
        $department->head_count = Employee::where('job_code_id', $department->id)->count();
        $department->team_supervisor = Supervisor::where('department_id', $department->id)->get();
        foreach($department->roles as $role){
            $role->total_employees = Employee::where('department_role_id', $role->id)->count();
            $role->role_supervisor = Supervisor::where('department_role_id', $role->id)->get();
        }


        return response()->json([
            'status' => true,
            'message' => "Successful.",
            'data' => $department
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
        $employer  = $request->user();

        $rules = [
            'job_code' => 'required',
            'description' => 'nullable'
        ];

        $validatedData = $request->validate($rules);

        $job_code = Department::where('id',$id)->where('employer_id', $employer->id)->first();
        $job_code->update($request->all());

        return response()->json([
            'status' => true,
            'message' => "Department updated successfully.",
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
