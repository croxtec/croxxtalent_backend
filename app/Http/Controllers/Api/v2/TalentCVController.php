<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;
use App\Models\Cv;

class TalentCVController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = $request->user();
        // Retrieve the validated input data...
        $validatedData = $request->validated();
        
        // $user = User::findOrFail($validatedData['user_id']);
        if ($user->type != 'talent') {
            return response()->json([
                'status' => false,
                'message' => "CV Builder can only be used by a talent.",
            ], 400);
        }
        $aff_eligibility = false;
        $_cv = Cv::where('user_id', $user->id)->first();
        if (!$_cv) {
            $aff_eligibility = true;
        }

        $cv = Cv::firstOrCreate(
            ['user_id' => $user->id],
            [
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
            ]
        );
        if ($cv) {
            // give reward to affiliate referral
            if ($aff_eligibility && $user->referral_user_id) {
                $referralUser = User::find($user->referral_user_id);
                if ($referralUser) {
                    $referralUser->affiliate_reward_points = (int) $referralUser->affiliate_reward_points + 10;
                    $referralUser->save();
                }
            }

            return response()->json([
                'status' => true,
                'message' => "CV profile loaded successfully.",
                'data' => $cv
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
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
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
