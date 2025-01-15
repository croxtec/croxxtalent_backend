<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::middleware('auth:sanctum')->name('api.')->group( function () {

    // Goal Competencies
    Route::post('project_goals/{goal}/competencies', 'Api\v2\Project\ProjectGoalController@addCompetency');
    Route::delete('project_goals/{goal}/competencies/{competency}', 'Api\v2\Project\ProjectGoalController@removeCompetency');

    Route::post('project_goals/{goal}/employees', 'Api\v2\Project\ProjectGoalController@assignEmployee');
    Route::delete('project_goals/{goal}/employees/{employee}', 'Api\v2\Project\ProjectGoalController@removeCompetency');

    Route::resources([
        'projects' => 'Api\v2\Project\ProjectController',
        'milestones' => 'Api\v2\Project\MilestoneController',
        'project_goals' => 'Api\v2\Project\ProjectGoalController',
    ]);

});
