<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group( function () {

    Route::get('goals/employee/{code}', 'Api\v2\GoalController@employee');//->name('assesments.index');
    Route::get('goals/overview/performance', 'Api\v2\GoalController@overview')->name('goals.overview');
    Route::get('goals/overview/calendar', 'Api\v2\GoalController@calendarOverview')->name('goals.overview.calendar');
    Route::patch('goals/{id}/archive', 'Api\v2\GoalController@archive')->name('goals.archive');
    Route::patch('goals/{id}/unarchive', 'Api\v2\GoalController@unarchive')->name('goals.unarchive');

    Route::resources([
        'goals' => 'Api\v2\GoalController',
    ]);
});
