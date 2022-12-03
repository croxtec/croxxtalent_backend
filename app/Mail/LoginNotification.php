<?php

namespace App\Mail;

use App\Models\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;
use GeoIPLocation;
use Jenssegers\Agent\Agent;

class LoginNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $user;

    public $clientGeoLocation;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user)
    {
        $this->user = $user;

        $agent = new Agent();
        $browser = $agent->browser();
        $browser_name = $browser . ' ' . $agent->version($browser);
        $platform = $agent->platform();
        $os_name = $platform . ' ' . $agent->version($platform);

        $this->clientGeoLocation = (object) [
            'dateTime' =>  Carbon::now()->toDayDateTimeString(),
            'ip' =>  GeoIPLocation::getIP(),
            'location' =>  trim(GeoIPLocation::getLocation()),
            'browser' =>  $browser_name,
            'os' =>  $os_name
        ];
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // Send Push notification
        $notification = new Notification();
        $notification->user_id = $this->user->id;
        $notification->action = "/campaings"; 
        $notification->category = "danger"; 
        $notification->title = 'Security Alert';
        $notification->message = "Your account  was successfully logged in to from a new device.";
        $notification->save(); 
        
        $subject = "Security alert for your account";
        $emoji = "=E2=9D=97";// Red Heavy exclamation mark symbol
        //add emoji before the subject
        $subject = "=?UTF-8?Q?" . $emoji . quoted_printable_encode(' ' . $subject) . "?=";

        return $this->subject($subject)
                    ->view('api.emails.account.login_notification')
                    ->text('api.emails.account.login_notification_plain')
                    ->with([
                        'name' => $this->user->username,
                        'email' => $this->user->email,
                        'clientGeoLocation' => $this->clientGeoLocation
                    ]);
    }
}
