<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Cv;
use App\Models\UserSetting;
use App\Models\Competency\TalentCompetency;

class CroxxProfileController extends Controller
{
    /**
     * Display a user Public profile
     *
     * @return \Illuminate\Http\Response
     */
    public function index($username)
    {
        // if($request->user())

        $profile = User::whereIn('type', ['talent'])->where([
                'username' => $username,
                'is_active' => true
        ])->firstOrFail();
        unset($profile->cv);
        unset($profile->password_updated_at);
        // $cv = CV::where('user_id', $profile->id)->first();
        $competencies = TalentCompetency::where('cv_id', $cv->id)->get();

        return response()->json([
            'status' => true,
            'data' => compact('profile',  'competencies'),
            'message' => ''
        ], 200);
    }

    public function settings(Request $request)
    {
        $user = $request->user();

        $settings = UserSetting::where('user_id', $user->id)->pluck('value', 'key');

        return response()->json([
            'status' => true,
            'data' => $settings,
            'message' => ''
        ], 200);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function storeSettings(Request $request)
    {

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
