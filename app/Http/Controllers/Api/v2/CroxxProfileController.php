<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Cv;
use App\Models\UserSetting;
use App\Models\Competency\TalentCompetency;
use App\Models\Assessment\CroxxAssessment;
use App\Models\Training\CroxxTraining;
use App\Models\Audit;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\UserRequest;
use App\Http\Requests\UserPhotoRequest;
use Cloudinary\Cloudinary;

class CroxxProfileController extends Controller
{

    protected $cloudinary;

    public function __construct(Cloudinary $cloudinary)
    {
        $this->cloudinary = $cloudinary;
    }

    /**
     * Display a user Public profile
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $username)
    {
        // $user = ($request->user()) ? $request->user() : null;

        $profile = User::whereIn('type', ['talent'])->where([
                'username' => $username,
                'is_active' => true
        ])->firstOrFail();
        $profile->makeHidden(['cv', 'password_updated_at']);

        $careers = [];
        $resume = CV::where('user_id', $profile->id)->first();
        $competencies = TalentCompetency::where('user_id', $profile->id)->get();
        $skills = $competencies->pluck('competency');

        foreach($competencies as $competency){
            $careerCompetency = $competency->getCareerByCompetency;

            $career = [
                'name' => $competency->competency,
                'level' => $competency->level,
                'assessment_taken' => [],
                'completed_trainings' => [],
                'currently_learning' => [],
                'summary' => ""
            ];
            // Add

            $assessments = CroxxAssessment::join('talent_assessment_summaries', 'croxx_assessments.id', '=', 'talent_assessment_summaries.assessment_id')
                        ->where('croxx_assessments.career_id', $careerCompetency->id)
                        ->where('talent_assessment_summaries.talent_id', $profile->id)
                        ->get();

            foreach ($assessments as $assessment) {
                $career['assessment_taken'][] = $assessment;
            }

            $trainings = CroxxTraining::join('course_libraries', 'croxx_trainings.id', '=', 'course_libraries.training_id')
                            ->where('croxx_trainings.career_id', $careerCompetency->id)
                            ->where('course_libraries.talent_id', $profile->id)
                            ->get();

            foreach ($trainings as $training) {
                $career['completed_trainings'][] = $training;
            }

            $careers[] = $career;
        }

        return response()->json([
            'status' => true,
            'data' => compact('profile',  'skills', 'resume','careers'),
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
     * Update the specified resource in storage.
     *
     * @param  \App\Models\Http\Requests\UserRequest  $request
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UserRequest $request)
    {
        // Authorization was declared in the UserRequest

        // Retrieve the validated input data....
        $validatedData = $request->validated();
        $user = $request->user();
        $old_user_data = collect($user)->toArray();

        $update_email = false;
        // if ($validatedData['email'] != $user->email) {
        //     $new_email = $validatedData['email'];
        //     unset($validatedData['email']);
        //     $update_email = true;
        // }
        // if ($request->type == 'affiliate') {
        //     $validatedData['company_affiliate'] = $request->company_affiliate;
        // }
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


    public function photo(UserPhotoRequest $request)
    {
        // Authorization was declared in the UserPhotoRequest

        // Retrieve the validated input data....
        $validatedData = $request->validated();
        $user = $request->user();

        if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
            $file = $request->file('photo');
            $extension = $file->extension();
            $fileSize = $file->getSize(); // size in bytes
            $transformation = [];

            // Check if the file size is greater than 700KB (700 * 1024 bytes)
            if ($fileSize > 700 * 1024) {
                // Reduce the size by 75%
                $transformation['quality'] = '60';
            }

            // Attach Filename
            $filename = time() . '-' . Str::random(32);
            $filename = "{$filename}.$extension";
            $year = date('Y');
            $rel_upload_path  = "CroxxPH/Profile";
            $old_photo = $user->photo;
            // Delete previously uploaded file if any
            if ($user->photo) {
                $public_id = pathinfo($user->photo, PATHINFO_FILENAME); // Extract public_id from URL
                info(['Public ID', $public_id]);
                $this->cloudinary->uploadApi()->destroy($public_id);
            }

            // Upload new photo
            $result = $this->cloudinary->uploadApi()->upload($file->getRealPath(), [
                'folder' => $rel_upload_path, // Specify a folder
            ]);

            // Update with the newly update file
            $user->photo = $result['secure_url'];
            $user->save();

            // save audit trail log
            $old_values = [$old_photo];
            $new_values = [$user->photo];
            Audit::log($user->id, 'users.photo.updated', $old_values, $new_values, User::class, $user->id);

            return response()->json([
                'status' => true,
                'message' => 'Photo updated successfully.',
                'data' => [
                    'user' => $user
                ]
            ], 200);
        }
        return response()->json([
            'status' => false,
            'message' => "Could not upload photo, please try again.",
        ], 400);
    }
}
