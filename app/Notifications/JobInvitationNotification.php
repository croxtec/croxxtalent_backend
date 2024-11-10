<?php

namespace App\Notifications;

use App\Mail\TalentJobInvitation;
use App\Models\JobInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class JobInvitationNotification extends Notification
{
    use Queueable;

    protected $jobInvitation;

    public function __construct(JobInvitation $jobInvitation)
    {
        $this->jobInvitation = $jobInvitation;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return  (new TalentJobInvitation($this->jobInvitation))
                     ->to($notifiable->email);
    }

    public function toDatabase($notifiable)
    {
        return [
            'job_invitation_id' => $this->jobInvitation->id,
            'message' => "You have a new job invitation/offer from <b>{$this->jobInvitation->employerUser->display_name}</b>.",
            'campaign_id' => $this->jobInvitation->campaign_id,
        ];
    }
}
