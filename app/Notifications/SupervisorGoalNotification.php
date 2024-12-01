<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SupervisorGoalNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $goal;
    protected $supervisor;
    protected $employee;

    /**
     * Create a new notification instance.
     *
     * @param  mixed  $goal
     * @param  mixed  $supervisor
     * @return void
     */
    public function __construct($goal, $supervisor, $employee)
    {
        $this->goal = $goal;
        $this->supervisor = $supervisor;
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
        return (new MailMessage)
            ->subject('A New Goal Has Been Assigned to You')
            ->view('api.emails.company.supervisor_goal_notification', [
                'goal' => $this->goal,
                'supervisor' => $this->supervisor,
                'employee' => $this->employee,
                'employee' => $notifiable,
                'buttonUrl' => url("/company"),
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
            'type' => 'CompanySupervisorGoal',
            'goal_id' => $this->goal->id,
            'goal_title' => $this->goal->title,
            'supervisor' => $this->supervisor->name,
            'message' => "Your supervisor, {$this->supervisor->name}, has assigned a new goal to you: \"{$this->goal->title}\".",
        ];
    }
}
