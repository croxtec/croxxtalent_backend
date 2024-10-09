<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Models\Goal;
use App\Models\User;
use App\Notifications\GoalReminderNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class SendGoalReminderNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */

     protected $signature = 'goals:send-reminders';
     protected $description = 'Send batch notifications for goals with reminders due in the current hour';



    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $timezone = 'Africa/Lagos';

        $startHour = Carbon::now($timezone)->startOfHour();
        $endHour = Carbon::now($timezone)->endOfHour();
        $this->info(implode(' ', ['Search Reminder between', $startHour, $endHour]));
        // Retrieve all goals where the reminder_date matches the current hour and hasn't been notified
        $goalsDue = Goal::whereBetween('reminder_date', [$startHour, $endHour])
            ->where('status', 'pending')
            ->get();

        if ($goalsDue->isEmpty()) {
            $this->info('No goals are due for notifications this hour.');
            return;
        }

         // Loop through the goals and send batch notifications
         foreach ($goalsDue as $goal) {
            $user = null;
            $name = '';
            if($goal->type == 'supervisor') {
                $employee = Employee::find($goal->employee_id);
                $user = $employee->talent;
                $name = $employee->name;
            }else{
                $user = User::find($goal->user_id);
                $name =  $user->name;
            }

        //    $this->info($user->email);
            // Send the reminder notification to the goal owner
           Notification::send($user,new GoalReminderNotification($goal, $name));
        }

        $this->info('Sent notifications for goals due in the current hour.');
    }
}
