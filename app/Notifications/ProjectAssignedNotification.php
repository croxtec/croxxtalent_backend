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
        $messageContent = $this->generateMessageContent();
        return (new MailMessage)
            ->subject('New Project Assignment: ' . $this->project->title)
            ->view('api.emails.company.project_assigned_notifications', [
                'project' => $this->project,
                'messageContent' => $messageContent,
                'notifiable' => $notifiable,
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
            'user_id' => $this->employee ? $this->employee?->user_id : null,
            'type' => 'ManageProject',
            'project_code' => $this->project->code,
            'message' => $this->generateMessageContent(),
        ];
    }

    /**
     * Generate message content based on the role.
     *
     * @return string
     */
    private function generateMessageContent()
    {
        if ($this->role === 'lead') {
            return "Dear {$this->employee?->name}, you have been assigned as a team lead for the new project titled '{$this->project->title}' by your company.";
        }

        return "Dear {$this->employee?->name}, you have been assigned to a new project titled '{$this->project->title}' by your company.";
    }
}
