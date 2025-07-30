<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group( function () {

    Route::get('goals/employee/{code}', 'Api\v2\GoalController@employee');//->name('assesments.index');
    Route::get('goals/overview/performance', 'Api\v2\GoalController@overview')->name('goals.overview');
    Route::get('goals/overview/calendar', 'Api\v2\GoalController@calendarOverview')->name('goals.overview.calendar');
    Route::patch('goals/{id}/archive', 'Api\v2\GoalController@archive')->name('goals.archive');
    Route::patch('goals/{id}/unarchive', 'Api\v2\GoalController@unarchive')->name('goals.unarchive');

    // New employee self-assessment routes
    Route::post('goals/{id}/employee-submit','Api\v2\GoalController@employeeSubmit');
    // Route::get('goals/pending-employee', [GoalController::class, 'pendingEmployeeGoals']);
    
    // New supervisor review routes 
    Route::post('goals/{id}/supervisor-review', 'Api\v2\GoalController@supervisorReview');
    // Route::get('goals/pending-supervisor-review', [GoalController::class, 'pendingSupervisorReview']);
    
    //Media uploads endpoints
    Route::get('documents/company', 'Api\v2\MediaController@getCompanyDocuments');
    Route::get('documents/employee/{code}', 'Api\v2\MediaController@getEmployeeDocuments');


    Route::resources([
        'goals' => 'Api\v2\GoalController',
    ]);
});
