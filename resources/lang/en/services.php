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
    ]
];