<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::middleware('auth:sanctum')->name('api.')->group( function () {
    // Employee
    Route::get('projects/employee/{code}', 'Api\v2\Project\ProjectController@employee')->name('projects.employee');
    // Project operations
    Route::get('projects/overview', 'Api\v2\Project\ProjectController@overview')->name('projects.overview');
    Route::pst('projects/{id}/add-team', 'Api\v2\Project\ProjectController@addTeam')->name('projects.addTeam');
    Route::pst('projects/{id}/remove-team', 'Api\v2\Project\ProjectController@addTeam')->name('projects.removeTeam');
    Route::patch('projects/{id}/archive', 'Api\v2\Project\ProjectController@archive')->name('projects.archive');
    Route::patch('projects/{id}/unarchive', 'Api\v2\Project\ProjectController@unarchive')->name('projects.unarchive');

    // Goal and Task Operations
    Route::patch('project_goals/{id}/archive', 'Api\v2\Project\ProjectGoalController@archive')->name('project_goals.archive');
    Route::patch('project_goals/{id}/unarchive', 'Api\v2\Project\ProjectGoalController@unarchive')->name('project_goals.unarchive');

    // Goal Competencies
    Route::post('project_goals/{goal}/competencies', 'Api\v2\Project\ProjectGoalController@addCompetency');
    Route::delete('project_goals/{goal}/competencies/{competency}', 'Api\v2\Project\ProjectGoalController@removeCompetency');

    Route::post('project_goals/{goal}/employees', 'Api\v2\Project\ProjectGoalController@assignEmployee');
    Route::delete('project_goals/{goal}/employees/{employee}', 'Api\v2\Project\ProjectGoalController@removeCompetency');

    Route::resources([
        'projects' => 'Api\v2\Project\ProjectController',
        'milestones' => 'Api\v2\Project\MilestoneController',
        'project_goals' => 'Api\v2\Project\ProjectGoalController',
        'goal_comments' => 'Api\v2\Project\TaskCommentController',
    ]);

});
