<?php

namespace Modules\HR\Http\Controllers\Api\Company;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\HR\Entities\Leave;

class LeaveController extends Controller
{
    public array $data = [];

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $data = Leave::all();

        return response()->json([
            'status' => true,
            'data' => $data,
            'message' => "",
        ], 200);
    }


    // private function checkAttendance(array $dates, $user_id, $leave_half_day = null, $leave_half_day_type = null) {

    //     foreach ($dates as $date) {

    //         $userShift = User::findOrFail($user_id)->shifts()->whereDate('date', $date)->first();

    //         if ($userShift) {
    //             $halfMarkTime = Carbon::createFromFormat('H:i:s', $userShift->shift->halfday_mark_time, $this->company->timezone);
    //         } else {
    //             $attendanceSetting = AttendanceSetting::first();
    //             $defaultShiftId = $attendanceSetting->default_employee_shift;
    //             $defaultShift = EmployeeShift::findOrFail($defaultShiftId);


    //             $halfMarkTime = Carbon::createFromFormat('H:i:s', $defaultShift->halfday_mark_time, $this->company->timezone);
    //         }

    //         $halfMarkDateTime = Carbon::createFromFormat('Y-m-d H:i:s', $date->format('Y-m-d') . ' ' . $halfMarkTime->toTimeString(), $this->company->timezone);

    //         $query = Attendance::whereDate('clock_in_time', $date)
    //                            ->where('user_id', $user_id);


    //         if (!is_null($leave_half_day)) {
    //             $query->where('half_day', $leave_half_day);
    //         }

    //         if (!is_null($leave_half_day_type)) {
    //             $query->where('half_day_type', $leave_half_day_type);
    //         }

    //         $attendance = $query->first();

    //         if ($attendance) {
    //             return true;
    //         }

    //         if (is_null($leave_half_day)) {
    //             $additionalCheck = Attendance::whereDate('clock_in_time', $date)
    //                 ->where('user_id', $user_id)
    //                 ->orderBy('id', 'desc')
    //                 ->first();

    //             if (!$additionalCheck) {
    //                 return false;
    //             }

    //             $clockInTime = Carbon::createFromFormat('Y-m-d H:i:s', $additionalCheck->clock_in_time, 'UTC')
    //             ->setTimezone($this->company->timezone);

    //             if ($leave_half_day_type == 'first_half') {
    //                 if($clockInTime->lessThan($halfMarkDateTime))
    //                 {
    //                     return true;
    //                 }
    //             } else if ($leave_half_day_type == 'second_half') {
    //                 if($clockInTime->greaterThan($halfMarkDateTime))
    //                 {
    //                     return true;
    //                 }
    //             }
    //         }
    //     }

    //     return false;
    // }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        //

         return response()->json([
            'status' => true,
            'data' => [],
            'message' => "",
        ], 200);
    }

    /**
     * Show the specified resource.
     */
    public function show($id): JsonResponse
    {
        //

         return response()->json([
            'status' => true,
            'data' => [],
            'message' => "",
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): JsonResponse
    {
        //

         return response()->json([
            'status' => true,
            'data' => [],
            'message' => "",
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id): JsonResponse
    {
        //

         return response()->json([
            'status' => true,
            'data' => [],
            'message' => "",
        ], 200);
    }
}
