<?php

namespace App\Http\Controllers\Api\v1\Link;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Verification;
use App\Models\Audit;
use App\Mail\EmailChanged;

class VerificationLinkController extends Controller
{
    /**
     * Verify welcome email address
     * 
     * @param Illuminate\Http\Request $request
     * @param string $token
     * @return \Illuminate\Http\Response
     */
    public function verifyEmail(Request $request, $token)
    {
        $verification = Verification::where('action', 'register')->where('token', $token)->first();
        $verified = false;
        if ($verification) {
            $user = $verification->verifiable()->first();
            if ($user) {
                $user->email_verified_at = Carbon::now();
                $user->save();
                // delete token after verification
                $verification->delete();
                
                // save audit trail log
                $old_values = [];
                $new_values = [];
                Audit::log($user->id, 'email_verified', $old_values, $new_values, User::class, $user->id);

                $verified = true;
            }
        }
        
        return view('api.links.verifications.verify_email')
                ->with( compact('verified') );;
    } 

    /**
     * Verify welcome email address
     * 
     * @param Illuminate\Http\Request $request
     * @param string $token
     * @return \Illuminate\Http\Response
     */
    public function verifyEditEmail(Request $request, $token)
    {
        $verification = Verification::where('action', 'edit_email')->where('token', $token)->first();
        $verified = false;
        if ($verification) {
            $user = $verification->verifiable()->first();
            $old_email = $user->email;

            if ($user && isset($verification->metadata['new_email'])) {
                $user->email = $verification->metadata['new_email'];
                $user->email_updated_at = Carbon::now();
                $user->save();                
                $new_email = $user->email;
                // delete token after verification
                $verification->delete();

                // save audit trail log
                $old_values = ['email' => $old_email];
                $new_values = ['email' => $new_email];
                Audit::log($user->id, 'users.email.updated', $old_values, $new_values, User::class, $user->id);
                
                // send email notification
                if ($old_email) {                    
                    if (config('mail.queue_send')) {
                        Mail::to($old_email)->queue(new EmailChanged($user, $old_email, $new_email));
                    } else {
                        Mail::to($old_email)->send(new EmailChanged($user, $old_email, $new_email));
                    }
                }

                $verified = true;
            }
        }
        
        return view('api.links.verifications.verify_email')
                ->with( compact('verified') );;
    }
}
