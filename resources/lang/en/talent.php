<?php

return [
    'application' => [
        'submitted' => 'Your job application has been submitted successfully',
        'error' => 'Could not complete job application request',
    ],
    
    'saved' => [
        'success' => 'Campaign has been saved successfully',
        'error' => 'Could not complete save campaign request',
    ],
    
    'campaign' => [
        'not_found' => 'Campaign not found or not published',
    ],
    
    'notifications' => [
        'campaign_application_title' => 'Campaign Application',
        'campaign_application_message' => 'A talent has just applied for :title campaign',
    ],
    
    'validation' => [
        'campaign_id_required' => 'The campaign ID is required',
    ],

    'goals'  => [
        'created' => 'New future goal created successfully',
        'updated' => 'Goal updated successfully',
        'not_found' => 'Goal not found',
        'create_error' => 'Could not create goal',
        'update_error' => 'Could not update goal',

    ],

    'job_invitation' => [
        'sent' => 'An invitation has been sent to :name',
        'archived' => 'Job invitation archived successfully',
        'unarchived' => 'Job invitation unarchived successfully',
        'deleted' => 'Job invitation deleted successfully',
        'not_found' => 'Job invitation not found',
        'create_error' => 'Could not send job invitation',
        'archive_error' => 'Could not archive job invitation',
        'unarchive_error' => 'Could not unarchive job invitation',
        'delete_error' => 'Could not delete job invitation',
        'delete_restricted' => 'The ":name" record cannot be deleted because it is associated with :count other record(s). You can archive it instead.',
        'notification_title' => 'Job Invitation',
        'notification_message' => 'You have a new job invitation/offer from :name',
    ],
];