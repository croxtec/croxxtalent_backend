<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\NotificationContent;

class AssessmentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $assessment;
    protected $employee;

    public function __construct($assessment, $employee =  null)
    {
        $this->assessment = $assessment;
        $this->employee = $employee;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    // public function toMail($notifiable)
    // {
    //     // $content = NotificationContent::where('type', 'assessment_assigned')->first();
    //     $content = collect([
    //         'type' => 'supervisor_assigned_assessment',
    //         'subject' => 'Manage Assessment',
    //         'message_template' => 'Hello, {name}. A new assessment has been assigned for you to manage. Please log in to view the details.'
    //     ]);
    //     info($content);
    //     $message = str_replace('{name}', $this->employee->name, $content->message_template);

    //     return (new MailMessage)
    //                 ->subject($content->subject)
    //                 ->view('api.emails.assessment_notifications', [
    //                     'assessment' => $this->assessment,
    //                     'message' => $message,
    //                 ]);
    // }

    // public function toArray($notifiable)
    // {
    //     return [
    //         'assessment_id' => $this->assessment->id,
    //         'message' => str_replace('{name}', $this->employee->name, $this->content->message_template),
    //     ];
    // }

    public function toMail($notifiable)
    {
        $message = $this->employee ? "Hello {$this->employee->name}, you have been assigned a new assessment." : "You have been assigned a new assessment.";
        return (new MailMessage)
            ->subject('New Assessment Assigned')
            ->view('api.emails.assessment_notifications', [
                'assessment' => $this->assessment,
                'message' => $message,
            ]);
    }

    public function toArray($notifiable)
    {
        // info([$this->employee?]);
        return [
            'user_id' => $this->employee ? $this->employee->user_id : null, // Custom user_id
            'type' => 'ManageAssessment', // Custom type
            'assessment_id' => $this->assessment->id,
            'message' => $this->employee ? "Hello {$this->employee->name}, you have been assigned a new assessment." : "You have been assigned a new assessment.",
        ];
    }
}
