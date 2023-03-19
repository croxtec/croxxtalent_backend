<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use App\Http\Requests\CvRequest;
use App\Http\Requests\CvPhotoRequest;
use Illuminate\Support\Facades\Validator;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Cv;
use App\Models\Audit;
use App\Libraries\LinkedIn;


class TalentCVController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = $request->user();
        // $this->authorize('view', [Cv::class, $cv]);
        // Retrieve the validated input data...

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
            // Attach Cv skill
            $cv_skills = [];
            foreach($cv->skills as $sk){
                $sk->secondary;
                $sk->tertiary;
                // array_push($cv_skills, $sk);
            }
            // $cv->cv_skills = $cv_skills;
            $this->authorize('view', [Cv::class, $cv]);


            return response()->json([
                'status' => true,
                'message' => "Resume loaded successfully.",
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
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CvRequest $request)
    {
        // Authorization was declared in the Form Request
        $user = $request->user();
        $cv = CV::where('user_id', $user->id)->firstorFail();
        // Retrieve the validated input data...
        $validatedData = $request->validated();
        // $user = User::findOrFail($validatedData['user_id']);
        $cv->update($request->all());
        return response()->json([
            'status' => true,
            'message' => "CV profile saved successfully.",
            'data' => Cv::find($cv->id)
        ], 200);
    }


    public function contact(Request $request)
    {
        // Authorization was declared in the Form Request
        $user = $request->user();
        $cv = CV::where('user_id', $user->id)->firstorFail();

        $validator = Validator::make($request->all(),[
            'phone' => 'required|max:25',
            'email' => 'required|email|max:150',
            'country_code' => 'required|exists:countries,code',
            'city' => 'required|max:255',
            'state_id' => 'required|exists:states,id',
            'postal_code' => 'nullable|max:10',
            'address' => 'required|max:255'
        ]);
        // Retrieve the validated input data...
        if($validator->fails()){
            return response()->json([
              'status' => false,
              'errors' =>  $validator->errors()->toJson()
            ], 400);
        }
        // $user = User::findOrFail($validatedData['user_id']);
        $cv->update($request->all());
        return response()->json([
            'status' => true,
            'message' => "CV contact saved successfully.",
            'data' => Cv::find($cv->id)
        ], 200);
    }

     /**
    * Upload and update photo.
    *
    * @param  \App\Models\Http\Requests\CvPhotoRequest  $request
    * @param  string  $id
    * @return \Illuminate\Http\Response
    */
    public function photo(Request $request, $id)
    {
        // Authorization was declared in the CvPhotoRequest

        // Retrieve the validated input data....
        // $validatedData = $request->validated();
        $cv = Cv::findOrFail($id);

        if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
            $extension = $request->file('photo')->extension();
            $filename = $cv->id . '-' . time() . '-' . Str::random(32);
            $filename = "{$filename}.$extension";
            $year = date('Y');
            $month = date('m');
            $rel_upload_path    = "profile/{$year}/{$month}";
            if ( config('app.env') == 'local') {
                $rel_upload_path = "local/{$rel_upload_path}"; // dir for dev environment test uploads
            }
            // do upload
            $uploaded_file_path = $request->file('photo')->storeAs($rel_upload_path, $filename);
            Storage::setVisibility($uploaded_file_path, 'public'); //set file visibility to  "public"
            // delete previously uploaded file if any
            if ($cv->photo) {
                Storage::delete($cv->photo);
            }
            // Update with the newly update file
            $cv->photo = $uploaded_file_path;
            $cv->save();

            return response()->json([
                'status' => true,
                'message' => 'Photo uploaded successfully.',
                'data' => [
                    'photo_url' => cloud_asset($uploaded_file_path),
                    'cv' => $cv
                ]
            ], 200);
        }
        return response()->json([
            'status' => true,
            'message' => "Could not upload photo, please try again.",
        ], 400);
    }

    /**
     * Publish a CV
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function publish(Request $request, $id)
    {
        $cv = Cv::findOrFail($id);

        $this->authorize('update', [Cv::class, $cv]);
        $old_is_published = $cv->is_published;

        $cv->is_published = true;
        $cv->save();

        // save audit trail log
        $event = $old_is_published === false ? 'cvs.created' : 'cvs.updated';
        $old_values = ['is_published' => $old_is_published];
        $new_values = ['is_published' => $cv->is_published];
        Audit::log($request->user()->id, $event, $old_values, $new_values, Cv::class, $cv->id);

        // Send notifications to the references
        if ($cv->references) {
            foreach($cv->references as $cvReference) {
                $cvReference->sendReferenceRequestEmail();
            }
        }

        return response()->json([
            'status' => true,
            'message' => "CV published and sent successfully.",
            'data' => Cv::find($cv->id)
        ], 200);
    }

    /**
     * Unublish a CV
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function unpublish($id)
    {
        $cv = Cv::findOrFail($id);

        $this->authorize('update', [Cv::class, $cv]);

        $cv->is_published = false;
        $cv->save();

        return response()->json([
            'status' => true,
            'message' => "CV unpublished successfully.",
            'data' => Cv::find($cv->id)
        ], 200);
    }

    /**
     * Import LinkedIn Profile
     *
     * @param Illuminate\Http\Request $request
     * @param string $id
     * @return \Illuminate\Http\Response
     */
    public function importLinkedIn(Request $request, $id)
    {
        $cv = Cv::findOrFail($id);

        // https://api.croxxtalent.com/v1/links/cvs/import-linkedin-callback

        $linkedIn = new LinkedIn([
            'api_key' => env('LINKEDIN_APP_CLIENT_ID'),
            'api_secret' => env('LINKEDIN_APP_CLIENT_SECRET'),
            'callback_url' => route('api.links.cvs.import_linkedin_callback')
        ]);

        $login_url = $linkedIn->getLoginUrl([
            LinkedIn::SCOPE_BASIC_PROFILE,
            // LinkedIn::SCOPE_FULL_PROFILE, // needs approval
            LinkedIn::SCOPE_EMAIL_ADDRESS,
            // LinkedIn::SCOPE_CONTACT_INFO, // needs approval
        ]);

        session(['oauth2_target_cv_id' => $cv->id]);

        return redirect($login_url);
    }

    /**
     * Import LinkedIn Profile Callback
     *
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function importLinkedInCallback(Request $request)
    {
        $error_message = null;
        try {
            $error = $request->query('error');
            $error_description = $request->query('error_description');
            if ($error_description) {
                $error_message = $error_description;
                $data_retrieved = false;
            } else {

                $id = session('oauth2_target_cv_id');
                $cv = Cv::findOrFail($id);

                $authorization_code = $request->query('code');

                $linkedIn = new LinkedIn([
                    'api_key' => env('LINKEDIN_APP_CLIENT_ID'),
                    'api_secret' => env('LINKEDIN_APP_CLIENT_SECRET'),
                    'callback_url' => route('api.links.cvs.import_linkedin_callback')
                ]);

                $access_token = $linkedIn->getAccessToken($authorization_code);
                $access_token_expires = $linkedIn->getAccessTokenExpiration();

                // $info = $linkedIn->get("/me?projection=(id,firstName,lastName,profilePicture(displayImage~:playableStreams))");
                $profileInfo = $linkedIn->get("/me?projection=(id,firstName,lastName)");
                $emailInfo = $linkedIn->get("/emailAddress?q=members&projection=(elements*(handle~))");

                if ($profileInfo->firstName) {
                    $cv->first_name = $profileInfo->firstName->localized->en_US;
                }
                if ($profileInfo->lastName) {
                    $cv->last_name = $profileInfo->lastName->localized->en_US;
                }
                if ($emailInfo->elements[0]) {
                    $cv->email = $emailInfo->elements[0]->{'handle~'}->emailAddress;
                }
                $cv->save();

                $data_retrieved = true;
            }
        }
        catch(Exception $e) {
            $data_retrieved = false;
            $error_message = $e->getMessage();
        }
        catch(\RuntimeException $e) {
            $data_retrieved = false;
            $error_message = $e->getMessage();
        }

        return view('api.links.cvs.oauth2_import')
                ->with( compact('data_retrieved', 'error_message') );;
    }

}
