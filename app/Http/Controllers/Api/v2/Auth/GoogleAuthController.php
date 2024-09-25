<?php

namespace App\Http\Controllers\Api\v2\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{

    public function redirect(Request $request){
        return Socialite::driver("google")->redirect();
        // $googleUser = Socialite::driver('google')->stateless()->user();
    }

    public function callback(Request $request){
        $googleUser = Socialite::driver("google")->user();
        var_dump(($googleUser));

        // $user = User::updateOrCreate(
        //     [
        //         'google_id' => $googleUser->id
        //     ],
        //     [
        //         'first_name' => $googleUser->name,
        //         'last_name' => $googleUser->name,
        //         'email' => $googleUser->email,
        //         'password' => Str::random(12),
        //         'email_verified_at' => now(),
        //     ]
        // );

    }
}
