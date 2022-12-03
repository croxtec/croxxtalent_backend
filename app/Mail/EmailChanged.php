<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;
use GeoIPLocation;
use Jenssegers\Agent\Agent;
use App\Models\User;

class EmailChanged extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $old_email;
    public $new_email;

    public $clientGeoLocation;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user, $old_email, $new_email)
    {
        $this->user = $user;
        $this->old_email = $old_email;
        $this->new_email = $new_email;

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
        $subject = "Your email address was successfully changed";
        $emoji = "=E2=9D=97";// Red Heavy exclamation mark symbol
        //add emoji before the subject
        $subject = "=?UTF-8?Q?" . $emoji . quoted_printable_encode(' ' . $subject) . "?=";

        return $this->subject($subject)
                    ->view('api.emails.email_changed')
                    ->text('api.emails.email_changed_plain')
                    ->with([
                        'name' => $this->user->name,
                        'email' => $this->old_email,
                        'old_email' =>$this->old_email,
                        'new_email' => $this->new_email,
                        'clientGeoLocation' => $this->clientGeoLocation
                    ]);
    }
}
