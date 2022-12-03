<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;
use App\Models\CvReference;

class CvReferenceRequestApproved extends Mailable
{
    use Queueable, SerializesModels;

    public $cvReference;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(CvReference $cvReference)
    {
        $this->cvReference = $cvReference;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = "CV Reference Request approved by {$this->cvReference->name}";
        return $this->subject($subject)
                    ->replyTo( $this->cvReference->email, $this->cvReference->name)
                    ->view('api.emails.cv_reference_request_approved')
                    ->text('api.emails.cv_reference_request_approved_plain')
                    ->with([
                        'name' => $this->cvReference->cv->name,
                        'email' => $this->cvReference->cv->email,
                        'cvReference' => $this->cvReference,
                    ]);
    }
}
