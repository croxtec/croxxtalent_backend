<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SupervisorRemoved extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    protected $supervisor;

    public function __construct($supervisor = null)
    {
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
        $message =  'Hi ' . $this->supervisor->name . ' You have been removed has '. $this->supervisor?->department?->job_code;

        return (new MailMessage)
                ->subject('Supervisor Update')
                ->view('api.emails.supervisor_removed', [
                    'supervisor' => $this->supervisor,
                    'message' => $message,
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
            'message' => 'Hi ' . $this->supervisor->name . ' You have been removed has '. $this->supervisor?->department?->job_code,
            'supervisor_id' => $this->supervisor->id,
        ];
    }
}
