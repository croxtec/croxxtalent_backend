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

    // public function toMail($notifiable)
    // {
    //     return  (new TalentJobInvitation($this->jobInvitation))
    //                  ->to($notifiable->email);
    // }

    public function toMail($notifiable)
    {
        $locale = $notifiable->locale ?? app()->getLocale();
        
        return (new MailMessage)
            ->subject(__('notifications.job_invitation.subject', [], $locale))
            ->view('api.emails.talent_job_invitation', [
                'jobInvitation' => $this->jobInvitation,
                'locale' => $locale,
            ]);
    }

    public function toDatabase($notifiable)
    {
        $locale = $notifiable->locale ?? app()->getLocale();
        
        return [
            'job_invitation_id' => $this->jobInvitation->id,
            'message' => __('notifications.job_invitation.database_message', [
                'employer_name' => $this->jobInvitation->employerUser->display_name
            ], $locale),
            'campaign_id' => $this->jobInvitation->campaign_id,
        ];
    }
}
