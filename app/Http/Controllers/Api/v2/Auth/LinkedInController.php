<?php

namespace App\Http\Controllers\Api\v2\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Facades\Socialite;

class LinkedInController extends Controller
{
   // Redirect user to LinkedIn for authentication
   public function redirectToLinkedIn()
   {
       return Socialite::driver('linkedin')->redirect();
   }

   // Handle LinkedIn callback
   public function handleLinkedInCallback(Request $request)
   {
       $linkedinUser = Socialite::driver('linkedin')->user();

       // Check if user already exists
       $user = User::where('linkedin_id', $linkedinUser->id)->first();

       if (!$user) {
           // Create new user
           $user = User::create([
               'name' => $linkedinUser->name,
               'email' => $linkedinUser->email,
               'linkedin_id' => $linkedinUser->id,
               'password' => bcrypt(str_random(16)), // Random password
           ]);
       }

       // Login the user
       Auth::login($user);

       return redirect('/home');
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
