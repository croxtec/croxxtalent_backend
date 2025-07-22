<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmployeeGoalSubmissionNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $goal;
    protected $employee;

    /**
     * Create a new notification instance.
     *
     * @param  mixed  $goal
     * @param  mixed  $employee
     * @return void
     */
    public function __construct($goal, $employee)
    {
        $this->goal = $goal;
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
            ->subject(__('notifications.employee_goal_submission.subject', [], $locale))
            ->view('api.emails.company.employee_goal_submission_notification', [
                'goal' => $this->goal,
                'employee' => $this->employee,
                'supervisor' => $notifiable,
                'buttonUrl' => url("/company/goals/{$this->goal->id}/review"),
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
            'type' => 'EmployeeGoalSubmission',
            'goal_id' => $this->goal->id,
            'goal_title' => $this->goal->title,
            'employee_id' => $this->employee->id,
            'employee_name' => $this->employee->name,
            'employee_status' => $this->goal->employee_status,
            'message' => __('notifications.employee_goal_submission.database_message', [
                'employee_name' => $this->employee->name,
                'goal_title' => $this->goal->title,
                'status' => __('goals.status.' . $this->goal->employee_status, [], $locale)
            ], $locale),
        ];
    }
}

