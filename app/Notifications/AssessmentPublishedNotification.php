<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\NotificationContent;

class AssessmentPublishedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $assessment;
    protected $employee;
    protected $role;

    /**
     * Create a new notification instance.
     *
     * @param $assessment
     * @param $employee
     * @param $role
     */
    public function __construct($assessment, $employee, $role)
    {
        $this->assessment = $assessment;
        $this->employee = $employee;
        $this->role = $role;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $messageContent = $this->generateMessageContent();
        info( $messageContent );
        return (new MailMessage)
            ->subject('New Assessment Assigned: ' . $this->assessment->title)
            ->view('api.emails.assessment_notifications', [
                'assessment' => $this->assessment,
                'messageContent' => $messageContent,
                'notifiable' => $notifiable,
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'user_id' => $this->employee ? $this->employee->user_id : null,
            'type' => 'ManageAssessment',
            'assessment_id' => $this->assessment->id,
            'message' => $this->generateMessageContent(),
        ];
    }

    /**
     * Generate message content based on the role.
     *
     * @return string
     */
    private function generateMessageContent()
    {
        if ($this->role === 'supervisor') {
            return "Dear {$this->employee->name}, you have been assigned as a supervisor for the new assessment titled '{$this->assessment->title}' by your company.";
        }

        return "Dear {$this->employee->name}, you have been assigned a new assessment titled '{$this->assessment->title}' by your company.";
    }
}
