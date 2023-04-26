<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class WelcomeEmployee extends Mailable
{
    use Queueable, SerializesModels;

    public $employee;
    public $employer;


    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $employee, User $employer)
    {
        $this->employee = $employee;
        $this->employer = $employer;
    }

       /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = 'Welcome To ' .$this->employer->name;
        $emoji = "=E2=9A=A1";// Yellow hazard symbol
        //add emoji before the subject
        $subject = "=?UTF-8?Q?" . $emoji . quoted_printable_encode(' ' . $subject) . "?=";

        return $this->subject($subject)
                    ->view('api.emails.welcome_employee_email')
                    ->text('api.emails.welcome_employee_email_plain')
                    ->with([
                        'name' => $this->employee->name,
                        'email' => $this->employee->email
                    ]);
    }
}
