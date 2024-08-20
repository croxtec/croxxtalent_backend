<?php

namespace App\Http\Controllers\Api\v2\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Mail\WelcomeEmployee;

use App\Models\User;
use App\Models\Employee;
use App\Models\Verification;
use Illuminate\Support\Facades\Mail;

class ManageEmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function resendInvitation(Request $request, $id)
    {
        $employer = $request->user();

        if (is_numeric($id)) {
            $employee = Employee::where('id', $id)->where('employer_id', $employer->id)->firstOrFail();
        } else {
            $employee = Employee::where('code', $id)->where('employer_id', $employer->id)->firstOrFail();
        }

        if($employee->user_id){
            return response()->json([
                'status' => false,
                'message' => "Employee already accepted invitation.",
            ], 400);
        }

        $verification = new Verification();
        $verification->action = "employee";
        $verification->sent_to = $employee->email;
        $verification->is_otp = false;
        $verification = $employee->verifications()->save($verification);

        if ($verification) {
            Mail::to($employee->email)->send(new WelcomeEmployee($employee, $employer, $verification));
        }

        return response()->json([
            'status' => true,
            'message' => "Employee invitation has been resent successfully.",
            'data' => $verification,
        ], 201);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
