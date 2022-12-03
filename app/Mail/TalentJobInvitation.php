<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;
use App\Models\JobInvitation;
class TalentJobInvitation extends Mailable
{
    use Queueable, SerializesModels;

    public $cvReference;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(JobInvitation $jobInvitation)
    {
        $this->jobInvitation = $jobInvitation;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {   
        $subject = "New job invitation";
        return $this->subject($subject)
                    ->view('api.emails.talent_job_invitation')
                    ->text('api.emails.talent_job_invitation_plain')
                    ->with([
                        'name' => $this->jobInvitation->talentCv->name,
                        'email' => $this->jobInvitation->talentCv->email,
                        'jobInvitation' => $this->jobInvitation,
                    ]);
    }
}
