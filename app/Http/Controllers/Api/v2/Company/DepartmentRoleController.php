<?php

namespace App\Http\Controllers\Api\v2\Company;

use App\Http\Controllers\Controller;
use App\Http\Requests\DepartmentRequest;
use Illuminate\Http\Request;
use App\Models\DepartmentRole;
class DepartmentRoleController extends Controller
{

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(DepartmentRequest $request, $departmentId)
    {
        try {
            $employer = $request->user();

            $rules = [
                'name' => 'required|max:30',
                'description' => 'nullable|max:130'
            ];

            $validatedData = $request->validate($rules);

            $department = DepartmentRole::where('id', $departmentId)
                ->where('employer_id', $employer->id)
                ->firstOrFail();

            $role = DepartmentRole::create([
                'employer_id' => $employer->id,
                'department_id' => $department->id,
                'name' => $validatedData['name'],
                'description' => $validatedData['description'] ?? null
            ]);

            return response()->json([
                'status' => true,
                'message' => "Role \"{$role->name}\" created successfully.",
                'data' => $role
            ], 201);

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
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,  $roleId)
    {
        try {
            $employer = $request->user();

            $rules = [
                'name' => 'required|max:30',
                'description' => 'nullable|max:130'
            ];

            $validatedData = $request->validate($rules);

            $role = DepartmentRole::where('id', $roleId)
                ->where('employer_id', $employer->id)
                ->firstOrFail();

            $role->update($validatedData);

            return response()->json([
                'status' => true,
                'message' => "Role updated successfully.",
                'data' => $role
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
                'message' => 'Role not found.'
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
        $departmet = DepartmentRole::findOrFail($id);

        // $this->authorize('delete', [DepartmentRole::class, $departmet]);

        $departmet->archived_at = now();
        $departmet->save();

        return response()->json([
            'status' => true,
            'message' => "Jobcode \"{$departmet->job_code}\" archived successfully.",
            'data' => DepartmentRole::find($departmet->id)
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
        $department = DepartmentRole::findOrFail($id);

        // $this->authorize('delete', [DepartmentRole::class, $department]);

        $department->archived_at = null;
        $department->save();

        return response()->json([
            'status' => true,
            'message' => "Jobcode \"{$department->job_code}\" unarchived successfully.",
            'data' => DepartmentRole::find($department->id)
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
        $job_code = DepartmentRole::findOrFail($id);

        // $this->authorize('delete', [DepartmentRole::class, $job_code]);

        $name = $job_code->name;
        // check if the record is linked to other records
        $relatedRecordsCount = related_records_count(DepartmentRole::class, $job_code);

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
