<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Requests\UserRequest;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\ResetNewPasswordRequest;
use App\Http\Requests\UserPhotoRequest;
use App\Mail\ActivateUserEmail;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Verification;
use App\Models\Campaign;
use App\Models\Cv;
use App\Models\JobInvitation;
use App\Models\Audit;
use App\Mail\WelcomeVerifyEmail;
use App\Mail\VerifyEditEmail;
use App\Models\Notification;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->authorize('view-any', User::class);

        $per_page = $request->input('per_page', 100);
        $sort_by = $request->input('sort_by', 'created_at');
        $sort_dir = $request->input('sort_dir', 'desc');
        $search = $request->input('search');
        $active = $request->input('active');
        $type = $request->input('type');
        $datatable_draw = $request->input('draw'); // if any

        $active = $active == 'yes' ? true : ($active == 'no' ? false : null);

        $users = User::where( function ($query) use ($type, $active) {
            if ($type) {
                $query->where('type', $type);
            }
            if ($active !== null ) {
                $query->where('is_active', $active);
            }
        })->where( function($query) use ($search) {
            $query->where('first_name', 'LIKE', "%{$search}%")
                ->orWhere('last_name', 'LIKE', "%{$search}%")
                ->orWhere('email', 'LIKE', "%{$search}%");
        })->orderBy($sort_by, $sort_dir);

        if ($per_page === 'all' || $per_page <= 0 ) {
            $results = $users->get();
            $users = new \Illuminate\Pagination\LengthAwarePaginator($results, $results->count(), -1);
        } else {
            $users = $users->paginate($per_page);
        }

        $response = collect([
            'status' => true,
            'message' => "Successful."
        ])->merge($users)->merge(['draw' => $datatable_draw]);

        return response()->json($response, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Models\Http\Requests\UserRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(UserRequest $request)
    {
        // Authorization was declared in the Form Request
        // Retrieve the validated input data...
        $validatedData = $request->validated();
        $validatedData['password'] = $request->password;
        $user = User::create($validatedData);
        if ($user) {
            // save audit trail log
            $old_values = [];
            $new_values = $validatedData;
            Audit::log($user->id, 'register', $old_values, $new_values, User::class, $user->id);

            return response()->json([
                'status' => true,
                'message' => "User \"{$user->first_name}\" created successfully.",
                'data' => User::find($user->id)
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
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::findOrFail($id);

        $this->authorize('view', [User::class, $user]);

        return response()->json([
            'status' => true,
            'message' => "Successful.",
            'data' => $user
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Models\Http\Requests\UserRequest  $request
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UserRequest $request, $id)
    {
        // Authorization was declared in the UserRequest

        // Retrieve the validated input data....
        $validatedData = $request->validated();
        $user = User::findOrFail($id);
        $old_user_data = collect($user)->toArray();

        $update_email = false;
        if ($validatedData['email'] != $user->email) {
            $new_email = $validatedData['email'];
            unset($validatedData['email']);
            $update_email = true;
        }
        if ($request->type == 'affiliate') {
            $validatedData['company_affiliate'] = $request->company_affiliate;
        }
        if ($request->type == 'employer'){
            $validatedData['company_size'] = $request->company_size;
            $validatedData['services'] = $request->services;
        }
        $user->update($validatedData);

        // save audit trail log
        $old_values = $old_user_data;
        $new_values = $validatedData;
        Audit::log($user->id, 'users.updated', $old_values, $new_values, User::class, $user->id);

        $email_changed_msg = '';

        if ($update_email) {
            // create and send email verification token records
            $verification = new Verification();
            $verification->action = "edit_email";
            $verification->sent_to = $new_email;
            $verification->metadata = ['new_email' => $new_email];
            $verification->is_otp = false;
            $verification = $user->verifications()->save($verification);
            if ($verification && $new_email) {
                if (config('mail.queue_send')) {
                    Mail::to($new_email)->queue(new VerifyEditEmail($user, $verification));
                } else {
                    Mail::to($new_email)->send(new VerifyEditEmail($user, $verification));
                }
            }
            $email_changed_msg = "We sent a verification to {$new_email} to make sure it’s a valid email address.";
            $email_changed_msg .= " If it doesn’t appear within a few minutes, check your spam folder.";
        }

        return response()->json([
            'status' => true,
            'message' => "Profile information updated successfully. $email_changed_msg",
            'data' => User::find($user->id)
        ], 200);
    }

    /**
     * Archive the specified resource from active list.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function archive($id)
    {
        $user = User::findOrFail($id);

        $this->authorize('delete', [User::class, $user]);

        $user->archived_at = now();
        $user->save();

        return response()->json([
            'status' => true,
            'message' => "User \"{$user->first_name}\" archived successfully.",
            'data' => User::find($user->id)
        ], 200);
    }

    public function activate($id)
    {
        $user = User::findOrFail($id);

        $this->authorize('update', [User::class, $user]);

        $user->is_active = 1;
        $user->save();
        if ($user->email) {
            Mail::to($user->email)->send(new ActivateUserEmail($user));
        }
        return response()->json([
            'status' => true,
            'message' => "User \"{$user->first_name}\" activated successfully.",
            'data' => User::find($user->id)
        ], 200);
    }
    /**
     * Unarchive the specified resource from archived storage.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function unarchive($id)
    {
        $user = User::findOrFail($id);

        $this->authorize('delete', [User::class, $user]);

        $user->archived_at = null;
        $user->save();

        return response()->json([
            'status' => true,
            'message' => "User \"{$user->first_name}\" unarchived successfully.",
            'data' => User::find($user->id)
        ], 200);
    }

    /*
     * Remove the specified resource from storage.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        $this->authorize('delete', [User::class, $user]);

        $name = $user->first_name;
        // check if the record is linked to other records
        $relatedRecordsCount = related_records_count(User::class, $user);

        if ($relatedRecordsCount <= 0) {
            $user->delete();
            return response()->json([
                'status' => true,
                'message' => "User deleted successfully.",
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => "The record cannot be deleted because it is associated with {$relatedRecordsCount} other record(s). You can archive it instead.",
            ], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function destroyMultiple(Request $request)
    {
        $ids = $request->input('ids');
        $valid_ids = [];
        $deleted_count = 0;
        if (is_array($ids)) {
            foreach ($ids as $id) {
                $user = User::find($id);
                if ($user) {
                    $this->authorize('delete', [User::class, $user]);
                    $valid_ids[] = $user->id;
                }
            }
        }
        $valid_ids = collect($valid_ids);
        if ($valid_ids->isNotEmpty()) {
            foreach ($valid_ids as $id) {
                $user = User::find($id);
                // check if the record is linked to other records
                $relatedRecordsCount = related_records_count(User::class, $user);
                if ($relatedRecordsCount <= 0) {
                    $user->delete();
                    $deleted_count++;
                }
            }
        }

        return response()->json([
            'status' => true,
            'message' => "{$deleted_count} users deleted successfully.",
        ], 200);
    }

    /**
     * Resend verification email
     *
     * @param string $id
     * @return \Illuminate\Http\Response
     */
    public function resendVerification($id)
    {
        $user = User::findOrFail($id);

        $this->authorize('update', [User::class, $user]);

        // create and send email verification token records
        $verification = new Verification();
        $verification->action = "register";
        $verification->sent_to = $user->email;
        $verification->metadata = null;
        $verification->is_otp = false;
        $verification = $user->verifications()->save($verification);
        if ($verification && $user->email && !$user->email_verified_at) {
            if (config('mail.queue_send')) {
                Mail::to($user->email)->queue(new WelcomeVerifyEmail($user, $verification));
            } else {
                Mail::to($user->email)->send(new WelcomeVerifyEmail($user, $verification));
            }
        }

        return response()->json([
            'status' => true,
            'message' => "Verification email sent successfully. If it doesn’t appear within a few minutes, check your spam folder.",
            'data' => User::find($user->id)
        ], 200);
    }

    /**
    * Change and update password.
    *
    * @param  \App\Models\Http\Requests\ChangePasswordRequest  $request
    * @param  string  $id
    * @return \Illuminate\Http\Response
    */
    public function password(ChangePasswordRequest $request, $id)
    {
        // Authorization was declared in the ChangePasswordRequest

        // Retrieve the validated input data....
        $validatedData = $request->validated();
        $user = User::findOrFail($id);
        // Password will be encrypted by User model password field attribute setter
        $user->password = $validatedData['new_password'];
        $user->password_updated_at = Carbon::now();
        $user->save();

        // Force logout and re-generate token if enabled.
        $force_logout = (isset($validatedData['force_logout']) && $validatedData['force_logout'] == true) ? true : false;
        if ($force_logout) {
            // Revoke all tokens...
            $user->tokens()->delete();
            // Create new token
            $abilities = [];
            array_push($abilities, "access:{$user->type}");
            $token =  $user->createToken('access-token', $abilities)->plainTextToken;
        }

        // save audit trail log
        $old_values = [];
        $new_values = [];
        Audit::log($user->id, 'change_password', $old_values, $new_values, User::class, $user->id);

        // send email notification
        if ($user->email) {
            if (config('mail.queue_send')) {
                Mail::to($user->email)->queue(new PasswordChanged($user));
            } else {
                Mail::to($user->email)->send(new PasswordChanged($user));
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Password changed successfully.',
            'data' => [
                'force_logout' => $force_logout
            ]
        ], 200);
    }


    public function photo(UserPhotoRequest $request, $id)
    {
        // Authorization was declared in the UserPhotoRequest

        // Retrieve the validated input data....
        $validatedData = $request->validated();
        $user = User::findOrFail($id);

        if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
            $extension = $request->file('photo')->extension();
            $filename = $user->id . '-' . time() . '-' . Str::random(32);
            $filename = "{$filename}.$extension";
            $year = date('Y');
            $month = date('m');
            $rel_upload_path    = "profile/{$year}/{$month}";
            if ( config('app.env') == 'local') {
                $rel_upload_path = "local/{$rel_upload_path}"; // dir for dev environment test uploads
            }
            // do upload
            $uploaded_file_path = $request->file('photo')->storeAs($rel_upload_path, $filename);
            // $uploaded_file_path_1 = $request->file('photo')->store($rel_upload_path,$filename,'do_spaces');
            Storage::setVisibility($uploaded_file_path, 'public'); //set file visibility to  "public"
            // info([$uploaded_file_path,  $rel_upload_path, $filename]);
            // delete previously uploaded file if any
            if ($user->photo) {
                Storage::delete($user->photo);
            }
            // Update with the newly update file
            $user->photo = $uploaded_file_path;
            $user->save();

            // save audit trail log
            $old_values = [];
            $new_values = [];
            Audit::log($user->id, 'users.photo.updated', $old_values, $new_values, User::class, $user->id);

            return response()->json([
                'status' => true,
                'message' => 'Photo updated successfully.',
                'data' => [
                    'photo_url' => cloud_asset($uploaded_file_path),
                    'user' => $user
                ]
            ], 200);
        }
        return response()->json([
            'status' => false,
            'message' => "Could not upload photo, please try again.",
        ], 400);
    }


    /**
     * Display a listing of the user's campaigns.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function campaigns(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $this->authorize('view-any', Campaign::class);

        $per_page = $request->input('per_page', 100);
        $sort_by = $request->input('sort_by', 'created_at');
        $sort_dir = $request->input('sort_dir', 'asc');
        $search = $request->input('search');
        $datatable_draw = $request->input('draw'); // if any

        $campaigns = Campaign::where('user_id', $user->id)
        ->where( function($query) use ($search) {
            $query->where('title', 'LIKE', "%{$search}%");
        })->orderBy($sort_by, $sort_dir);

        if ($per_page === 'all' || $per_page <= 0 ) {
            $results = $campaigns->get();
            $campaigns = new \Illuminate\Pagination\LengthAwarePaginator($results, $results->count(), -1);
        } else {
            $campaigns = $campaigns->paginate($per_page);
        }

        $response = collect([
            'status' => true,
            'message' => "Successful."
        ])->merge($campaigns)->merge(['draw' => $datatable_draw]);

        return response()->json($response, 200);
    }


    /**
     * Display a listing of the user's affiliates.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function affiliates(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $this->authorize('view-any', Cv::class);

        $per_page = $request->input('per_page', 100);
        $sort_by = $request->input('sort_by', 'created_at');
        $sort_dir = $request->input('sort_dir', 'asc');
        $search = $request->input('search');
        $datatable_draw = $request->input('draw'); // if any

        $cvs = User::where( function ($query) use ($user) {
            // $query->whereHas('user', function($sub_query) use ($user) {
            //     $sub_query->where('referral_user_id', $user->id);
            //     return $sub_query;
            // });
            return $query->where('referral_user_id', $user->id);
            // return User::where('referral_user_id', $this->id)
        })->where( function($query) use ($search) {
            $query->where('first_name', 'LIKE', "%{$search}%");
        })->orderBy($sort_by, $sort_dir);

        if ($per_page === 'all' || $per_page <= 0 ) {
            $results = $cvs->get();
            $cvs = new \Illuminate\Pagination\LengthAwarePaginator($results, $results->count(), -1);
        } else {
            $cvs = $cvs->paginate($per_page);
        }

        $response = collect([
            'status' => true,
            'message' => "Successful."
        ])->merge($cvs)->merge(['draw' => $datatable_draw]);
        return response()->json($response, 200);
    }

    /**
     * Display a listing of the user's job invitations.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function jobInvitations(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $this->authorize('view-any', JobInvitation::class);

        $per_page = $request->input('per_page', 100);
        $sort_by = $request->input('sort_by', 'created_at');
        $sort_dir = $request->input('sort_dir', 'asc');
        $search = $request->input('search');
        $status = $request->input('status');
        $datatable_draw = $request->input('draw'); // if any

        $jobInvitations = JobInvitation::where( function ($query) use ($user, $status) {
            if ($user->type == 'talent') {
                $query->where('talent_user_id', $user->id);
            } else {
                $query->where('employer_user_id', $user->id);
            }
            if ($status) {
                $query->where('status', $status);
            }
        })->where( function($query) use ($search, $user) {
            if ($user->type == 'talent') {
                $query->whereHas('talentUser', function($sub_query) use ($search) {
                    $sub_query->where('first_name', 'LIKE', "%{$search}%");
                    $sub_query->OrWhere('last_name', 'LIKE', "%{$search}%");
                    $sub_query->OrWhere('company_name', 'LIKE', "%{$search}%");
                    return $sub_query;
                });
            } else {
                $query->whereHas('talentCv', function($sub_query) use ($search) {
                    $sub_query->where('first_name', 'LIKE', "%{$search}%");
                    $sub_query->OrWhere('last_name', 'LIKE', "%{$search}%");
                    $sub_query->OrWhere('email', 'LIKE', "%{$search}%");
                    $sub_query->OrWhere('phone', 'LIKE', "%{$search}%");
                    return $sub_query;
                });
            }
        })->orderBy($sort_by, $sort_dir);

        if ($per_page === 'all' || $per_page <= 0 ) {
            $results = $jobInvitations->get();
            $jobInvitations = new \Illuminate\Pagination\LengthAwarePaginator($results, $results->count(), -1);
        } else {
            $jobInvitations = $jobInvitations->paginate($per_page);
        }

        $response = collect([
            'status' => true,
            'message' => "Successful."
        ])->merge($jobInvitations)->merge(['draw' => $datatable_draw]);
        return response()->json($response, 200);
    }

    public function notifications(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // $this->authorize('view-any', JobInvitation::class);

        $per_page = $request->input('per_page', 25);
        $sort_by = $request->input('sort_by', 'created_at');
        $sort_dir = $request->input('sort_dir', 'desc');

        $notifications = Notification::where('user_id', $user->id)->latest();
        // ->orderBy($sort_by, $sort_dir);

        if ($per_page === 'all' || $per_page <= 0 ) {
            $results = $notifications->get();
            $notifications = new \Illuminate\Pagination\LengthAwarePaginator($results, $results->count(), -1);
        } else {
            $notifications = $notifications->paginate($per_page);
        }

        $response = collect([
            'status' => true,
            'message' => "Successful."
        ])->merge($notifications);
        return response()->json($response, 200);
    }

    public function seenNotification(Request $request, $id)
    {
        $notification = Notification::find($id);

        if($notification){
            $notification->seen = 1;
            $notification->save();

            return response()->json([
                'status' => true,
                'message' => 'Succesfull'
            ], 200);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Not found'
            ], 200);
        }
    }
}
