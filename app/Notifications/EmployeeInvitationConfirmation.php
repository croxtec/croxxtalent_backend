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
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        $locale = $notifiable->locale ?? app()->getLocale();

        return [
            'employee_id' => $this->employee ? $this->employee->user_id : null,
            'employee_code' => $this->employee ? $this->employee->code : null,
            'type' => 'EmployeeInvitationConfirmation',
            'message' => __('notifications.employee_invitation_confirmation.message', [
                'employee_name' => $this->employee?->name
            ], $locale),
        ];
    }
}