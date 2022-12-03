<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Models\Verification;

class WelcomeVerifyEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $verification;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user, Verification $verification)
    {
        $this->user = $user;
        $this->verification = $verification;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = 'Verify your account on ' . config('myapp.name');
        $emoji = "=E2=9A=A1";// Yellow hazard symbol
        //add emoji before the subject
        $subject = "=?UTF-8?Q?" . $emoji . quoted_printable_encode(' ' . $subject) . "?=";

        return $this->subject($subject)
                    ->view('api.emails.welcome_verify_email')
                    ->text('api.emails.welcome_verify_email_plain')
                    ->with([
                        'name' => $this->user->name,
                        'email' => $this->verification->sent_to,
                        'verification_token' => $this->verification->token,
                        'verification_url' => route('api.links.verifications.verify_email', ['token' => $this->verification->token]),
                    ]);
    }
}
