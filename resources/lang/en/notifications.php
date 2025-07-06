<?php
// resources/lang/en/notifications.php
return [
    'supervisor' => [
        'added' => [
            'subject' => 'New Supervisor Assigned',
            'greeting' => 'Hello :name,',
            'message' => 'A new supervisor has been assigned to you. Please review the details below and reach out if you have any questions.',
            'footer' => 'If you have any questions, please contact your HR department.',
        ],
        'removed' => [
            'subject' => 'Supervisor Removed',
            'greeting' => 'Hello :name,',
            'message' => 'Your supervisor has been removed. Please contact your HR department if you have any questions about this change.',
            'footer' => 'If you have any questions, please contact your HR department.',
        ],
        'details' => 'Supervisor Details',
        'name' => 'Name',
        'department' => 'Department',
        'job_code' => 'Job Code',
        'view_dashboard' => 'View Dashboard',
        'body' => 'Your supervisor :name from :job_code has been updated.',
    ],
    
    'job_invitation' => [
        'page_title' => 'Job Invitation',
        'subject' => 'New Job Invitation',
        'greeting' => 'Hi :name,',
        'message' => 'You have a new job invitation/offer from :employer_name.',
        'interview_details' => ':employer_name has scheduled an interview with you at :interview_time',
        'login_instruction' => 'Please login to your :app_name account to view details and accept offer.',
        'database_message' => 'You have a new job invitation/offer from <b>:employer_name</b>.',
    ],
    
    'goal_reminder' => [
        'page_title' => 'Goal Reminder',
        'subject' => 'Reminder: Your Goal ":goal_title" Needs Attention',
        'greeting' => 'Hello :name,',
        'intro' => 'This is a friendly reminder about the goal assigned to you: ":goal_title".',
        'purpose' => 'This goal was set to help you achieve significant progress in your role and contribute to the company\'s success.',
        'metric_label' => 'Goal Metric:',
        'progress_message' => 'As the deadline approaches, we encourage you to review the progress you have made so far. Completing this goal will help in your personal development and will ensure that the team achieves its objectives.',
        'support_message' => 'If you need any assistance or clarification, feel free to reach out to your supervisor. Keep pushing forward, and we are confident you will meet this goal!',
        'view_button' => 'View Your Goal',
        'database_message' => 'Hi :name, this is a reminder about your goal ":goal_title". Please review your progress and take necessary actions to meet the goal before the deadline.',
    ],
    
    'assessment_published' => [
        'page_title' => 'Assessment Notification',
        'subject' => 'New Assessment Assigned: :assessment_title',
        'greeting' => 'Hello :name,',
        'supervisor_intro' => 'You have been assigned as a supervisor for the new assessment titled ":assessment_name" by your company.',
        'employee_intro' => 'You have been assigned a new assessment titled ":assessment_name" by your company.',
        'importance_message' => 'This assessment is an important part of your development and performance evaluation. Please log into the platform to complete the assessment.',
        'access_button' => 'Access Your Assessment',
        'supervisor_message' => 'Dear :employee_name, you have been assigned as a supervisor for the new assessment titled ":assessment_name" by your company.',
        'employee_message' => 'Dear :employee_name, you have been assigned a new assessment titled ":assessment_name" by your company.',
    ],
    
    'project_assigned' => [
        'page_title' => 'Project Assignment',
        'subject' => 'New Project Assignment: :project_title',
        'greeting' => 'Hello :name,',
        'lead_intro' => 'You have been assigned as a team lead for the new project titled ":project_title" by your company.',
        'employee_intro' => 'You have been assigned to a new project titled ":project_title" by your company.',
        'contribution_message' => 'You have been added to this project team and your contribution is essential to its success. Please log into the platform to view project details and your assigned tasks.',
        'details_label' => 'Project Details:',
        'title_label' => 'Title:',
        'start_date_label' => 'Start Date:',
        'end_date_label' => 'Expected Completion:',
        'view_button' => 'View Project Details',
        'lead_message' => 'Dear :employee_name, you have been assigned as a team lead for the new project titled ":project_title" by your company.',
        'employee_message' => 'Dear :employee_name, you have been assigned to a new project titled ":project_title" by your company.',
    ],
];