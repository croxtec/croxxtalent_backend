<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Models\Employee;
use App\Models\Verification;

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
    public function __construct(Employee $employee, User $employer, Verification $verification)
    {
        $this->employee = $employee;
        $this->employer = $employer;
        $this->verification = $verification;
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
                        'company_name' => $this->employer->name,
                        'name' => $this->employee->name,
                        'email' => $this->employee->email,
                        'verification_token' => $this->verification->token,
                        'verification_url' => route('api.links.verifications.verify_employee', ['token' => $this->verification->token]),
                    ]);
    }
}
