<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AssessmentPublishedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $assessment;
    protected $role;

    public function __construct($assessment, $role = null)
    {
        $this->assessment = $assessment;
        $this->role = $role;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        $message = $this->role == 'supervisor' ? "You have been assigned as a supervisor for a new assessment." : "You have been assigned a new assessment.";
        return (new MailMessage)
            ->subject('New Assessment Assigned')
            ->view('api.emails.assessment_notifications', [
                'assessment' => $this->assessment,
                'message' => $message,
            ]);
    }

    public function toArray($notifiable)
    {
        return [
            'user_id' => $notifiable->id,
            'type' => 'ManageAssessment',
            'assessment_id' => $this->assessment->id,
            'message' => $this->role == 'supervisor' ? "You have been assigned as a supervisor for a new assessment." : "You have been assigned a new assessment.",
        ];
    }
}
