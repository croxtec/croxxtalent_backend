<?php

namespace App\Helpers;

use App\Models\Employee;
use App\Notifications\ProjectAssignedNotification;
use Illuminate\Support\Facades\Notification;

class ProjectNotificationHelper
{
    /**
     * Notify assigned users (team members and team leads) about a project.
     *
     * @param  array  $memberInstances
     * @param  array  $leadInstances
     * @param  mixed  $project
     * @return void
     */
    public static function notifyAssignedUsers($memberInstances, $leadInstances, $project)
    {
        // Notify team members
        if (!empty($memberInstances)) {
            $members = collect();

            foreach ($memberInstances as $assignedMember) {
                $member = Employee::find($assignedMember->employee_id);
                if ($member) {
                    $members->push($member);
                }
            }

            if ($members->isNotEmpty()) {
                // Send notifications to team members
                foreach ($members as $member) {
                    Notification::send($member, new ProjectAssignedNotification($project, $member, 'member'));
                }
            }
        }

        // Notify team leads
        if (!empty($leadInstances)) {
            $leads = collect();

            foreach ($leadInstances as $assignedLead) {
                $lead = Employee::find($assignedLead->employee_id);
                if ($lead) {
                    $leads->push($lead);
                }
            }

            if ($leads->isNotEmpty()) {
                // Send notifications to team leads
                foreach ($leads as $lead) {
                    Notification::send($lead, new ProjectAssignedNotification($project, $lead, 'lead'));
                }
            }
        }
    }
}