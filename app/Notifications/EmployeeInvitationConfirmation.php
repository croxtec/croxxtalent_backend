<?php

namespace App\Notifications;

use App\Models\Employee;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmployeeInvitationConfirmation extends Notification
{
    use Queueable;

    protected $employee;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Employee $employee)
    {
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
        return ['database'];
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
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        $message = $this->employee?->name . ' has successfully accepted the employee invitation. They are now officially part of your company. Please proceed with the next steps for onboarding and access provisioning.';
        // info($message);
        return [
            'employee_id' => $this->employee ? $this->employee->user_id : null, // Custom user_id
            'employee_code' => $this->employee ? $this->employee->code : null, // Custom user_id
            'type' => 'EmployeeInvitationConfirmation',
            'message' =>  $message
        ];
    }

}
