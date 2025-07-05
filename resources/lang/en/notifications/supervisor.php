<?php

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
];