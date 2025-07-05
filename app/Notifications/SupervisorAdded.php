<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SupervisorAdded extends Notification
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
        $locale = $notifiable->locale ?? app()->getLocale();
        
        return (new MailMessage)
                ->subject(__('notifications.supervisor.added.subject', [], $locale))
                ->view('api.emails.supervisor_added', [
                    'supervisor' => $this->supervisor,
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
            'message' => __('notifications.supervisor.added.body', [
                'name' => $this->supervisor->name,
                'job_code' => $this->supervisor?->department?->job_code
            ], $locale),
            'supervisor_id' => $this->supervisor->id,
        ];
    }
}