<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;
use App\Models\CvReference;

class CvReferenceRequest extends Mailable
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
        $subject = "Reference Request from {$this->cvReference->cv->name} - " . config('myapp.name');
        return $this->subject($subject)
                    ->from( config('mail.from.address'), $this->cvReference->cv->name )
                    ->replyTo( $this->cvReference->cv->email, $this->cvReference->cv->name )
                    ->view('api.emails.cv_reference_request')
                    ->text('api.emails.cv_reference_request_plain')
                    ->with([
                        'name' => $this->cvReference->name,
                        'email' => $this->cvReference->email,
                        'cvReference' => $this->cvReference,
                        'form_url' => URL::signedRoute('api.links.cv_references.questionnaire_form', ['id' => $this->cvReference->id]),
                        'cv_url' => $this->cvReference->cv->pdf_url,
                    ]);
    }
}
