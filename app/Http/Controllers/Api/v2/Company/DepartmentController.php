<?php

namespace App\Http\Controllers\Api\v2\Company;

use App\Http\Controllers\Controller;
use App\Http\Requests\DepartmentRequest;
use Illuminate\Http\Request;
use App\Models\EmployerJobcode as Department;
use App\Models\DepartmentRole;
use App\Models\Employee;
use App\Models\Supervisor;
use Illuminate\Support\Facades\DB;
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
        $datatable_draw = $request->input('draw');

        $departments = Department::where('employer_id', $company->id)
        ->when($search, function($query) use ($search) {
            $query->where('job_code', 'LIKE', "%{$search}%")
                ->orWhere('job_code', 'LIKE', "%{$search}%");
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
            $department->technical_skill;
            $department->soft_skill;
            foreach($department->roles as $role){
                $employees = Employee::where('department_role_id', $role->id)
                                ->select(['id', 'name', 'photo_url', 'code', 'performance'])->get();
                $role->total_employees = $employees->count();
                $totalPerformance = $employees->sum('performance');
                $averagePerformance = $role->total_employees > 0 ? $totalPerformance / $role->total_employees : 0;
                $role->performance  = round($averagePerformance,2);
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
    public function store(DepartmentRequest $request)
    {
        $company = $request->user();

        $validatedData = $request->validated();
        $validatedData['employer_id'] = $company->id;

        $departmentPrefix = substr($validatedData['job_code'], 0, 3);
        $uniqueCode = Str::random(12);
        $validatedData['job_title'] = strtolower($departmentPrefix . $uniqueCode);

        DB::beginTransaction();
        try {
            $department = Department::create($validatedData);

            // Create roles for this department
            foreach($validatedData['roles'] as $role){
                DepartmentRole::firstOrCreate([
                    'employer_id' =>  $company->id,
                    'department_id' => $department->id,
                    'name' => $role['name']
                ],[
                    'description' => $role['description'] ?? null,
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => "Department \"{$department->job_code}\" created successfully.",
                'data' => Department::with('roles')->find($department->id)
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => "Could not complete request.",
                'error' => $e->getMessage()
            ], 500);
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

        if (empty($id) || $id === 'undefined') {
            $department = Department::where('employer_id', $employer->id)
                ->firstOrFail();
        } elseif (is_numeric($id)) {
            $department = Department::where('id', $id)->where('employer_id', $employer->id)
                ->firstOrFail();
        } else {
            $department = Department::where('job_title', $id)->where('employer_id', $employer->id)
                ->firstOrFail();
        }

        $department->load(['roles', 'technical_skill', 'soft_skill']);
        $department->head_count = Employee::where('job_code_id', $department->id)->count();
        $department->roles_count = $department->roles ? $department->roles->count() : 0;
        $department->supervisor_count = Supervisor::where('department_id', $department->id)->count();

        $rolesPerformance =[];
        $departmentData = [
            'id' => $department->id,
            'label' => $department->job_code,
            'children' => []
        ];

        $departmentData['team_supervisor'] = Supervisor::where('department_id', $department->id)->get()->map(function($supervisor) {
            $employee = $supervisor->employee;
            return [
                'id' => $supervisor->id,
                'label' => $employee->name,
                'photo' => $employee->photo_url,
                'children' => []
            ];
        })->toArray();


        foreach ($department->roles as $role) {
            $roleData = [
                'id' => $role->id,
                'pid' => $department->id,
                'label' => $role->name,
                'children' => []
            ];

            $roleData['total_employees'] = Employee::where('department_role_id', $role->id)->count();
            $roleData['role_supervisor'] = Supervisor::where('department_role_id', $role->id)->get()->map(function($supervisor) use ($role) {
                $employee = $supervisor->employee;
                return [
                    'id' => $supervisor->id,
                    'pid' => $role->id,
                    'label' => $employee->name,
                    'photo' => $employee->photo_url,
                ];
            })->toArray();

            $roleData['children'] = $roleData['role_supervisor'];

            $departmentData['children'][] = $roleData;
        }

        foreach ($department->roles as $role) {
            // Retrieve employees for this role
            $employees = Employee::where('department_role_id', $role->id)->select('performance')->get();

            // Calculate total employees and average performance
            $totalEmployees = $employees->count();
            $totalPerformance = $employees->sum('performance');
            $averagePerformance = $totalEmployees > 0 ? $totalPerformance / $totalEmployees : 0;

            $rolesPerformance[] = [
                'role_name' => $role->name,
                'average_performance' => round($averagePerformance, 2)
            ];
        }

        array_walk($departmentData['children'], function(&$roleData) {
            unset($roleData['role_supervisor']);
        });

        $department->chart = $departmentData;

        $department->teamGap = $rolesPerformance;

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
        try {
            $employer = $request->user();

            $rules = [
                'department_name' => 'required|max:30',
                'description' => 'nullable|max:130'
            ];

            $validatedData = $request->validate($rules);

            if (is_numeric($id)) {
                $department = Department::where('id', $id)
                    ->where('employer_id', $employer->id)
                    ->firstOrFail();
            } else {
                $department = Department::where('department_slug', $id)
                    ->where('employer_id', $employer->id)
                    ->firstOrFail();
            }

            $department->update($validatedData);

            return response()->json([
                'status' => true,
                'message' => "Department updated successfully.",
                'data' => Department::with('roles')->findOrFail($department->id)
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Department not found.'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An unexpected error occurred.',
                'error' => $e->getMessage()
            ], 500);
        }
    }



       /**
     * Archive the specified resource from active list.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function archive($id)
    {
        $departmet = Department::findOrFail($id);

        // $this->authorize('delete', [Department::class, $departmet]);

        $departmet->archived_at = now();
        $departmet->save();

        return response()->json([
            'status' => true,
            'message' => "Jobcode \"{$departmet->job_code}\" archived successfully.",
            'data' => Department::find($departmet->id)
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
        $department = Department::findOrFail($id);

        // $this->authorize('delete', [Department::class, $department]);

        $department->archived_at = null;
        $department->save();

        return response()->json([
            'status' => true,
            'message' => "Jobcode \"{$department->job_code}\" unarchived successfully.",
            'data' => Department::find($department->id)
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
