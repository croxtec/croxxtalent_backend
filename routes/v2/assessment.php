<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::middleware('auth:sanctum')->group( function () {
   // Assesment Options
    // Route::patch('assessments/{id}/unpublish', 'Api\v2\AssesmentController@unpublish')->name('assessments.unpublish');
    Route::get('assessments/talent/{id}', 'Api\v2\Operations\ExperienceAssessmentController@talent')->name('assessments.talent');
    Route::put('assessments/{id}/update', 'Api\v2\Operations\ExperienceAssessmentController@update')->name('assessments.update');
    Route::patch('assessments/{id}/publish', 'Api\v2\Operations\ExperienceAssessmentController@publish')->name('assessments.publish');
    Route::patch('assessments/{id}/archive', 'Api\v2\Operations\ExperienceAssessmentController@archive')->name('assessments.archive');
    Route::patch('assessments/{id}/unarchive', 'Api\v2\Operations\ExperienceAssessmentController@unarchive')->name('assessments.unarchive');
    // Employee Assessment
    Route::get('assessments/employee/{code}', 'Api\v2\Operations\EmployeeAssessmentController@employee');//->name('assesments.index');
    Route::get('assessments/feedbacks/{code}', 'Api\v2\Operations\EmployeeAssessmentController@feedbacks');//->name('assesments.index');
    Route::post('assessments/talent/answer', 'Api\v2\Operations\EmployeeAssessmentController@storeTalentAnswer');//->name('assessments.index');
    Route::patch('assessments/{id}/talent/publish', 'Api\v2\Operations\EmployeeAssessmentController@publishTalentAnswers');//->name('assessments.index')

    // Manage Assesment
    Route::get('assessments/{id}/assigned/employees', 'Api\v2\ScoresheetController@employeeList');//->name('assessments.index');
    Route::get('assessments/{code}/result/{talent}', 'Api\v2\ScoresheetController@assessmentResult');//->name('assessments.index');
    Route::get('assessments/{code}/feedback/{talent}', 'Api\v2\ScoresheetController@assessmentFeedback');//->name('assessments.index');
    Route::post('assessments/{id}/supervisor/scoresheet', 'Api\v2\ScoresheetController@gradeAssessmentScoreSheet');//->name('assesments.index');
    Route::patch('assessments/{id}/supervisor/feedback', 'Api\v2\ScoresheetController@publishSupervisorFeedback');//->name('assesments.index');
    // Assesment Questions
    Route::post('assessments/questions/generate', 'Api\v2\Operations\AssesmentQuestionController@generate');//->name('assessments.index');
    Route::post('assessments/questions', 'Api\v2\Operations\AssesmentQuestionController@store');//->name('assessments.index');
    Route::patch('assessments/questions/{id}/archive', 'Api\v2\Operations\AssesmentQuestionController@archive')->name('assessments.archive');
     Route::patch('assessments/questions/{id}/unarchive', 'Api\v2\Operations\AssesmentQuestionController@unarchive')->name('assessments.unpublish');
    Route::delete('assessments/questions/{id}', 'Api\v2\Operations\AssesmentQuestionController@destroy');//->name('assessments.index');



    Route::resources([
        'assessments/evaluation' => 'Api\v2\Operations\EvaluationAssessmentController',
        'assessments' => 'Api\v2\Operations\ExperienceAssessmentController',
    ]);
});
