<?php

namespace App\Helpers;

use App\Models\Employee;
use App\Notifications\AssessmentPublishedNotification;
use Illuminate\Support\Facades\Notification;

class AssessmentNotificationHelper{

    /**
     * Notify assigned users (employees and supervisors) about an assessment.
     *
     * @param  array  $employeeInstances
     * @param  array  $supervisorInstances
     * @param  mixed  $assessment
     * @return void
     */
    public static function notifyAssignedUsers($employeeInstances, $supervisorInstances, $assessment)
    {
        // Notify employees
        if (!empty($employeeInstances)) {
            $employees = collect();

            foreach ($employeeInstances as $assignedEmployee) {
                $employee = Employee::find($assignedEmployee->employee_id);
                if ($employee) {
                    $employees->push($employee);
                }
            }

            if ($employees->isNotEmpty()) {
                // Send batch notifications to employees
                foreach ($employees as $employee) {
                    Notification::send($employee, new AssessmentPublishedNotification($assessment, $employee, 'employee'));
                }
            }
        }

        // Notify supervisors
        if (!empty($supervisorInstances)) {
            $supervisors = collect();

            foreach ($supervisorInstances as $assignedEmployee) {
                $supervisor = Employee::find($assignedEmployee->employee_id);
                if ($supervisor) {
                    $supervisors->push($supervisor);
                }
            }

            if ($supervisors->isNotEmpty()) {
                // Send batch notifications to supervisors
                foreach ($supervisors as $supervisor) {
                    Notification::send($supervisor, new AssessmentPublishedNotification($assessment, $supervisor, 'supervisor'));
                }
            }
        }
    }

}
