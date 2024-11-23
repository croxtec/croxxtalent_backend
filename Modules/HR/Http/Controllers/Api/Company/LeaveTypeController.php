<?php

namespace Modules\HR\Http\Controllers\Api\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\HR\Entities\LeaveType;

class LeaveTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('hr::index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('hr::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $leaveType = new LeaveType();
        $leaveType->type_name = $request->type_name;
        $leaveType->leavetype = $request->leavetype;
        $leaveType->color = $request->color;
        $leaveType->paid = $request->paid;
        if($request->leavetype == 'monthly'){
            $leaveType->no_of_leaves = $request->monthly_leave_number;
            $leaveType->monthly_limit = 0;
        }else{
            $leaveType->no_of_leaves = $request->yearly_leave_number;
            $leaveType->monthly_limit = $request->monthly_limit;
        }
        $leaveType->effective_after = $request->effective_after;
        $leaveType->effective_type = $request->effective_type;
        $leaveType->unused_leave = $request->unused_leave;
        $leaveType->encashed = $request->has('encashed') ? 1 : 0;
        $leaveType->allowed_probation = $request->has('allowed_probation') ? 1 : 0;
        $leaveType->allowed_notice = $request->has('allowed_notice') ? 1 : 0;
        $leaveType->gender = $request->gender ? json_encode($request->gender) : null;
        $leaveType->marital_status = $request->marital_status ? json_encode($request->marital_status) : null;
        $leaveType->department = $request->department ? json_encode($request->department) : null;
        $leaveType->designation = $request->designation ? json_encode($request->designation) : null;
        $leaveType->role = $request->role ? json_encode($request->role) : null;
        $leaveType->save();

        $leaveTypes = LeaveType::get();

        return response()->json([
            'status' => true,
            'data' => $leaveTypes,
            'message' => "New Leave Type created successfully.",
        ], 201);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        if ($request->leaves < 0) {
            return Reply::error('messages.leaveTypeValueError');
        }

        $leaveType = LeaveType::findOrFail($id);
        $leaveType->type_name = $request->type_name;
        $leaveType->color = $request->color;
        $leaveType->paid = $request->paid;

        // need values later no of leaves early one
        session([
                'old_leaves' => $leaveType->no_of_leaves,
                'old_leavetype' => $leaveType->leavetype
            ]);

        if($request->leavetype == 'monthly'){
            $leaveType->no_of_leaves = $request->monthly_leave_number;
            $leaveType->monthly_limit = 0;
        }else{
            $leaveType->no_of_leaves = $request->yearly_leave_number;
            $leaveType->monthly_limit = $request->monthly_limit;
        }

        $leaveType->leavetype = $request->leavetype;
        $leaveType->monthly_limit = $request->monthly_limit;
        $leaveType->effective_after = $request->effective_after;
        $leaveType->effective_type = $request->effective_type;
        $leaveType->encashed = $request->encashed;
        $leaveType->allowed_probation = $request->allowed_probation;
        $leaveType->allowed_notice = $request->allowed_notice;
        $leaveType->gender = $request->gender ? json_encode($request->gender) : null;
        $leaveType->marital_status = $request->marital_status ? json_encode($request->marital_status) : null;
        $leaveType->department = $request->department ? json_encode($request->department) : null;
        $leaveType->designation = $request->designation ? json_encode($request->designation) : null;
        $leaveType->role = $request->role ? json_encode($request->role) : null;
        $leaveType->save();

        return response()->json([
            'status' => true,
            'data' => $leaveType,
            'message' => "",
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $leaveType = LeaveType::withTrashed()->find($id);

        if (request()->has('restore') && request()->restore == 'restore') {
            if ($leaveType && $leaveType->trashed()) {
                $leaveType->restore();
                // return Reply::success(__('messages.restoreSuccess'));
            }
        }

        if (request()->has('archive') && request()->archive == 'archive') {
            if ($leaveType) {
                $leaveType->delete();
                // return Reply::success(__('messages.archiveSuccess'));
            }
        }

        if (request()->has('force_delete') && request()->force_delete == 'force_delete') {
            if ($leaveType) {
                $leaveType->forceDelete();
                // return Reply::success(__('messages.deleteSuccess'));
            }
        }

        LeaveType::destroy($id);

        return response()->json([
            'status' => true,
            'data' => $leaveType,
            'message' => "",
        ], 200);
    }
}
