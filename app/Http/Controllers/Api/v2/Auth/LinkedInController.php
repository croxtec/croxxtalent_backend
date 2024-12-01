<?php

namespace App\Http\Controllers\Api\v2\Auth;

use App\Http\Controllers\Controller;
use App\Models\Audit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Facades\Socialite;

class LinkedInController extends Controller
{

    // Redirect user to LinkedIn for authentication
    public function redirect(Request $request)
    {
        info(config('services.linkedin.redirect'));
        // ->scopes(['r_liteprofile', 'r_emailaddress'])  // Request email and profile scopes
        return Socialite::driver('linkedin')
                ->redirectUrl(config('services.linkedin.redirect')) // Specify the redirect URL
                // ->scopes(['openid', 'profile', 'w_member_social', 'email']) // Request email and profile scopes
                ->redirect();
    }

    // Handle LinkedIn callback
    public function handleLinkedInCallback(Request $request)
    {
        // if ($request->has('error')) {
        //     return response()->json([
        //         'status' => 'False',
        //         'message' => 'User declined authorization',
        //         'error' => $request->error,
        //     ], 400);
        // }

        // if (!$request->has('code')) {
        //     return response()->json([
        //         'status' => 'False',
        //         'message' => 'Authorization code not received',
        //     ], 400);
        // }

        try {
            // Retrieve the LinkedIn user with the authorization code
            $linkedinUser = Socialite::driver('linkedin')
                    ->redirectUrl(config('services.linkedin.redirect'))->stateless()->user();

            info($linkedinUser);

            // Check if the user already exists
            $user = User::where('linkedin_id', $linkedinUser->id)
                        ->orWhere('email', $linkedinUser->email)
                        ->first();

            if ($user) {
                // If user exists, update the LinkedIn token
                $user->linkedin_token = $linkedinUser->token; // Save LinkedIn access token
                $user->save();
            } else {
                // Create a new user if not already in the database
                $user = User::create([
                    'linkedin_id' => $linkedinUser->id,
                    'first_name' => $linkedinUser->user['localizedFirstName'] ?? '',
                    'last_name' => $linkedinUser->user['localizedLastName'] ?? '',
                    'email' => $linkedinUser->email,
                    'password' => bcrypt(Str::random(12)),  // Random password
                    'linkedin_token' => $linkedinUser->token, // Store LinkedIn access token
                    'email_verified_at' => now(),
                ]);
            }

          // Log the user in
          Auth::login($user);

          // Create access token with abilities
          $abilities = ["access:{$user->type}"];
          $token = $user->createToken('access-token', $abilities)->plainTextToken;

          // Generate an external token
          $external_token = (string) Str::orderedUuid();
          $user->token = $external_token;
          $user->save();

          // Optionally, log the action in your audit trail
          $old_values = [];
          $new_values = [];
          Audit::log($user->id, 'login', $old_values, $new_values, User::class, $user->id);

          // Prepare response data
          $responseData = $this->tokenData($token);
          $responseData['realtime_token'] = $user->token;
          $responseData['user'] = $user;

          // Return success response
          return response()->json([
              'status' => true,
              'message' => 'You have logged in successfully.',
              'data' => $responseData
          ], 200);

        } catch (\Exception $e) {
            // Log the error and return the failure response
            \Log::error('LinkedIn Authentication Error: ' . $e->getMessage());

            return response()->json([
                'status' => 'False',
                'message' => 'Failed to authenticate.',
                'error' => $e->getMessage(),
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

   // Import LinkedIn profile data
   public function importProfile()
   {
       $user = Auth::user();
       if (!$user->linkedin_id) {
           return response()->json(['error' => 'User not authenticated with LinkedIn'], 401);
       }

       // Make a request to LinkedIn API to get the user's profile
       $linkedinAccessToken = $user->linkedin_token; // Save token when authenticating

       $profileData = Http::withToken($linkedinAccessToken)
           ->get('https://api.linkedin.com/v2/me?projection=(id,firstName,lastName,profilePicture(displayImage~:playableStreams))')
           ->json();

       $emailData = Http::withToken($linkedinAccessToken)
           ->get('https://api.linkedin.com/v2/emailAddress?q=members&projection=(elements*(handle~))')
           ->json();

       // Process profile data and store in your app
       // Example: save profile data to the database
       $user->update([
           'first_name' => $profileData['firstName']['localized']['en_US'],
           'last_name' => $profileData['lastName']['localized']['en_US'],
           'profile_picture' => $profileData['profilePicture']['displayImage~']['elements'][0]['identifiers'][0]['identifier'],
           'email' => $emailData['elements'][0]['handle~']['emailAddress'],
       ]);

       return response()->json(['message' => 'Profile imported successfully', 'profile' => $profileData]);
   }
}
