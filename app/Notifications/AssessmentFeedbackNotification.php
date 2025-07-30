<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;


class AssessmentFeedbackNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $assessment;
    protected $employee;

    /**
     * Create a new notification instance.
     *
     * @param  mixed  $assessment
     * @param  mixed  $employee
     * @return void
     */
    public function __construct($assessment, $employee)
    {
        $this->assessment = $assessment;
        $this->employee = $employee;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
         $locale = $notifiable->locale ?? app()->getLocale();

        return (new MailMessage)
            ->subject(__('notifications.assessment_feedback.subject'))
            ->view('api.emails.company.assessment_feedback_notification', [
                'assessment' => $this->assessment,
                'employee' => $this->employee,
                'actionUrl' => url("/company"),
                'locale' => $locale,
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
        $locale = $notifiable->locale ?? app()->getLocale();

        return [
            'type' => 'AssessmentFeedback',
            'assessment_id' => $this->assessment->id,
            'assessment_code' => $this->assessment->code,
            'message' => __('notifications.assessment_feedback.database_message', [
                'name' => $this->employee->name
            ], $locale),
        ];
    }
}
