<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Models\Verification;

class ActivateUserEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = 'Account Activation ' . config('myapp.name');
     
        return $this->subject($subject)
                    ->view('api.emails.user_activated')
                    ->with([
                        'name' => $this->user->name,
                        'email' => $this->user->email,
                        'button_url' => route('login'),
                    ]);
    }
}
