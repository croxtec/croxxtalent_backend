<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('croxxtalent')->group( function () {
    // Professional
    Route::resources([ 'professional' => 'Api\v2\Admin\ProfessionalController' ]);
    Route::patch('professional/{id}/archive', 'Api\v2\Admin\ProfessionalController@archive')->name('professional.archive');
    Route::patch('professional/{id}/unarchive', 'Api\v2\Admin\ProfessionalController@unarchive')->name('professional.unarchive');
});

// Configurations
Route::prefix('settings')->name('api.settings.')->group( function () {
    // Timezones
    Route::get('timezones', 'Api\v2\Settings\TimezoneController@index')->name('languages.index');
    Route::get('timezones/{id}', 'Api\v2\Settings\TimezoneController@show')->name('languages.show');
    Route::middleware('auth:sanctum')->group( function () {
        Route::post('timezones', 'Api\v2\Settings\TimezoneController@store')->name('timezones.store');
        Route::put('timezones/{id}', 'Api\v2\Settings\TimezoneController@update')->name('timezones.update');
        Route::patch('timezones/{id}/archive', 'Api\v2\Settings\TimezoneController@archive')->name('timezones.archive');
        Route::patch('timezones/{id}/unarchive', 'Api\v2\Settings\TimezoneController@unarchive')->name('timezones.unarchive');
        Route::delete('timezones/{id}', 'Api\v2\Settings\TimezoneController@destroy')->name('timezones.destroy');
    });
    // Countries
    Route::get('countries', 'Api\v2\Settings\CountryController@index')->name('countries.index');
    Route::get('countries/{id}', 'Api\v2\Settings\CountryController@show')->name('countries.show');
    Route::middleware('auth:sanctum')->group( function () {
        Route::post('countries', 'Api\v2\Settings\CountryController@store')->name('countries.store');
        Route::put('countries/{id}', 'Api\v2\Settings\CountryController@update')->name('countries.update');
        Route::patch('countries/{id}/archive', 'Api\v2\Settings\CountryController@archive')->name('countries.archive');
        Route::patch('countries/{id}/unarchive', 'Api\v2\Settings\CountryController@unarchive')->name('countries.unarchive');
        Route::delete('countries/{id}', 'Api\v2\Settings\CountryController@destroy')->name('countries.destroy');
        Route::get('countries/{id}/states', 'Api\v2\Settings\CountryController@states')->name('countries.states');
    });
    // States
    Route::get('states', 'Api\v2\Settings\StateController@index')->name('states.index');
    Route::get('states/{id}', 'Api\v2\Settings\StateController@show')->name('states.show');
    Route::middleware('auth:sanctum')->group( function () {
        Route::post('states', 'Api\v2\Settings\StateController@store')->name('states.store');
        Route::put('states/{id}', 'Api\v2\Settings\StateController@update')->name('states.update');
        Route::patch('states/{id}/archive', 'Api\v2\Settings\StateController@archive')->name('states.archive');
        Route::patch('states/{id}/unarchive', 'Api\v2\Settings\StateController@unarchive')->name('states.unarchive');
        Route::delete('states/{id}', 'Api\v2\Settings\StateController@destroy')->name('states.destroy');
    });
    // Course Of Studies
    Route::get('course-of-studies', 'Api\v2\Settings\CourseOfStudyController@index')->name('course_of_studies.index');
    Route::get('course-of-studies/{id}', 'Api\v2\Settings\CourseOfStudyController@show')->name('course_of_studies.show');
    Route::middleware('auth:sanctum')->group( function () {
        Route::post('course-of-studies', 'Api\v2\Settings\CourseOfStudyController@store')->name('course_of_studies.store');
        Route::put('course-of-studies/{id}', 'Api\v2\Settings\CourseOfStudyController@update')->name('course_of_studies.update');
        Route::patch('course-of-studies/{id}/archive', 'Api\v2\Settings\CourseOfStudyController@archive')->name('course_of_studies.archive');
        Route::patch('course-of-studies/{id}/unarchive', 'Api\v2\Settings\CourseOfStudyController@unarchive')->name('course_of_studies.unarchive');
        Route::delete('course-of-studies/{id}', 'Api\v2\Settings\CourseOfStudyController@destroy')->name('course_of_studies.destroy');
    });
    // Certification Courses
    Route::get('certification-courses', 'Api\v2\Settings\CertificationCourseController@index')->name('certification_courses.index');
    Route::get('certification-courses/{id}', 'Api\v2\Settings\CertificationCourseController@show')->name('certification_courses.show');
    Route::middleware('auth:sanctum')->group( function () {
        Route::post('certification-courses', 'Api\v2\Settings\CertificationCourseController@store')->name('certification_courses.store');
        Route::put('certification-courses/{id}', 'Api\v2\Settings\CertificationCourseController@update')->name('certification_courses.update');
        Route::patch('certification-courses/{id}/archive', 'Api\v2\Settings\CertificationCourseController@archive')->name('certification_courses.archive');
        Route::patch('certification-courses/{id}/unarchive', 'Api\v2\Settings\CertificationCourseController@unarchive')->name('certification_courses.unarchive');
        Route::delete('certification-courses/{id}', 'Api\v2\Settings\CertificationCourseController@destroy')->name('certification_courses.destroy');
    });
    // Skills
    Route::get('competence', 'Api\v2\Settings\SkillController@index')->name('competence.index');
    Route::get('competence/{id}', 'Api\v2\Settings\SkillController@show')->name('competence.show');
    Route::middleware('auth:sanctum')->group( function () {
        Route::post('competence', 'Api\v2\Settings\CompetencyController@store')->name('competence.store');
        Route::post('competence/file', 'Api\v2\Settings\SkillController@uploadSkill')->name('competence.upload');
        Route::put('competence/{id}', 'Api\v2\Settings\SkillController@update')->name('competence.update');
        Route::patch('competence/{id}/archive', 'Api\v2\Settings\SkillController@archive')->name('competence.archive');
        Route::patch('competence/{id}/unarchive', 'Api\v2\Settings\SkillController@unarchive')->name('competence.unarchive');
        Route::delete('competence/{id}', 'Api\v2\Settings\SkillController@destroy')->name('competence.destroy');
    });

    Route::middleware('auth:sanctum')->group( function () {
        // Skills Levels
        Route::get('/competence/core/{domain}', 'Api\v2\Settings\SkillLevelsController@indexCore')->name('competence.core');
        Route::get('/competence/skill/{core}', 'Api\v2\Settings\SkillLevelsController@indexTertiary')->name('competence.skill');

        Route::post('/competence/levels/secondary', 'Api\v2\Settings\SkillLevelsController@storeSecondary')->name('competence.store.core');
        Route::post('/competence/levels/tertiary', 'Api\v2\Settings\SkillLevelsController@storeTertiary')->name('competence.store.skill');
        Route::put('competence/levels/secondary/{id}', 'Api\v2\Settings\SkillLevelsController@updateSecondary')->name('competence.update.core');
        Route::put('competence/levels/tertiary/{id}', 'Api\v2\Settings\SkillLevelsController@updateTertiary')->name('competence.update.skill');
        // Route::post('/competence/levels/secondary', 'Api\v2\Settings\SkillLevelsController@storeSecondary')->name('competence.store.core');
        // Route::patch('competence/{id}/archive', 'Api\v2\Settings\SkillController@archive')->name('competence.archive');
    });


    // Job Titles
    Route::get('job-titles', 'Api\v2\Settings\JobTitleController@index')->name('job_titles.index');
    Route::get('job-titles/{id}', 'Api\v2\Settings\JobTitleController@show')->name('job_titles.show');
    Route::middleware('auth:sanctum')->group( function () {
        Route::post('job-titles', 'Api\v2\Settings\JobTitleController@store')->name('job_titles.store');
        Route::put('job-titles/{id}', 'Api\v2\Settings\JobTitleController@update')->name('job_titles.update');
        Route::patch('job-titles/{id}/archive', 'Api\v2\Settings\JobTitleController@archive')->name('job_titles.archive');
        Route::patch('job-titles/{id}/unarchive', 'Api\v2\Settings\JobTitleController@unarchive')->name('job_titles.unarchive');
        Route::delete('job-titles/{id}', 'Api\v2\Settings\JobTitleController@destroy')->name('job_titles.destroy');
    });
    // Career Competenciees
    Route::resources([ 'carrer/competencies' => 'Api\v2\Admin\CareerController' ]);

    // Industries
    Route::get('industries', 'Api\v2\Settings\IndustryController@index')->name('industries.index');
    Route::get('industries/{id}', 'Api\v2\Settings\IndustryController@show')->name('industries.show');
    Route::middleware('auth:sanctum')->group( function () {
        Route::post('industries', 'Api\v2\Settings\IndustryController@store')->name('industries.store');
        Route::put('industries/{id}', 'Api\v2\Settings\IndustryController@update')->name('industries.update');
        Route::patch('industries/{id}/archive', 'Api\v2\Settings\IndustryController@archive')->name('industries.archive');
        Route::patch('industries/{id}/unarchive', 'Api\v2\Settings\IndustryController@unarchive')->name('industries.unarchive');
        Route::delete('industries/{id}', 'Api\v2\Settings\IndustryController@destroy')->name('industries.destroy');
    });
    // Degrees
    Route::get('degrees', 'Api\v2\Settings\DegreeController@index')->name('degrees.index');
    Route::get('degrees/{id}', 'Api\v2\Settings\DegreeController@show')->name('degrees.show');
    Route::middleware('auth:sanctum')->group( function () {
        Route::post('degrees', 'Api\v2\Settings\DegreeController@store')->name('degrees.store');
        Route::put('degrees/{id}', 'Api\v2\Settings\DegreeController@update')->name('degrees.update');
        Route::patch('degrees/{id}/archive', 'Api\v2\Settings\DegreeController@archive')->name('degrees.archive');
        Route::patch('degrees/{id}/unarchive', 'Api\v2\Settings\DegreeController@unarchive')->name('degrees.unarchive');
        Route::delete('degrees/{id}', 'Api\v2\Settings\DegreeController@destroy')->name('degrees.destroy');
    });
    // Language
    Route::get('languages', 'Api\v2\Settings\LanguageController@index')->name('languages.index');
    Route::get('languages/{id}', 'Api\v2\Settings\LanguageController@show')->name('languages.show');
    Route::middleware('auth:sanctum')->group( function () {
        Route::post('languages', 'Api\v2\Settings\LanguageController@store')->name('languages.store');
        Route::put('languages/{id}', 'Api\v2\Settings\LanguageController@update')->name('languages.update');
        Route::patch('languages/{id}/archive', 'Api\v2\Settings\LanguageController@archive')->name('languages.archive');
        Route::patch('languages/{id}/unarchive', 'Api\v2\Settings\LanguageController@unarchive')->name('languages.unarchive');
        Route::delete('languages/{id}', 'Api\v2\Settings\LanguageController@destroy')->name('languages.destroy');
    });
});
