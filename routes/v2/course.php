<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group( function () {
    // Courses
    Route::get('courses/progress', 'Api\v2\Learning\CourseController@progress')->name('courses.progress');
    Route::get('courses/suggest/{id}', 'Api\v2\Learning\CourseController@suggest')->name('courses.suggest');
    Route::post('courses/suggest/{id}', 'Api\v2\Learning\CourseController@cloneSuggestionRequest')->name('courses.curatr_suggest');
    Route::get('company/courses', 'Api\v2\Learning\CourseController@courses')->name('company.courses');

    Route::patch('courses/{id}/publish', 'Api\v2\Learning\CourseController@publish')->name('courses.publish');
    Route::patch('courses/{id}/archive', 'Api\v2\Learning\CourseController@archive')->name('courses.archive');
    Route::patch('courses/{id}/unarchive', 'Api\v2\Learning\CourseController@unarchive')->name('courses.unarchive');

    Route::get('courses/{id}/participants', 'Api\v2\Learning\CourseController@participants')->name('courses.participants');
    Route::post('courses/add/participants', 'Api\v2\Learning\CourseController@enrollParticipants')->name('enroll.participants');

    //
    Route::post('lessons/resources', 'Api\v2\Learning\LessonResourceController@store');

    Route::resources([
        'courses' => 'Api\v2\Learning\CourseController',
        'lessons' => 'Api\v2\Learning\LessonController',
    ]);
});

