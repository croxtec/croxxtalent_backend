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
    public $verification;

    public $tries = 3;  // Number of retry attempts
    public $backoff = 10;  // Delay between retries (in seconds)


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
        $isTalent = $this->employee->talent;
        $subject = 'Welcome To ' . $this->employer->company_name;
        $emoji = "=E2=9A=A1"; // Yellow hazard symbol

        // Customize the subject based on employee type
        if ($isTalent) {
            $subject = "Exciting Opportunities Await You at " . $this->employer->company_name;
        } else {
            $subject = "Welcome to " . $this->employer->company_name . "! We're Glad to Have You";
        }

        // Add emoji before the subject
        $subject = "=?UTF-8?Q?" . $emoji . quoted_printable_encode(' ' . $subject) . "?=";

        return $this->subject($subject)
                    ->view('api.emails.welcome_employee_email')
                    ->text('api.emails.welcome_employee_email_plain')
                    ->with([
                        'is_talent' => $isTalent,
                        'company_name' => $this->employer->company_name,
                        'name' => $this->employee->name,
                        'email' => $this->employee->email,
                        'verification_token' => $this->verification->token,
                        'verification_url' => route('api.links.verifications.verify_employee', ['token' => $this->verification->token]),
                    ]);
    }
}
