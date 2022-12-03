<?php

namespace App\Http\Controllers\Api\v1\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\PermissionRequest;
use App\Models\Permission;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->authorize('view-any', Permission::class);

        $per_page = $request->input('per_page', 100);
        $sort_by = $request->input('sort_by', 'sort_order');
        $sort_dir = $request->input('sort_dir', 'asc');
        $search = $request->input('search');
        $archived = $request->input('archived');
        $is_visible_to = $request->input('is_visible_to');
        $group_list = $request->input('group_list');
        $datatable_draw = $request->input('draw'); // if any
        
        $filter['archived'] = $archived == 'yes' ? true : ($archived == 'no' ? false : null);
        $filter['group_list'] = $group_list == 'yes' ? true : ($group_list == 'no' ? false : null);
        $filter['is_visible_to_sysadmin'] = null;
        $filter['is_visible_to_reseller'] = null;
        $filter['is_visible_to_staff'] = null;
        if ($is_visible_to) {
            if ($is_visible_to == 'sysadmin' || base64_decode($is_visible_to) == 'sysadmin') {
                $filter['is_visible_to_sysadmin'] =  true;
            }
            if ($is_visible_to == 'reseller' || base64_decode($is_visible_to) == 'reseller') {
                $filter['is_visible_to_reseller'] =  true;
            }
            if ($is_visible_to == 'staff' || base64_decode($is_visible_to) == 'staff') {
                $filter['is_visible_to_staff'] =  true;
            }
        }

        if ($filter['group_list'] === true) {
            // list by module group with no pagination
            $permissions['data'] = Permission::where( function ($query) use ($filter) {
                if ($filter['archived'] !== null ) {
                    if ($filter['archived'] === true ) {
                        $query->whereNotNull('archived_at');
                    } else {
                        $query->whereNull('archived_at');
                    }                 
                }
                if ($filter['is_visible_to_sysadmin'] !== null ) {
                    $query->where('is_visible_to_sysadmin', (bool) $filter['is_visible_to_sysadmin']);
                }
                if ($filter['is_visible_to_reseller'] !== null ) {
                    $query->where('is_visible_to_reseller', (bool) $filter['is_visible_to_reseller']);
                }
                if ($filter['is_visible_to_staff'] !== null ) {
                    $query->where('is_visible_to_staff', (bool) $filter['is_visible_to_staff']);
                }
            })->where( function($query) use ($search) {
                $query->where('name', 'ILIKE', "%{$search }%");
            })->orderBy('module', 'asc')
                ->orderBy($sort_by, $sort_dir)
                ->get()
                ->groupBy('module');
        } else {
            // default listing with pagination
            $permissions = Permission::where( function ($query) use ($filter) {
                if ($filter['archived'] !== null ) {
                    if ($filter['archived'] === true ) {
                        $query->whereNotNull('archived_at');
                    } else {
                        $query->whereNull('archived_at');
                    }                 
                }
                if ($filter['is_visible_to_sysadmin'] !== null ) {
                    $query->where('is_visible_to_sysadmin', (bool) $filter['is_visible_to_sysadmin']);
                }
                if ($filter['is_visible_to_reseller'] !== null ) {
                    $query->where('is_visible_to_reseller', (bool) $filter['is_visible_to_reseller']);
                }
                if ($filter['is_visible_to_staff'] !== null ) {
                    $query->where('is_visible_to_staff', (bool) $filter['is_visible_to_staff']);
                }
            })->where( function($query) use ($search) {
                $query->where('name', 'ILIKE', "%{$search }%");
            })->orderBy('module', 'asc')
                ->orderBy($sort_by, $sort_dir)
                ->paginate($per_page);
        }

        $response = collect([
            'status' => true, 
            'message' => "Successful."
        ])->merge($permissions)->merge(['draw' => $datatable_draw]);
        return response()->json($response, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Models\Http\Requests\PermissionRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PermissionRequest $request)
    {
        // Authorization is declared in the Form Request

        // Retrieve the validated input data...
        $validatedData = $request->validated();
        $permission = Permission::create($validatedData);
        if ($permission) {
            return response()->json([
                'status' => true, 
                'message' => "Permission \"{$permission->name}\" created successfully.",
                'data' => Permission::find($permission->id)
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
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $permission = Permission::findOrFail($id);

        $this->authorize('view', [Permission::class, $permission]);
        
        return response()->json([
            'status' => true, 
            'message' => "Successful.",
            'data' => $permission
        ], 200);        
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Models\Http\Requests\PermissionRequest  $request
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function update(PermissionRequest $request, $id)
    {
        // Authorization is declared in the PermissionRequest

        // Retrieve the validated input data....
        $validatedData = $request->validated();
        $permission = Permission::findOrFail($id);
        $permission->update($validatedData);
        return response()->json([
            'status' => true, 
            'message' => "Permission \"{$permission->name}\" updated successfully.",
            'data' => Permission::find($permission->id)
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
        $permission = Permission::findOrFail($id);

        $this->authorize('delete', [Permission::class, $permission]);

        $permission->archived_at = now();
        $permission->save();

        return response()->json([
            'status' => true, 
            'message' => "Permission \"{$permission->name}\" archived successfully.",
            'data' => Permission::find($permission->id)
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
        $permission = Permission::findOrFail($id);

        $this->authorize('delete', [Permission::class, $permission]);

        $permission->archived_at = null;
        $permission->save();

        return response()->json([
            'status' => true, 
            'message' => "Permission \"{$permission->name}\" unarchived successfully.",
            'data' => Permission::find($permission->id)
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
        $permission = Permission::findOrFail($id);

        $this->authorize('delete', [Permission::class, $permission]);

        $name = $permission->name;
        // check if the record is linked to other records
        $relatedRecordsCount = related_records_count(Permission::class, $permission);

        if ($relatedRecordsCount <= 0) {
            $permission->delete();
            return response()->json([
                'status' => true, 
                'message' => "Permission \"{$name}\" deleted successfully.",
            ], 200);
        } else {
            return response()->json([
                'status' => false, 
                'message' => "The \"{$name}\" record cannot be deleted because it is associated with {$relatedRecordsCount} other record(s). You can archive it instead.",
            ], 400);
        }              
    }
}
