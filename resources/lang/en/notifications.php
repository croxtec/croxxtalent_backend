<?php
// resources/lang/en/notifications.php

return [
    'supervisor' => [
        'page_title' => 'Supervisor Notification',
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
        'accepted_subject' => 'Job invitation accepted by :talent_name',
        'rejected_subject' => 'Job invitation rejected by :talent_name',
        'accepted_message' => 'Your job invitation/offer was <b style="color: green;">accepted</b> by <b>:talent_name</b>.',
        'rejected_message' => 'Your job invitation/offer was <b style="color: red;">rejected</b> by <b>:talent_name</b>.',
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

    'password_reset' => [
        'page_title' => 'Password Reset',
        'subject' => 'Password reset code',
        'greeting' => 'Hi :name,',
        'message' => 'We received a request to reset your <a href=":url" target="_blank">:app_name</a> password.',
        'code_label' => 'Your Password Reset Code is',
        'validity' => 'This code is valid for 30 minutes or until a next code is generated.',
    ],
    
    'campaign_published' => [
        'page_title' => 'Campaign Published',
        'subject' => 'Campaign published',
        'greeting' => 'Hi :name,',
        'message' => 'Your campaign <b>":title"</b> has been <b style="color: red;">published</b>.',
    ],
    
    'assessment_feedback' => [
        'page_title' => 'Assessment Feedback',
        'subject' => 'Your Assessment Feedback is Now Available',
        'greeting' => 'Dear :name,',
        'message' => 'Your assessment titled <b>":assessment_name"</b> (Code: :code) has been reviewed, and your feedback is now available. This assessment is an important part of your professional development and performance evaluation.',
        'encouragement' => 'We encourage you to carefully review the feedback provided by your supervisor, which contains valuable insights to help guide your career growth. Please take the time to reflect on the feedback and make improvements where necessary.',
        'button_text' => 'View Feedback',
        'database_message' => 'Hello :name, a supervisor has published your assessment feedback.',
    ],
    
    'supervisor_goal' => [
        'page_title' => 'Supervisor Goal Assignment',
        'subject' => 'A New Goal Has Been Assigned to You',
        'greeting' => 'Dear :name,',
        'message' => 'Your supervisor, <b>:supervisor_name</b>, has assigned a new goal to you: <b>":goal_title"</b>.',
        'instruction' => 'Please log in to the platform to review the goal and start working towards completing it. This goal is an important part of your performance and growth within the company.',
        'button_text' => 'View Goal',
        'database_message' => 'Your supervisor, :supervisor_name, has assigned a new goal to you: ":goal_title".',
    ],
    
    'employee_invitation_confirmation' => [
        'page_title' => 'Employee Invitation Confirmation',
        'message' => ':employee_name has successfully accepted the employee invitation. They are now officially part of your company. Please proceed with the next steps for onboarding and access provisioning.',
    ],
    
    'otp' => [
        'page_title' => 'One-Time Password',
        'subject' => 'One-Time Password (OTP) for your request',
        'greeting' => 'Hi :name,',
        'message' => 'Your One-Time Password (OTP) is',
        'validity' => 'This OTP is valid for 30 minutes or until a next OTP is generated.',
    ],

    'welcome_employee' => [
        'page_title' => 'Welcome Notification',
        'talent_subject' => 'Exciting Opportunities Await You at :company_name',
        'employee_subject' => 'Welcome to :company_name! We\'re Glad to Have You',
    ],
    
    'welcome_verify_email' => [
        'page_title' => 'Email Verification',
        'subject' => 'Verify your account on :app_name',
    ],
    
    'email_templates' => [
        'notification_title' => 'Notification - :app_name',
        'greeting' => 'Hi :name,',
        'profile_registered' => 'Your profile has been registered with :app_name.',
        'verify_email_instruction' => 'Simply click the button below to verify your email address.',
        'verify_button_text' => 'Click here to verify email address',
        'invitation_title' => 'Invitation to Join :company_name - :app_name',
        'join_team_title' => 'Join the Team at :company_name',
        'talent_invitation_message' => 'We are excited to invite you to officially join the :company_name team on our platform. Your profile has been linked to the company\'s employee management system, where you\'ll be able to access important company resources and collaborate with your team.',
        'talent_verify_instruction' => 'Simply click the button below to verify your email address and complete your onboarding process.',
        'employee_invitation_message' => 'You\'ve been invited to join :company_name on our platform! By joining, you will gain access to company tools, resources, and be a part of their employee management system.',
        'employee_verify_instruction' => 'Please click the button below to verify your email address and get started as a member of :company_name.',
        'verify_email_button' => 'Verify Your Email Address',
    ],

    'password_change' => [
        'page_title' => 'Password Change',
        'subject' => 'Your password was successfully changed',
        'notification_message' => 'Your password has been successfully changed for your :app_name account.',
    ],

    'profile_change' => [
        'page_title' => 'Profile Update',
        'subject' => 'Profile information updated',
        'notification_message' => 'Changes has been made to your :app_name profile information.',
    ],

    'complimentary_close' => [
        'text' => 'Sincerely,',
        'team' => 'The :app_name Team',
    ],

    'geo_location' => [
        'title' => 'When and where this happened:',
        'date' => 'Date:',
        'ip' => 'IP:',
        'browser' => 'Browser:',
        'os' => 'Operating System:',
        'location' => 'Approximate Location:',
        'security_warning' => 'Didn\'t do this?',
        'security_action' => 'Be sure to reset your password right away.',
    ],
];