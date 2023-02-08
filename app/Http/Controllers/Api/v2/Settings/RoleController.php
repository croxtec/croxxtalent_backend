<?php

namespace App\Http\Controllers\Api\v1\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\RoleRequest;
use App\Models\Role;
use App\PermissionRole;
use App\School;
use App\Reseller;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->authorize('view-any', Role::class);

        $per_page = $request->input('per_page', 100);
        $sort_by = $request->input('sort_by', 'sort_order');
        $sort_dir = $request->input('sort_dir', 'asc');
        $search = $request->input('search');
        $type = $request->input('type');
        $archived = $request->input('archived');
        $is_custom = $request->input('is_custom');
        $datatable_draw = $request->input('draw'); // if any
        
        $archived = $archived == 'yes' ? true : ($archived == 'no' ? false : null);
        $is_custom = $is_custom == 'yes' ? true : ($is_custom == 'no' ? false : null);
        
        $roles = Role::where( function ($query) use ($type, $archived, $is_custom) {
            $query->where('type', $type);
            if ($archived !== null ) {
                if ($archived === true ) {
                    $query->whereNotNull('archived_at');
                } else {
                    $query->whereNull('archived_at');
                }                 
            }
            if ($is_custom !== null ) {
                $query->where('is_custom', (bool) $is_custom);
            }
        })->where( function($query) use ($search) {
            $query->where('name', 'ILIKE', "%{$search }%");
        })->orderBy('sort_order', 'asc')
            ->orderBy($sort_by, $sort_dir)
            ->paginate($per_page);

        $response = collect([
            'status' => true, 
            'message' => "Successful."
        ])->merge($roles)->merge(['draw' => $datatable_draw]);
        return response()->json($response, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Models\Http\Requests\RoleRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(RoleRequest $request)
    {
        // Authorization is declared in the Form Request

        // Retrieve the validated input data...
        $validatedData = $request->validated();

        // if owner permissions is selected, turn off admin permission
        if ($validatedData['is_owner'] === true) {
            $validatedData['is_admin'] = false;
        }
        $role = Role::create($validatedData);
        if ($role) {
            // Associate role to school or reseller if custom
            if ($validatedData['is_custom'] === ('reseller' || 'staff') && isset($validatedData['is_custom']) && $validatedData['is_custom'] === true) {
                if (isset($validatedData['school_id']) && $validatedData['school_id']) {
                    $school = School::find($validatedData['school_id']);
                    if ($school) {
                        $role->roleable()->associate($school);
                        $role->save();
                    }
                } elseif (isset($validatedData['reseller_id']) && $validatedData['reseller_id']) {
                    $reseller = Reseller::find($validatedData['reseller_id']);
                    if ($reseller) {
                        $role->roleable()->associate($reseller);
                        $role->save();
                    }
                }
            }
            return response()->json([
                'status' => true, 
                'message' => "Role \"{$role->name}\" created successfully.",
                'data' => Role::find($role->id)
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
        $role = Role::findOrFail($id);

        $this->authorize('view', [Role::class, $role]);
        
        return response()->json([
            'status' => true, 
            'message' => "Successful.",
            'data' => $role
        ], 200);        
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Models\Http\Requests\RoleRequest  $request
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function update(RoleRequest $request, $id)
    {
        // Authorization is declared in the RoleRequest

        // Retrieve the validated input data....
        $validatedData = $request->validated();

        // if owner permissions is selected, turn off admin permission
        if ($validatedData['is_owner'] === true) {
            $validatedData['is_admin'] = false;
        }
        $role = Role::findOrFail($id);
        $role->update($validatedData);
        return response()->json([
            'status' => true, 
            'message' => "Role \"{$role->name}\" updated successfully.",
            'data' => Role::find($role->id)
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
        $role = Role::findOrFail($id);

        $this->authorize('delete', [Role::class, $role]);

        $role->archived_at = now();
        $role->save();

        return response()->json([
            'status' => true, 
            'message' => "Role \"{$role->name}\" archived successfully.",
            'data' => Role::find($role->id)
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
        $role = Role::findOrFail($id);

        $this->authorize('delete', [Role::class, $role]);

        $role->archived_at = null;
        $role->save();

        return response()->json([
            'status' => true, 
            'message' => "Role \"{$role->name}\" unarchived successfully.",
            'data' => Role::find($role->id)
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
        $role = Role::findOrFail($id);

        $this->authorize('delete', [Role::class, $role]);

        $name = $role->name;
        // check if the record is linked to other records
        $relatedRecordsCount = related_records_count(Role::class, $role);

        if ($relatedRecordsCount <= 0) {
            $role->delete();
            return response()->json([
                'status' => true, 
                'message' => "Role \"{$name}\" deleted successfully.",
            ], 200);
        } else {
            return response()->json([
                'status' => false, 
                'message' => "The \"{$name}\" record cannot be deleted because it is associated with {$relatedRecordsCount} other record(s). You can archive it instead.",
            ], 400);
        }              
    }


    /**
     * Display a listing of the role permissions.
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function showPermissions($id)
    {
        $this->authorize('view-any', Role::class);
        
        $permissionRoles = PermissionRole::where('role_id', $id)->get();

        $response = collect([
            'status' => true, 
            'message' => "Successful.",
            'data' => $permissionRoles
        ]);
        return response()->json($response, 200);
    }


    /**
     * Update the the role permissions.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function updatePermissions(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $this->authorize('update', [Role::class, $role]);

        $validatedData = $request->validate([
            'permissions' => 'nullable|array',
        ]);

        // first delete all existing permissions of the selected role
        PermissionRole::where('role_id', $role->id)->delete();

        if ($validatedData['permissions']) {
            foreach($validatedData['permissions'] as $permission_id) 
            {
                PermissionRole::create([
                    'role_id' => $role->id,
                    'permission_id' => $permission_id
                ]);
            }
        }        
        return response()->json([
            'status' => true, 
            'message' => "{$role->name} permissions updated successfully.",
            'data' => null
        ], 200);
    }
}
