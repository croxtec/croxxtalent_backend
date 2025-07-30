<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GoalReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $goal;
    protected $name;

    public function __construct($goal, $name)
    {
        $this->goal = $goal;
        $this->name = $name;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $locale = $notifiable->locale ?? app()->getLocale();
        
        return (new MailMessage)
            ->subject(__('notifications.goal_reminder.subject', [
                'goal_title' => $this->goal->title
            ], $locale))
            ->view('api.emails.goal_reminder', [
                'goal' => $this->goal,
                'name' => $this->name,
                'locale' => $locale,
            ]);
    }

    public function toArray($notifiable)
    {
        $locale = $notifiable->locale ?? app()->getLocale();
        
        return [
            'goal_title' => $this->goal->title,
            'goal_description' => $this->goal->description,
            'reminder_time' => $this->goal->reminder_date,
            'message' => __('notifications.goal_reminder.database_message', [
                'name' => $this->name,
                'goal_title' => $this->goal->title
            ], $locale),
        ];
    }
}