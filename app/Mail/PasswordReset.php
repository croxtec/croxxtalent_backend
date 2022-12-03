<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Models\Verification;

class PasswordReset extends Mailable
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
        return $this->subject('Password reset code')
                    ->view('api.emails.password_reset')
                    ->text('api.emails.password_reset_plain')
                    ->with([
                        'name' => $this->user->name,
                        'email' => $this->verification->sent_to,
                        'verification_token' => $this->verification->token,
                    ]);
    }
}
