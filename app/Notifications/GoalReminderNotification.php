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
        return (new MailMessage)
            ->subject('Reminder: Your Goal "' . $this->goal->title . '" Needs Attention')
            ->view('api.emails.goal_reminder', [
                'goal' => $this->goal,
                'name' => $this->name
            ]);
    }

    public function toArray($notifiable)
    {
        return [
            'goal_title' => $this->goal->title,
            'goal_description' => $this->goal->description,
            'reminder_time' => $this->goal->reminder_date,
            'message' => "Hi {$this->name}, this is a reminder about your goal '{$this->goal->title}'. Please review your progress and take necessary actions to meet the goal before the deadline.",
        ];
    }
}
