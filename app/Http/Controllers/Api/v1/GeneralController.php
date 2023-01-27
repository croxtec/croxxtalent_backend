<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Mail\ContactMail;
use App\Models\MailSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use stdClass;

class GeneralController extends Controller
{
    public function postContact(Request $request){
        $this->validate($request, [
            'fullname' => 'required|max:30',
            'subject' => 'required|max:50',
            'phoneNumber' => 'required|max:20',
            'emailAddress' => 'required|email',
            'message' => 'required|max:768',
        ]);

        $feedback = new stdClass();
        $feedback->fullname = $request->fullname;
        $feedback->phone = $request->phoneNumber;
        $feedback->email = $request->emailAddress;
        $feedback->subject = $request->subject;
        $feedback->message = $request->message;

       Mail::to('support@croxxtalent.com')->send(new ContactMail($feedback));
       $msg = "Your Feedback has been submitted. Thank you";

       return response()->json([
            'status' => true,
            'message' => $msg
        ]);
    }

    public function newsletter(Request $request){
        $this->validate($request, [
            'email' => 'required|email|unique:mail_subscriptions'
        ], [
            'email.unique' => 'This email has already subscribe to our mailing list. .'
        ]);

        $subscribe = MailSubscription::create($request->all());

        $msg = "You have succesfully join our mailing list. Thank you";
        return response()->json([
            'status' => true,
            'message' => $msg
        ]);
    }

}
