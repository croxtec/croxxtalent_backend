<?php

namespace App\Http\Controllers\Api\v1\Link;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Cv;
use App\Libraries\LinkedIn;

class CvLinkController extends Controller
{
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
