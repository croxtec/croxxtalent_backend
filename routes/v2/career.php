<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group( function () {
   // Competence
   Route::prefix('talent')->name('talent')->group( function () {
        Route::get('career/progress', 'Api\v2\Talent\TalentCompetencyController@progress')->name('competence.progress');
        Route::get('career/suggestion', 'Api\v2\Talent\TalentCompetencyController@suggestion')->name('competence.suggestion');
        Route::get('career/competency/match', 'Api\v2\Talent\TalentCompetencyController@competencyMatch')->name('competence.match');
        Route::get('career/competency/recommendation', 'Api\v2\Talent\TalentCompetencyController@competencyRecommendation')->name('competence.recommendation');
        Route::get('career/job/training', 'Api\v2\Talent\TalentCompetencyController@jobTraining')->name('competence.job-training');
        Route::get('career/explore', 'Api\v2\Talent\TalentCompetencyController@exploreAssessment')->name('competence.explore');
    });

    // Trainings & Learning Path
    Route::get('trainings/employee/{code}', 'Api\v2\Learning\TrainingHubController@employee')->name('trainings.employee');
    Route::get('trainings', 'Api\v2\Learning\TrainingHubController@index')->name('trainings.index');
    Route::get('trainings/recommended', 'Api\v2\Learning\TrainingHubController@recommended')->name('trainings.recommended');
    Route::get('trainings/paths', 'Api\v2\Learning\TrainingHubController@paths')->name('trainings.paths');
    Route::get('trainings/review/{code}', 'Api\v2\Learning\TrainingHubController@show')->name('trainings.course');
    Route::get('trainings/lesson/{code}/{alias}', 'Api\v2\Learning\TrainingHubController@lesson')->name('trainings.lesson');
});
