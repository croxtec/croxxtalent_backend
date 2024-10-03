<?php

namespace App\Http\Controllers\Api\v2\Auth;

use App\Http\Controllers\Controller;
use App\Models\Audit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{

    public function redirect(Request $request){
        return Socialite::driver('google')->stateless()->redirect();
        // $googleUser = Socialite::driver('google')->stateless()->user();
    }

    public function handleGoogleCallback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
            // Check if a user exists with the given email
            $user = User::where('email', $googleUser->email)->first();

            if ($user) {
                if (!$user->google_id) {
                    $user->google_id = $googleUser->id;
                    $user->save();
                }
            } else {
                $user = User::create([
                    'google_id' => $googleUser->id,
                    'first_name' => $googleUser->user['given_name'] ?? '',
                    'last_name' => $googleUser->user['family_name'] ?? '',
                    'email' => $googleUser->email,
                    'password' => Str::random(12), // Random password for new users
                    'email_verified_at' => now(),
                ]);
            }

            // Create access token with abilities
            $abilities = ["access:{$user->type}"];
            $token = $user->createToken('access-token', $abilities)->plainTextToken;

            // Optionally, log the action in your audit trail
            $old_values = [];
            $new_values = [];
            Audit::log($user->id, 'login', $old_values, $new_values, User::class, $user->id);

            // Prepare response data
            // $responseData = $this->tokenData($token);

            $frontendUrl = env('FRONTEND_URL', 'https://localhost:5173');
            return redirect()->to($frontendUrl . '/auth/callback?token=' . $token);

            // Return success response
            // return response()->json([
            //     'status' => true,
            //     'message' => '',
            //     'data' => $responseData
            // ], 200);

        } catch (\Exception $e) {
            // Log exception details
            info('Authentication error:', ['exception' => $e->getMessage()]);

            // Return error response
            return response()->json([
                'status' => 'False',
                'message' => 'Failed to authenticate.',
                'error' => $e->getMessage(), // Display the error message
            ], 500);
        }

    }

    protected function tokenData($token)
    {
        $token_expiry = (60 * (int) config('sanctum.expiration'));
        return [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => $token_expiry,
            'expires_at' => Carbon::now()->addSeconds($token_expiry)->toJSON()
        ];
    }

}
