<?php

namespace App\Http\Controllers\Api\v2\Resume;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Http\Requests\CvRequest;
use App\Http\Requests\CvPhotoRequest;
use Illuminate\Support\Facades\Validator;

use App\Models\User;
use App\Models\Cv;
use App\Models\Audit;
use Cloudinary\Cloudinary;

class TalentCVController extends Controller
{
    protected $cloudinary;

    public function __construct(Cloudinary $cloudinary)
    {
        $this->cloudinary = $cloudinary;
    }

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
                'phone' => $user->phone,
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
     * Store a newly created resource in storage
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeInformation(CvRequest $request)
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


    public function storeContact(Request $request)
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
    public function photo(Request $request)
    {
        $user = $request->user();
        // Authorization was declared in the CvPhotoRequest
        // Retrieve the validated input data....
        // $validatedData = $request->validated();
        $cv = CV::where('user_id', $user->id)->firstorFail();

        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

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
            $rel_upload_path  = "CroxxPH/CV";

            // Delete previously uploaded file if any
            if ($cv->photo) {
                $public_id = pathinfo($cv->photo, PATHINFO_FILENAME); // Extract public_id from URL
                info(['Public ID', $public_id]);
                $this->cloudinary->uploadApi()->destroy($public_id);
            }

            // Upload new photo
            $result = $this->cloudinary->uploadApi()->upload($file->getRealPath(), [
                'folder' => $rel_upload_path, // Specify a folder
            ]);

            // Update with the newly update file
            $cv->photo = $result['secure_url'];
            $cv->save();

            return response()->json([
                'status' => true,
                'message' => 'Photo uploaded successfully.',
                'data' => [
                    'photo_url' => $result['secure_url'],
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
}
