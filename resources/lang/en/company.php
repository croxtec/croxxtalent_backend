<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during authentication for various
    | messages that we need to display to the user. You are free to modify
    | these language lines according to your application's requirements.
    |
    */

    'employee' => [
        'created' => 'Employee created successfully.',
        'imported' => 'Data imported successfully.',
        'updated' => 'Employee ":name" updated successfully.',
        'status_updated' => 'Employee ":name" status updated to :status.',
        'account_created' => '":name" created successfully. Please check your email to confirm your account. If you don\'t see the email, check your spam folder.',
        'archived' => 'Employee ":name" archived successfully.',
        'restored' => 'Employee ":name" restored successfully.',
        'import_failed' => 'Could not upload file, please try again.',
        'employee_exists' => 'Employee already exists.',
    ],

    'supervisor' => [
        'already_exists' => 'Some supervisors already exist.',
        'created' =>  'Supervisors added successfully',
        'removed' => 'Supervisor removed successfully',
    ],

    'department' => [
        'created' => 'Department ":title" created succesfully',
        'updated' => "Department updated successfully",
        'not_found' => "Department not found",
        'archived' => "Department ':title' archived successfully",
        'restored' => "Department ':title' restored successfully",
        'deleted' =>  'Department ":title" has been deleted successfully.',
        'cannot_delete' => 'The department ":title" could not be deleted because it has associated with ":relatedRecordsCount" other record(s). You can archive it instead.',
    ],

    'competency' => [
       'generated' => 'Competency Mapping and KPI setup generated successfully.',
       'generation_error' => 'An error occurred while generating the Competency Mapping and KPI setup. Please try again later.',
       'add_competency' => 'ComCompetency and KPI mapping stored successfully',
       'mapping_error' => 'Error storing competency mapping',
       'matched' => 'Competency matched',
        'invalid_faq_type' => 'Invalid FAQ type provided',
        'faq_reviewed' => 'FAQ :faqType has been marked as reviewed',
    ]

];
