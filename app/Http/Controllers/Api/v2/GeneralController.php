<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Controller;
use App\Mail\ContactMail;
use App\Models\MailSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use stdClass;
use Illuminate\Support\Facades\Validator;

class GeneralController extends Controller
{
    public function __construct()
    {
        info('Location');
    }

    public function contact(Request $request){
        $validator = Validator::make($request->all(),[
            'fullname' => 'required|max:30',
            'subject' => 'required|max:50',
            'phone' => 'required|max:20',
            'email' => 'required|email',
            'message' => 'required|max:768',
        ]);

        if($validator->fails()){
            $status = false;
            $message = $validator->errors()->toJson();
            return response()->json(compact('status', 'message') , 400);
        }

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
