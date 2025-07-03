<?php

return [
    'assessment' => [
        'created' => 'Assessment created successfully',
        'store_error' => 'Failed to create assessment',
        'updated' => 'Assessment updated successfully',
        'published' => 'Assessment ":name" published successfully',
        'already_published' => 'Assessment ":name" is already published',
        'archived' => 'Assessment ":name" archived successfully',
        'unarchived' => 'Assessment ":name" unarchived successfully',
        'not_found' => 'Assessment not found',
    ],

    'questions' => [
        'generated' => 'Assessment questions generated successfully',
        'created' => 'Assessment Question created',
        'create_error' => 'Failed to create assessment question',
        'updated' => 'Assessment Question updated',
        'update_error' => 'Failed to update assessment question',
        'not_found' => 'Assessment question not found',
        'archived' => 'Question archived',
        'restored' => 'Question restored',
        'deleted' => 'Assessment ":name" deleted successfully',
        'delete_error' => 'The ":name" record cannot be deleted because it is associated with :count other record(s). You can archive it instead.',
    ],
    
    'feedbacks' => [
        'already_submitted' => 'You have already submitted this assessment',
        'submitted' => 'Assessment submitted',
        'cannot_delete' => 'Assessment already submitted',
        'score_messages' => [
            'exceptional' => 'Exceptional! You scored :score out of :total points, hitting a fantastic :percentage%%! You\'re a master in this area!',
            'fantastic' => 'Fantastic! You scored :score out of :total points, achieving an impressive :percentage%%. Keep pushing, you\'re almost at the top!',
            'great_job' => 'Great job! You got :score out of :total points, which is a solid :percentage%%. You\'re doing well, keep up the hard work!',
            'good_effort' => 'Good effort! You achieved :score out of :total points, making it :percentage%%. There\'s potential for more, just keep refining your skills!',
            'getting_there' => 'You\'re getting there! You earned :score out of :total points, which is :percentage%%. Keep practicing and you\'ll see more progress.',
            'decent_try' => 'A decent try! You got :score out of :total points, making it :percentage%%. Focus on your weak areas to see better results next time.',
            'keep_going' => 'Keep going! You scored :score out of :total points, which is :percentage%%. Practice will help you get there, don\'t give up!',
            'learning_experience' => 'A learning experience! You scored :score out of :total points, making it :percentage%%. Keep working, and you\'ll improve in no time.',
            'persist' => 'Don\'t worry, you scored :score out of :total points, which is :percentage%%. Stay persistent, and you\'ll get better results with more practice!'
        ]
    ],
    
    'training' => [
        'participants_added' => 'Employees have been added to this training.',
        'lessons_retrieved' => 'Lessons successfully retrieved.',
        'lesson_cloned' => 'Lesson cloned successfully.',
        'updated' => 'Training updated successfully.',
        'archived' => 'Training archived successfully.',
        'unarchived' => 'Training restored successfully.',
    ],

    'lessons' => [
        'exists' => 'Lesson already available',
        'created' => 'New Lesson added successfully',
        'create_error' => 'Error creating lesson',
        'upload_error' => 'The video failed to upload',
        'updated' => 'Lesson updated successfully',
        'archived' => 'Lesson archived successfully',
        'restored' => 'Lesson restored successfully',
    ],

    'resources' => [
        'uploaded' => 'Lesson resources uploaded successfully',
        'upload_error' => 'Error uploading lesson resources',
        'deleted' => 'Resource deleted successfully',
        'delete_error' => 'Error deleting resource',
    ],

    'projects' => [
        'fetched' => 'Team structure and projects fetched successfully',
        'overview' => 'Project overview fetched successfully',
        'created' => 'Project created successfully',
        'create_error' => 'Could not complete project creation',
        'team_updated' => 'Team updated successfully',
        'team_removed' => 'Team member removed successfully',
        'team_not_found' => 'Team member not found',
        'updated' => 'Project updated successfully',
        'archived' => 'Project archived successfully',
        'restored' => 'Project restored successfully',
    ],

    'goals' => [
        'created' => 'Task created successfully',
        'updated' => 'Task updated successfully',
        'not_found' => 'Task not found',
        'update_error' => 'Failed to update goal',
        'competencies_added' => 'Competencies added successfully',
        'competency_removed' => 'Competency removed successfully',
        'employees_assigned' => 'Employees assigned successfully',
        'employee_unassigned' => 'Employee unassigned successfully',
        'archived' => 'Project archived successfully',
        'restored' => 'Project restored successfully',
    ],

    'comments' => [
        'employee_not_found' => 'Employee record not found',
        'added' => 'Comment added successfully',
        'updated' => 'Comment updated successfully',
        'deleted' => 'Comment deleted successfully',
        'not_found' => 'Comment not found',
        'update_error' => 'Failed to update comment',
        'delete_error' => 'Failed to delete comment',
        'unauthorized' => 'Unauthorized to perform this action on comment',
    ],

    
    'activities' => [
        'goal_created' => 'Task created: :title',
        'employee_assigned' => 'Employee assigned to task',
        'competency_added' => 'Competency added to task',
        'goal_updated' => 'Task updated: :fields changed',
        'competency_removed' => 'Competency removed from task',
        'employee_removed' => 'Employee removed from task',
        'goal_archived' => 'Task archived',
        'goal_restored' => 'Task restored',
        'comment_added' => 'Comment added to goal',
        'comment_updated' => 'Comment updated',
        'comment_deleted' => 'Comment deleted from goal',
    ],

    'campaigns' => [
        'created' => 'Campaign created successfully',
        'create_error' => 'Could not complete campaign creation',
        'updated' => 'Campaign updated successfully',
        'archived' => 'Campaign archived successfully',
        'published' => 'Campaign published successfully',
        'closed' => 'Campaign closed successfully',
        'restored' => 'Campaign restored successfully',
        'deleted' => 'Campaign deleted successfully',
        'delete_error' => 'The ":name" record cannot be deleted because it is associated with :count other record(s). You can archive it instead.',
        'multi_deleted' => ':count campaigns deleted successfully',
    ],
    
    'candidates' => [
        'rated' => 'Candidate has been reviewed',
        'invited' => 'An invitation has been sent to :name',
        'invite_error' => 'Could not complete invitation request',
        'scored' => 'Interview scored successfully',
        'withdrawn' => 'Job application has been withdrawn successfully',
    ],
];