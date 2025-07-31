
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



// Career and CV
Route::middleware('auth:sanctum')->name('api.')->group( function () {
    // Resume
    Route::prefix('talent')->name('talent')->group( function () {
        Route::get('resume', 'Api\v2\Resume\TalentCVController@index')->name('resume.index');
        Route::post('resume', 'Api\v2\Resume\TalentCVController@storeInformation')->name('resume.store');
        Route::get('resume/files', 'Api\v2\Resume\TalentImportCVController@getCVFiles');
        Route::post('resume/import', 'Api\v2\Resume\TalentImportCVController@importResume')->name('resume.import');
        Route::post('resume/upload', 'Api\v2\Resume\TalentImportCVController@uploadCV')->name('resume.upload');
        Route::put('/resume/{uploadId}/primary', 'Api\v2\Resume\TalentImportCVController@setPrimaryCv');
        Route::delete('/resumes/{uploadId}', 'Api\v2\Resume\TalentImportCVController@deleteCVFile');
        Route::get('/resumes/{uploadId}/download', 'Api\v2\Resume\TalentImportCVController@downloadCV');

        Route::post('resume/contact', 'Api\v2\Resume\TalentCVController@storeContact')->name('resume.contact');
        Route::post('resume/photo', 'Api\v2\Resume\TalentCVController@photo')->name('resume.update_photo');
        Route::patch('resume/publish', 'Api\v2\Resume\TalentCVController@publish')->name('resume.publish');
        Route::patch('resume/unpublish', 'Api\v2\Resume\TalentCVController@unpublish')->name('resume.unpublish');
        // CV Work Experiences
        Route::prefix('resume/work-experiences')->name('resume.work_experience.')->group( function () {
            Route::get('/', 'Api\v2\Resume\CvWorkExperienceController@index')->name('index');
            Route::post('/', 'Api\v2\Resume\CvWorkExperienceController@store')->name('store');
            Route::get('/{cv_work_experience_id}', 'Api\v2\Resume\CvWorkExperienceController@show')->name('show');
            Route::put('/{cv_work_experience_id}', 'Api\v2\Resume\CvWorkExperienceController@update')->name('update');
            Route::delete('/{cv_work_experience_id}', 'Api\v2\Resume\CvWorkExperienceController@destroy')->name('destroy');
        });
        // CV Educations
        Route::prefix('resume/educations')->name('resume.educations.')->group( function () {
            Route::get('/', 'Api\v2\Resume\CvEducationController@index')->name('index');
            Route::get('/{cv_education_id}', 'Api\v2\Resume\CvEducationController@show')->name('show');
            Route::post('/', 'Api\v2\Resume\CvEducationController@store')->name('store');
            Route::put('/{cv_education_id}', 'Api\v2\Resume\CvEducationController@update')->name('update');
            Route::delete('/{cv_education_id}', 'Api\v2\Resume\CvEducationController@destroy')->name('destroy');
        });
        // CV Certifications
        Route::prefix('resume/certifications')->name('resume.certifications.')->group( function () {
            Route::get('/', 'Api\v2\Resume\CvCertificationController@index')->name('index');
            Route::get('/{cv_certification_id}', 'Api\v2\Resume\CvCertificationController@show')->name('show');
            Route::post('/', 'Api\v2\Resume\CvCertificationController@store')->name('store');
            Route::put('/{cv_certification_id}', 'Api\v2\Resume\CvCertificationController@update')->name('update');
            Route::delete('/{cv_certification_id}', 'Api\v2\Resume\CvCertificationController@destroy')->name('destroy');
        });
        // CV Competency Skills
        Route::prefix('resume/competence')->name('resume.competence.')->group( function () {
            Route::get('/', 'Api\v2\Resume\CvCompetencyController@index')->name('index');
            Route::post('/', 'Api\v2\Resume\CvCompetencyController@store')->name('store');
            // Route::get('/{cv_skill_id}', 'Api\v2\Resume\CvCompetencyController@show')->name('show');
            // Route::put('/{cv_skill_id}', 'Api\v2\Resume\CvCompetencyController@update')->name('update');
            // Route::delete('/{cv_skill_id}', 'Api\v2\Resume\CvCompetencyController@destroy')->name('destroy');
        });
        // CV Hobbies
        Route::prefix('resume/hobbies')->name('resume.hobbies.')->group( function () {
            Route::get('/', 'Api\v2\Resume\CvHobbyController@index')->name('index');
            Route::get('/{cv_hobby_id}', 'Api\v2\Resume\CvHobbyController@show')->name('show');
            Route::post('/', 'Api\v2\Resume\CvHobbyController@store')->name('store');
            Route::put('/{cv_hobby_id}', 'Api\v2\Resume\CvHobbyController@update')->name('update');
            Route::delete('/{cv_hobby_id}', 'Api\v2\Resume\CvHobbyController@destroy')->name('destroy');
        });
        // CV Awards
        Route::prefix('resume/awards')->name('resume.awards.')->group( function () {
            Route::get('/', 'Api\v2\Resume\CvAwardController@index')->name('index');
            Route::get('/{cv_award_id}', 'Api\v2\Resume\CvAwardController@show')->name('show');
            Route::post('/', 'Api\v2\Resume\CvAwardController@store')->name('store');
            Route::put('/{cv_award_id}', 'Api\v2\Resume\CvAwardController@update')->name('update');
            Route::delete('/{cv_award_id}', 'Api\v2\Resume\CvAwardController@destroy')->name('destroy');
        });
        // CV Languages
        Route::prefix('resume/languages')->name('resume.languages.')->group( function () {
            Route::get('/', 'Api\v2\Resume\CvLanguageController@index')->name('index');
            Route::get('/{cv_language_id}', 'Api\v2\Resume\CvLanguageController@show')->name('show');
            Route::post('/', 'Api\v2\Resume\CvLanguageController@store')->name('store');
            Route::put('/{cv_language_id}', 'Api\v2\Resume\CvLanguageController@update')->name('update');
            Route::delete('/{cv_language_id}', 'Api\v2\Resume\CvLanguageController@destroy')->name('destroy');
        });
        // CV References
        Route::prefix('resume/references')->name('resume.references.')->group( function () {
            Route::get('/', 'Api\v2\Resume\CvReferenceController@index')->name('index');
            Route::get('/{cv_reference_id}', 'Api\v2\Resume\CvReferenceController@show')->name('show');
            Route::post('/', 'Api\v2\Resume\CvReferenceController@store')->name('store');
            Route::put('/{cv_reference_id}', 'Api\v2\Resume\CvReferenceController@update')->name('update');
            Route::delete('/{cv_reference_id}', 'Api\v2\Resume\CvReferenceController@destroy')->name('destroy');
        });
    });

    // CVs
    Route::get('cvs', 'Api\v2\CvController@index')->name('cvs.index');
    Route::get('cvs/{id}', 'Api\v2\CvController@show')->name('cvs.show');
    // Route::put('cvs/{id}', 'Api\v2\CvController@update')->name('cvs.update');
    // Route::post('cvs', 'Api\v2\CvController@store')->name('cvs.store');
    // Route::post('cvs/{id}/photo', 'Api\v2\CvController@photo')->name('cvs.update_photo');
    // Route::patch('cvs/{id}/publish', 'Api\v2\CvController@publish')->name('cvs.publish');
    // Route::patch('cvs/{id}/unpublish', 'Api\v2\CvController@unpublish')->name('cvs.unpublish');
    Route::delete('cvs/{id}', 'Api\v2\CvController@destroy')->name('cvs.destroy');
    Route::post('cvs/delete-multiple', 'Api\v2\CvController@destroyMultiple')->name('cvs.destroy_multiple');

});

