<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProjectAssignedNotification extends Notification
{
    use Queueable;

    protected $project;
    protected $employee;
    protected $role;

    /**
     * Create a new notification instance.
     *
     * @param $project
     * @param $employee
     * @param $role
     */
    public function __construct($project, $employee, $role)
    {
        $this->project = $project;
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
            ->subject(__('notifications.project_assigned.subject', [
                'project_title' => $this->project->title
            ], $locale))
            ->view('api.emails.company.project_assigned_notifications', [
                'project' => $this->project,
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
            'user_id' => $this->employee ? $this->employee?->user_id : null,
            'type' => 'ManageProject',
            'project_code' => $this->project->code,
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
        if ($this->role === 'lead') {
            return __('notifications.project_assigned.lead_message', [
                'employee_name' => $this->employee?->name,
                'project_title' => $this->project->title
            ], $locale);
        }

        return __('notifications.project_assigned.employee_message', [
            'employee_name' => $this->employee?->name,
            'project_title' => $this->project->title
        ], $locale);
    }
}