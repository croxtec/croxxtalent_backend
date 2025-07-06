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
        $locale = $notifiable->locale ?? app()->getLocale();
        
        return (new MailMessage)
            ->subject(__('notifications.assessment_published.subject', [
                'assessment_title' => $this->assessment->title
            ], $locale))
            ->view('api.emails.company.assessment_notifications', [
                'assessment' => $this->assessment,
                'employee' => $this->employee,
                'role' => $this->role,
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
            'user_id' => $this->employee ? $this->employee->user_id : null,
            'type' => 'ManageAssessment',
            'assessment_code' => $this->assessment->code,
            'message' => $this->generateMessageContent($locale),
        ];
    }

    /**
     * Generate message content based on the role.
     *
     * @param string $locale
     * @return string
     */
    private function generateMessageContent($locale)
    {
        if ($this->role === 'supervisor') {
            return __('notifications.assessment_published.supervisor_message', [
                'employee_name' => $this->employee->name,
                'assessment_name' => $this->assessment->name
            ], $locale);
        }

        return __('notifications.assessment_published.employee_message', [
            'employee_name' => $this->employee->name,
            'assessment_name' => $this->assessment->name
        ], $locale);
    }
}