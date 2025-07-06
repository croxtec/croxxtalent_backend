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

    protected $cvReference;

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
        $locale = $this->jobInvitation->talentCv->locale ?? app()->getLocale();
        $subject = __('notifications.job_invitation.subject', [], $locale);
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
