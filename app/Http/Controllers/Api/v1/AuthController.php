<?php

namespace App\Http\Controllers\Api\v1;

use App\Events\NewNotification;
use App\Events\NotificationMessage;
use App\Events\Notifications;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use Illuminate\Support\Facades\Notification;

use GeoIPLocation;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Verification;
use App\Models\Audit;
use App\Mail\WelcomeVerifyEmail;
use App\Notifications\CroxxTalentUsers;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;


class AuthController extends Controller
{

   /**
    * Create a new AuthController instance.
    *
    * @return void
    */
    public function __construct()
    {

    }

    public function index(){
        return 'Done';
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

   /**
    * Get a Access Token via given credentials.
    *
    * @param \App\Http\Requests\LoginRequest
    * @return \Illuminate\Http\JsonResponse
    */
    public function login(LoginRequest $request)
    {
        // Retrieve the validated input data....
        $validatedData = $request->validated();

        $abilities = [];

        // update the sanctum token expiration to the custom highest_expiration if the requested token is for a long-lived token
        $long_lived_access_token = isset($validatedData['long_lived_access_token']) ? true : false;
        if ($long_lived_access_token === true) {
            \Config::set('sanctum.expiration', config('sanctum.highest_expiration') );
            array_push($abilities, 'long_lived_access_token');
        }

        // check if the login user entered email or phone
        if (filter_var($validatedData['login'], FILTER_VALIDATE_EMAIL)) {
            $login_field = 'email';
        } else{
            $login_field = 'email'; // phone
        }

        $user = User::where($login_field, $validatedData['login'])->first();

        if ( !$user || !Hash::check($validatedData['password'], $user->password) ) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid login credentials.'
            ], 401);
        }
        // Checking If A Password Needs To Be Rehashed
        // if the work factor used by the hasher has changed since the password was hashed
        if (Hash::needsRehash($user->password)) {
            $user->password = bcrypt($validatedData['password']);
            $user->saved();
        }

        if ($user->is_active !== true) {
            return response()->json([
                'status' => false,
                'message' => 'Your account is inactive, please contact support admin.'
            ], 401);
        }
        // create token
        array_push($abilities, "access:{$user->type}");
        $token =  $user->createToken('access-token', $abilities)->plainTextToken;

        // save audit trail log
        $old_values = [];
        $new_values = [];
        Audit::log($user->id, 'login', $old_values, $new_values, User::class, $user->id);




        $responseData = $this->tokenData($token);
        $responseData['user'] = $user;

        // send response
        return response()->json([
            'status' => true,
            'message' => 'You have logged in successfully.',
            'data' => $responseData
        ], 200);
    }

    /**
    * Register and Get a Access Token.
    *
    * @param \App\Http\Requests\RegisterRequest
    * @return \Illuminate\Http\JsonResponse
    */
    public function register(RegisterRequest $request)
    {
        // Retrieve the validated input data....
        $validatedData = $request->validated();
        // check if user was referred
        if ($validatedData['type'] == 'talent' && isset($validatedData['referral_code']) && $validatedData['referral_code']) {
            $referralUser = User::where('referral_code', $validatedData['referral_code'])->first();
            if ($referralUser) {
                $validatedData['referral_user_id'] = $referralUser->id;
                unset($validatedData['referral_code']);
            }
        }
        //Confirm user type
        if ($validatedData['type'] == 'employer'){
            $validator = Validator::make($request->all(),[
                'company_name' => 'required',
                'company_size' => 'required',
                'phone' => 'required|max:25',
                'services' => 'required',
            ]);
            if($validator->fails()){
                $status = false;
                $message = $validator->errors()->toJson();
                return response()->json(compact('status', 'message') , 400);
            }
            $validatedData['is_active'] = 0;
            $validatedData['company_name'] = $request->company_name;
            $validatedData['phone'] = $request->phone;
            $validatedData['company_size'] = $request->company_size;
            $validatedData['services'] = $request->services;
        }

        if ($validatedData['type'] == 'affiliate') {
            $validator = Validator::make($request->all(),[
                'company_name' => 'required',
                'company_affiliate' => 'required',
                'phone' => 'required|max:25',
            ]);
            if($validator->fails()){
                $status = false;
                $message = $validator->errors()->toJson();
                return response()->json(compact('status', 'message') , 400);
            }
            $validatedData['referral_code'] = (string) Str::orderedUuid();
            $validatedData['is_active'] = 0;
            $validatedData['company_name'] = $request->company_name;
            $validatedData['company_affiliate'] = $request->company_affiliate;
            $validatedData['phone'] = $request->phone;
        }
        $user = User::create($validatedData);
        // Log::info($user); return;
        if ($user) {

            $abilities = [];
            // update the sanctum token expiration to the custom highest_expiration if the requested token is for a long-lived token
            $long_lived_access_token = isset($validatedData['long_lived_access_token']) ? true : false;
            if ($long_lived_access_token === true) {
                \Config::set('sanctum.expiration', config('sanctum.highest_expiration') );
                array_push($abilities, 'long_lived_access_token');
            }
            // create token
            array_push($abilities, "access:{$user->type}");
            if($validatedData['type'] == 'talent'){
                $token =  $user->createToken('access-token', $abilities)->plainTextToken;
            }else{
                $token = Null;
            }

            // save audit trail log
            $old_values = [];
            $new_values = $validatedData;
            Audit::log($user->id, 'register', $old_values, $new_values, User::class, $user->id);
            // create and send email verification token records
            if($validatedData['type'] == 'talent'){
                $verification = new Verification();
                $verification->action = "register";
                $verification->sent_to = $user->email;
                $verification->metadata = null;
                $verification->is_otp = false;
                $verification = $user->verifications()->save($verification);
                if ($verification && $user->email) {
                    if (config('mail.queue_send')) {
                        Mail::to($user->email)->queue(new WelcomeVerifyEmail($user, $verification));
                    } else {
                        Mail::to($user->email)->send(new WelcomeVerifyEmail($user, $verification));
                    }
                }
            }
            // format token data
            $responseData = $this->tokenData($token);
            $responseData['user'] = $user;

            return response()->json([
                'status' => true,
                'message' => "User \"{$user->name}\" created successfully.",
                'data' => $responseData
            ], 201);
        } else {
            return response()->json([
                'status' => false,
                'message' => "Could not complete request.",
            ], 400);
        }
    }

   /**
    * Get the authenticated User.
    *
    * @return \Illuminate\Http\JsonResponse
    */
    public function user(Request $request)
    {
        $user = $request->user();

        if ($user) {
            return response()->json([
                'status' => true,
                'message' => 'Authenticated user retrieved.',
                'data' => $user
            ], 200);
        }
        return response()->json([
            'status' => false,
            'message' => 'Unauthorized.'
        ], 401);
    }

   /**
    * Log the user out (Invalidate the token).
    *
    * @return \Illuminate\Http\JsonResponse
    */
    public function logout(Request $request)
    {
        $user = $request->user();
        if ($user) {
            // Revoke all tokens...
            $user->tokens()->delete();
        }
        return response()->json([
            'status' => true,
            'message' => 'You have been successfully logged out.'
        ], 200);
    }

   /**
    * Refresh a token.
    *
    * @return \Illuminate\Http\JsonResponse
    */
    public function refresh(Request $request)
    {
        $user = $request->user();
        if ($user) {
            $abilities = [];
            // update the sanctum token expiration to the custom highest_expiration if the requested token is for a long-lived token
            if ($user->tokenCan('long_lived_access_token')) {
                \Config::set('sanctum.expiration', config('sanctum.highest_expiration') );
                array_push($abilities, 'long_lived_access_token');
            }

            // Revoke all tokens...
            $user->tokens()->delete();

            // Create new token
            array_push($abilities, "access:{$user->type}");
            $token =  $user->createToken('access-token', $abilities)->plainTextToken;

            $responseData = $this->tokenData($token);
            $responseData['user'] = $user;

            return response()->json([
                'status' => true,
                'message' => 'Token refreshed successfully.',
                'data' => $responseData
            ], 200);
        }
        return response()->json([
            'status' => false,
            'message' => 'Unauthorized.'
        ], 401);
    }

}
