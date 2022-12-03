<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;
use App\Models\JobInvitation;

class TalentJobInvitationRejected extends Mailable
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
        $subject = "Job invitation rejected by {$this->jobInvitation->talentCv->name}";
        return $this->subject($subject)
                    ->replyTo( $this->jobInvitation->talentCv->email, $this->jobInvitation->talentCv->name)
                    ->view('api.emails.talent_job_invitation_rejected')
                    ->text('api.emails.talent_job_invitation_rejected_plain')
                    ->with([
                        'name' => $this->jobInvitation->employerUser->display_name,
                        'email' => $this->jobInvitation->employerUser->email,
                        'jobInvitation' => $this->jobInvitation,
                    ]);
    }
}
