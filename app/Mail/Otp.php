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
use App\Models\Verification;

class Otp extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $verification;

    public $clientGeoLocation;


    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user, Verification $verification)
    {
        $this->user = $user;
        $this->verification = $verification;

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
        return $this->subject('One-Time Password (OTP) for your request')
                    ->view('api.emails.otp')
                    ->text('api.emails.otp_plain')
                    ->with([
                        'name' => $this->user->name,
                        'email' => $this->verification->sent_to,
                        'verification_token' => $this->verification->token,
                        'clientGeoLocation' => $this->clientGeoLocation
                    ]);
    }
}
