<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SupervisorReviewNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $goal;
    protected $supervisor;

    /**
     * Create a new notification instance.
     *
     * @param  mixed  $goal
     * @param  mixed  $supervisor
     * @return void
     */
    public function __construct($goal, $supervisor)
    {
        $this->goal = $goal;
        $this->supervisor = $supervisor;
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
            ->subject(__('notifications.supervisor_goal_review.subject', [], $locale))
            ->view('api.emails.company.supervisor_goal_review_notification', [
                'goal' => $this->goal,
                'supervisor' => $this->supervisor,
                'employee' => $notifiable,
                'buttonUrl' => url("/company/goals/{$this->goal->id}"),
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
        $reviewAction = $this->goal->supervisor_status === $this->goal->employee_status ? 
            'approved' : 'modified';

        return [
            'type' => 'SupervisorGoalReview',
            'goal_id' => $this->goal->id,
            'goal_title' => $this->goal->title,
            'supervisor_id' => $this->supervisor->id,
            'supervisor_name' => $this->supervisor->name,
            'supervisor_status' => $this->goal->supervisor_status,
            'review_action' => $reviewAction,
            'message' => __('notifications.supervisor_goal_review.database_message', [
                'supervisor_name' => $this->supervisor->name,
                'goal_title' => $this->goal->title,
                'action' => __('notifications.supervisor_goal_review.actions.' . $reviewAction, [], $locale)
            ], $locale),
        ];
    }
}