<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - v1
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/



// Direct publicly accessible routes (No API Key)
Route::prefix('links')->middleware('web')->name('api.links.')->group( function () {
    // Verifications
    Route::prefix('verifications')->name('verifications.')->group( function () {
        Route::get('/verify-email/{token}', 'Api\v1\Link\VerificationLinkController@verifyEmail')->name('verify_email');
        Route::get('/verify-edit-email/{token}', 'Api\v1\Link\VerificationLinkController@verifyEditEmail')->name('verify_edit_email');
    });

    Route::get('cvs/{id}/{employer}/generate', 'Api\v1\CvController@generate_employer')->name('cvs.generate.employer');
    Route::get('cvs/{id}/{employer}/download', 'Api\v1\CvController@generate_employer')->name('cvs.download.employer');
    // Signed Routes
    Route::middleware('signed')->group( function () {
        // CVs
        Route::get('cvs/{id}/generate', 'Api\v1\CvController@generate')->name('cvs.generate');
        Route::get('cvs/{id}/download', 'Api\v1\CvController@generate')->name('cvs.download');

        Route::get('cvs/{id}/import-linkedin', 'Api\v1\Link\CvLinkController@importLinkedIn')->name('cvs.import_linkedin');
        Route::get('cvs/import-linkedin-callback', 'Api\v1\Link\CvLinkController@importLinkedIn')->name('cvs.import_linkedin_callback');

        // CV References
        Route::get('cv-references/{id}/questionnaire', 'Api\v1\Link\CvReferenceLinkController@questionnaireForm')->name('cv_references.questionnaire_form');
        Route::post('cv-references/{id}/questionnaire', 'Api\v1\Link\CvReferenceLinkController@storeQuestionnaireForm')->name('cv_references.questionnaire_form.store');
        Route::get('cv-references/{id}/questionnaire/successful', 'Api\v1\Link\CvReferenceLinkController@questionnaireFormSuccessful')->name('cv_references.questionnaire_form.successful');

        // Misc
        Route::get('image-cors-proxy', 'Api\v1\Public\ImageCorsProxyController@view')->name('image_cors_proxy');
    });

    // Unsigned Routes
    Route::get('cvs/import-linkedin-callback', 'Api\v1\Link\CvLinkController@importLinkedInCallback')->name('cvs.import_linkedin_callback');
});



// Below API routes secured with API Key
// Route::middleware('auth.apikey')->group( function () {

    Route::any('/', function (Request $request) {
        return response()->json([
            'status' => true,
            'message' => "V1  If you're not sure you know what you are doing, you probably shouldn't be using this API.",
            'data' => [
                'service' => 'croxxtalent-api',
                'version' => '1.0',
            ]
        ], 200);
    });
    // Route::post('users/photo', 'Api\v1\UserController@picture');

    // Route::get('/ok', 'Api\v1\AuthController@testAuth');
    // Auth API
    Route::prefix('auth')->name('api.')->group( function () {
        Route::get('/', 'Api\v1\AuthController@index')->name('auth.index');
        Route::post('login', 'Api\v1\AuthController@login')->name('auth.login');
        Route::post('register', 'Api\v1\AuthController@register')->name('auth.register');
        Route::middleware('auth:sanctum')->group( function () {
            Route::post('logout', 'Api\v1\AuthController@logout')->name('auth.logout');
            Route::post('refresh', 'Api\v1\AuthController@refresh')->name('auth.refresh');
            Route::get('user', 'Api\v1\AuthController@user')->name('auth.user');
        });
    });

    Route::post('users/send-password-verification', 'Api\v1\UserController@sendPasswordVerification')->name('users.send_password_verification');
    Route::post('users/reset-new-password', 'Api\v1\UserController@resetNewPassword')->name('users.reset_new_password');

    // Authenticated requests API
    Route::middleware('auth:sanctum')->name('api.')->group( function () {
        // Users
        Route::get('users', 'Api\v1\UserController@index')->name('users.index');
        Route::get('users/{id}', 'Api\v1\UserController@show')->name('users.show');
        Route::post('users', 'Api\v1\UserController@store')->name('users.store');
        Route::put('users/{id}', 'Api\v1\UserController@update')->name('users.update');
        Route::delete('users/{id}', 'Api\v1\UserController@destroy')->name('users.destroy');
        Route::post('users/delete-multiple', 'Api\v1\UserController@destroyMultiple')->name('users.destroy_multiple');
        Route::post('users/{id}/resend-verification', 'Api\v1\UserController@resendVerification')->name('users.resend_verification');
        Route::patch('users/{id}/password', 'Api\v1\UserController@password')->name('users.update_password');
        Route::post('users/{id}/photo', 'Api\v1\UserController@photo')->name('users.update_photo');

        Route::patch('users/{id}/activate', 'Api\v1\UserController@activate')->name('users.activate');
        Route::patch('users/{id}/archive', 'Api\v1\UserController@archive')->name('users.archive');
        Route::patch('users/{id}/unarchive', 'Api\v1\UserController@unarchive')->name('users.unarchive');
        Route::get('users/{id}/campaigns', 'Api\v1\UserController@campaigns')->name('users.campaigns');
        Route::get('users/{id}/affiliates', 'Api\v1\UserController@affiliates')->name('users.affiliates');
        Route::get('users/{id}/job-invitations', 'Api\v1\UserController@jobInvitations')->name('users.job_invitations');

        Route::get('users/{id}/notifications', 'Api\v1\UserController@notifications')->name('users.notifications');
        Route::get('notifications/seen/{id}', 'Api\v1\UserController@seenNotification')->name('users.notifications');

        Route::get('trending/employers', 'Api\v1\CroxxJobsController@trendingEmployers')->name('trending.employers');
        // Croxx Jobs
        Route::get('jobs/available', 'Api\v1\CroxxJobsController@available')->name('jobs.available');
        Route::get('jobs/available/{id}', 'Api\v1\CroxxJobsController@show')->name('jobs.show');
        Route::get('jobs/applied', 'Api\v1\CroxxJobsController@index')->name('jobs.index');
        Route::post('jobs/applied', 'Api\v1\CroxxJobsController@store')->name('jobs.applied');
        Route::post('jobs/unapplied/{id}', 'Api\v1\CroxxJobsController@unapplyJob')->name('jobs.unapplied');

        // CVs
        Route::get('cvs', 'Api\v1\CvController@index')->name('cvs.index');
        Route::get('cvs/{id}', 'Api\v1\CvController@show')->name('cvs.show');
        Route::post('cvs', 'Api\v1\CvController@store')->name('cvs.store');
        Route::put('cvs/{id}', 'Api\v1\CvController@update')->name('cvs.update');
        Route::post('cvs/{id}/photo', 'Api\v1\CvController@photo')->name('cvs.update_photo');
        Route::patch('cvs/{id}/publish', 'Api\v1\CvController@publish')->name('cvs.publish');
        Route::patch('cvs/{id}/unpublish', 'Api\v1\CvController@unpublish')->name('cvs.unpublish');
        Route::delete('cvs/{id}', 'Api\v1\CvController@destroy')->name('cvs.destroy');
        Route::post('cvs/delete-multiple', 'Api\v1\CvController@destroyMultiple')->name('cvs.destroy_multiple');

        // CV Work Experiences
        Route::prefix('cvs/{cv_id}/work-experiences')->name('cvs.work_experience.')->group( function () {
            Route::get('/', 'Api\v1\CvWorkExperienceController@index')->name('index');
            Route::get('/{cv_work_experience_id}', 'Api\v1\CvWorkExperienceController@show')->name('show');
            Route::post('/', 'Api\v1\CvWorkExperienceController@store')->name('store');
            Route::put('/{cv_work_experience_id}', 'Api\v1\CvWorkExperienceController@update')->name('update');
            Route::delete('/{cv_work_experience_id}', 'Api\v1\CvWorkExperienceController@destroy')->name('destroy');
        });
        // CV Educations
        Route::prefix('cvs/{cv_id}/educations')->name('cvs.educations.')->group( function () {
            Route::get('/', 'Api\v1\CvEducationController@index')->name('index');
            Route::get('/{cv_education_id}', 'Api\v1\CvEducationController@show')->name('show');
            Route::post('/', 'Api\v1\CvEducationController@store')->name('store');
            Route::put('/{cv_education_id}', 'Api\v1\CvEducationController@update')->name('update');
            Route::delete('/{cv_education_id}', 'Api\v1\CvEducationController@destroy')->name('destroy');
        });
        // CV Certifications
        Route::prefix('cvs/{cv_id}/certifications')->name('cvs.certifications.')->group( function () {
            Route::get('/', 'Api\v1\CvCertificationController@index')->name('index');
            Route::get('/{cv_certification_id}', 'Api\v1\CvCertificationController@show')->name('show');
            Route::post('/', 'Api\v1\CvCertificationController@store')->name('store');
            Route::put('/{cv_certification_id}', 'Api\v1\CvCertificationController@update')->name('update');
            Route::delete('/{cv_certification_id}', 'Api\v1\CvCertificationController@destroy')->name('destroy');
        });
        // CV Skills
        Route::prefix('cvs/{cv_id}/skills')->name('cvs.skills.')->group( function () {
            Route::get('/', 'Api\v1\CvSkillController@index')->name('index');
            Route::get('/{cv_skill_id}', 'Api\v1\CvSkillController@show')->name('show');
            Route::post('/', 'Api\v1\CvSkillController@store')->name('store');
            Route::put('/{cv_skill_id}', 'Api\v1\CvSkillController@update')->name('update');
            Route::delete('/{cv_skill_id}', 'Api\v1\CvSkillController@destroy')->name('destroy');
        });
        // CV Hobbies
        Route::prefix('cvs/{cv_id}/hobbies')->name('cvs.hobbies.')->group( function () {
            Route::get('/', 'Api\v1\CvHobbyController@index')->name('index');
            Route::get('/{cv_hobby_id}', 'Api\v1\CvHobbyController@show')->name('show');
            Route::post('/', 'Api\v1\CvHobbyController@store')->name('store');
            Route::put('/{cv_hobby_id}', 'Api\v1\CvHobbyController@update')->name('update');
            Route::delete('/{cv_hobby_id}', 'Api\v1\CvHobbyController@destroy')->name('destroy');
        });
        // CV Awards
        Route::prefix('cvs/{cv_id}/awards')->name('cvs.awards.')->group( function () {
            Route::get('/', 'Api\v1\CvAwardController@index')->name('index');
            Route::get('/{cv_award_id}', 'Api\v1\CvAwardController@show')->name('show');
            Route::post('/', 'Api\v1\CvAwardController@store')->name('store');
            Route::put('/{cv_award_id}', 'Api\v1\CvAwardController@update')->name('update');
            Route::delete('/{cv_award_id}', 'Api\v1\CvAwardController@destroy')->name('destroy');
        });
        // CV Languages
        Route::prefix('cvs/{cv_id}/languages')->name('cvs.languages.')->group( function () {
            Route::get('/', 'Api\v1\CvLanguageController@index')->name('index');
            Route::get('/{cv_language_id}', 'Api\v1\CvLanguageController@show')->name('show');
            Route::post('/', 'Api\v1\CvLanguageController@store')->name('store');
            Route::put('/{cv_language_id}', 'Api\v1\CvLanguageController@update')->name('update');
            Route::delete('/{cv_language_id}', 'Api\v1\CvLanguageController@destroy')->name('destroy');
        });
        // CV References
        Route::prefix('cvs/{cv_id}/references')->name('cvs.references.')->group( function () {
            Route::get('/', 'Api\v1\CvReferenceController@index')->name('index');
            Route::get('/{cv_reference_id}', 'Api\v1\CvReferenceController@show')->name('show');
            Route::post('/', 'Api\v1\CvReferenceController@store')->name('store');
            Route::put('/{cv_reference_id}', 'Api\v1\CvReferenceController@update')->name('update');
            Route::delete('/{cv_reference_id}', 'Api\v1\CvReferenceController@destroy')->name('destroy');
        });

        Route::get('job-invitations', 'Api\v1\JobInvitationController@index')->name('job_invitations.index');
        Route::get('job-invitations/{id}', 'Api\v1\JobInvitationController@show')->name('job_invitations.show');
        Route::post('job-invitations', 'Api\v1\JobInvitationController@store')->name('job_invitations.store');
        // Route::put('job-invitations/{id}', 'Api\v1\JobInvitationController@update')->name('job_invitations.update');
        Route::patch('job-invitations/{id}/accept', 'Api\v1\JobInvitationController@accept')->name('job_invitations.accept');
        Route::patch('job-invitations/{id}/reject', 'Api\v1\JobInvitationController@reject')->name('job_invitations.reject');
        Route::post('job-invitations/check', 'Api\v1\JobInvitationController@check')->name('job_invitations.check');
    });

    // Campaigns
    Route::get('campaigns', 'Api\v1\CampaignController@index')->name('campaigns.index');
    Route::get('campaigns/{id}', 'Api\v1\CampaignController@show')->name('campaigns.show');
    Route::middleware('auth:sanctum')->group( function () {
        Route::post('campaigns', 'Api\v1\CampaignController@store')->name('campaigns.store');
        Route::put('campaigns/{id}', 'Api\v1\CampaignController@update')->name('campaigns.update');
        // Route::post('campaigns/{id}/photo', 'Api\v1\CampaignController@photo')->name('campaigns.update_photo');
        Route::patch('campaigns/{id}/publish', 'Api\v1\CampaignController@publish')->name('campaigns.publish');
        Route::patch('campaigns/{id}/unpublish', 'Api\v1\CampaignController@unpublish')->name('campaigns.unpublish');;
        Route::delete('campaigns/{id}', 'Api\v1\CampaignController@destroy')->name('campaigns.destroy');
        Route::post('campaigns/delete-multiple', 'Api\v1\CampaignController@destroyMultiple')->name('campaigns.destroy_multiple');
    });

    // Settings API
    Route::prefix('settings')->name('api.settings.')->group( function () {
        // Timezones
        Route::get('timezones', 'Api\v1\Settings\TimezoneController@index')->name('languages.index');
        Route::get('timezones/{id}', 'Api\v1\Settings\TimezoneController@show')->name('languages.show');
        Route::middleware('auth:sanctum')->group( function () {
            Route::post('timezones', 'Api\v1\Settings\TimezoneController@store')->name('timezones.store');
            Route::put('timezones/{id}', 'Api\v1\Settings\TimezoneController@update')->name('timezones.update');
            Route::patch('timezones/{id}/archive', 'Api\v1\Settings\TimezoneController@archive')->name('timezones.archive');
            Route::patch('timezones/{id}/unarchive', 'Api\v1\Settings\TimezoneController@unarchive')->name('timezones.unarchive');
            Route::delete('timezones/{id}', 'Api\v1\Settings\TimezoneController@destroy')->name('timezones.destroy');
        });
        // Countries
        Route::get('countries', 'Api\v1\Settings\CountryController@index')->name('countries.index');
        Route::get('countries/{id}', 'Api\v1\Settings\CountryController@show')->name('countries.show');
        Route::middleware('auth:sanctum')->group( function () {
            Route::post('countries', 'Api\v1\Settings\CountryController@store')->name('countries.store');
            Route::put('countries/{id}', 'Api\v1\Settings\CountryController@update')->name('countries.update');
            Route::patch('countries/{id}/archive', 'Api\v1\Settings\CountryController@archive')->name('countries.archive');
            Route::patch('countries/{id}/unarchive', 'Api\v1\Settings\CountryController@unarchive')->name('countries.unarchive');
            Route::delete('countries/{id}', 'Api\v1\Settings\CountryController@destroy')->name('countries.destroy');
            Route::get('countries/{id}/states', 'Api\v1\Settings\CountryController@states')->name('countries.states');
        });
        // States
        Route::get('states', 'Api\v1\Settings\StateController@index')->name('states.index');
        Route::get('states/{id}', 'Api\v1\Settings\StateController@show')->name('states.show');
        Route::middleware('auth:sanctum')->group( function () {
            Route::post('states', 'Api\v1\Settings\StateController@store')->name('states.store');
            Route::put('states/{id}', 'Api\v1\Settings\StateController@update')->name('states.update');
            Route::patch('states/{id}/archive', 'Api\v1\Settings\StateController@archive')->name('states.archive');
            Route::patch('states/{id}/unarchive', 'Api\v1\Settings\StateController@unarchive')->name('states.unarchive');
            Route::delete('states/{id}', 'Api\v1\Settings\StateController@destroy')->name('states.destroy');
        });
        // Course Of Studies
        Route::get('course-of-studies', 'Api\v1\Settings\CourseOfStudyController@index')->name('course_of_studies.index');
        Route::get('course-of-studies/{id}', 'Api\v1\Settings\CourseOfStudyController@show')->name('course_of_studies.show');
        Route::middleware('auth:sanctum')->group( function () {
            Route::post('course-of-studies', 'Api\v1\Settings\CourseOfStudyController@store')->name('course_of_studies.store');
            Route::put('course-of-studies/{id}', 'Api\v1\Settings\CourseOfStudyController@update')->name('course_of_studies.update');
            Route::patch('course-of-studies/{id}/archive', 'Api\v1\Settings\CourseOfStudyController@archive')->name('course_of_studies.archive');
            Route::patch('course-of-studies/{id}/unarchive', 'Api\v1\Settings\CourseOfStudyController@unarchive')->name('course_of_studies.unarchive');
            Route::delete('course-of-studies/{id}', 'Api\v1\Settings\CourseOfStudyController@destroy')->name('course_of_studies.destroy');
        });
        // Certification Courses
        Route::get('certification-courses', 'Api\v1\Settings\CertificationCourseController@index')->name('certification_courses.index');
        Route::get('certification-courses/{id}', 'Api\v1\Settings\CertificationCourseController@show')->name('certification_courses.show');
        Route::middleware('auth:sanctum')->group( function () {
            Route::post('certification-courses', 'Api\v1\Settings\CertificationCourseController@store')->name('certification_courses.store');
            Route::put('certification-courses/{id}', 'Api\v1\Settings\CertificationCourseController@update')->name('certification_courses.update');
            Route::patch('certification-courses/{id}/archive', 'Api\v1\Settings\CertificationCourseController@archive')->name('certification_courses.archive');
            Route::patch('certification-courses/{id}/unarchive', 'Api\v1\Settings\CertificationCourseController@unarchive')->name('certification_courses.unarchive');
            Route::delete('certification-courses/{id}', 'Api\v1\Settings\CertificationCourseController@destroy')->name('certification_courses.destroy');
        });
        // Skills
        Route::get('skills', 'Api\v1\Settings\SkillController@index')->name('skills.index');
        Route::get('skills/{id}', 'Api\v1\Settings\SkillController@show')->name('skills.show');
        Route::middleware('auth:sanctum')->group( function () {
            Route::post('skills', 'Api\v1\Settings\SkillController@store')->name('skills.store');
            Route::post('skills/file', 'Api\v1\Settings\SkillController@uploadSkill')->name('skills.upload');
            Route::put('skills/{id}', 'Api\v1\Settings\SkillController@update')->name('skills.update');
            Route::patch('skills/{id}/archive', 'Api\v1\Settings\SkillController@archive')->name('skills.archive');
            Route::patch('skills/{id}/unarchive', 'Api\v1\Settings\SkillController@unarchive')->name('skills.unarchive');
            Route::delete('skills/{id}', 'Api\v1\Settings\SkillController@destroy')->name('skills.destroy');
        });
        Route::middleware('auth:sanctum')->group( function () {
            // Skills Levels
            Route::get('/skills/levels/tertiary/{secondary}', 'Api\v1\Settings\SkillLevelsController@indexTertiary')->name('skills.tertiary');
            Route::post('/skills/levels/secondary', 'Api\v1\Settings\SkillLevelsController@storeSecondary')->name('skills.store.secondary');
            Route::post('/skills/levels/tertiary', 'Api\v1\Settings\SkillLevelsController@storeTertiary')->name('skills.store.tertiary');
            Route::put('skills/levels/secondary/{id}', 'Api\v1\Settings\SkillLevelsController@updateSecondary')->name('skills.update.secondary');
            Route::put('skills/levels/tertiary/{id}', 'Api\v1\Settings\SkillLevelsController@updateTertiary')->name('skills.update.tertiary');
            // Route::post('/skills/levels/secondary', 'Api\v1\Settings\SkillLevelsController@storeSecondary')->name('skills.store.secondary');
            // Route::patch('skills/{id}/archive', 'Api\v1\Settings\SkillController@archive')->name('skills.archive');
        });


        // Job Titles
        Route::get('job-titles', 'Api\v1\Settings\JobTitleController@index')->name('job_titles.index');
        Route::get('job-titles/{id}', 'Api\v1\Settings\JobTitleController@show')->name('job_titles.show');
        Route::middleware('auth:sanctum')->group( function () {
            Route::post('job-titles', 'Api\v1\Settings\JobTitleController@store')->name('job_titles.store');
            Route::put('job-titles/{id}', 'Api\v1\Settings\JobTitleController@update')->name('job_titles.update');
            Route::patch('job-titles/{id}/archive', 'Api\v1\Settings\JobTitleController@archive')->name('job_titles.archive');
            Route::patch('job-titles/{id}/unarchive', 'Api\v1\Settings\JobTitleController@unarchive')->name('job_titles.unarchive');
            Route::delete('job-titles/{id}', 'Api\v1\Settings\JobTitleController@destroy')->name('job_titles.destroy');
        });
        // Industries
        Route::get('industries', 'Api\v1\Settings\IndustryController@index')->name('industries.index');
        Route::get('industries/{id}', 'Api\v1\Settings\IndustryController@show')->name('industries.show');
        Route::middleware('auth:sanctum')->group( function () {
            Route::post('industries', 'Api\v1\Settings\IndustryController@store')->name('industries.store');
            Route::put('industries/{id}', 'Api\v1\Settings\IndustryController@update')->name('industries.update');
            Route::patch('industries/{id}/archive', 'Api\v1\Settings\IndustryController@archive')->name('industries.archive');
            Route::patch('industries/{id}/unarchive', 'Api\v1\Settings\IndustryController@unarchive')->name('industries.unarchive');
            Route::delete('industries/{id}', 'Api\v1\Settings\IndustryController@destroy')->name('industries.destroy');
        });
        // Degrees
        Route::get('degrees', 'Api\v1\Settings\DegreeController@index')->name('degrees.index');
        Route::get('degrees/{id}', 'Api\v1\Settings\DegreeController@show')->name('degrees.show');
        Route::middleware('auth:sanctum')->group( function () {
            Route::post('degrees', 'Api\v1\Settings\DegreeController@store')->name('degrees.store');
            Route::put('degrees/{id}', 'Api\v1\Settings\DegreeController@update')->name('degrees.update');
            Route::patch('degrees/{id}/archive', 'Api\v1\Settings\DegreeController@archive')->name('degrees.archive');
            Route::patch('degrees/{id}/unarchive', 'Api\v1\Settings\DegreeController@unarchive')->name('degrees.unarchive');
            Route::delete('degrees/{id}', 'Api\v1\Settings\DegreeController@destroy')->name('degrees.destroy');
        });
        // Language
        Route::get('languages', 'Api\v1\Settings\LanguageController@index')->name('languages.index');
        Route::get('languages/{id}', 'Api\v1\Settings\LanguageController@show')->name('languages.show');
        Route::middleware('auth:sanctum')->group( function () {
            Route::post('languages', 'Api\v1\Settings\LanguageController@store')->name('languages.store');
            Route::put('languages/{id}', 'Api\v1\Settings\LanguageController@update')->name('languages.update');
            Route::patch('languages/{id}/archive', 'Api\v1\Settings\LanguageController@archive')->name('languages.archive');
            Route::patch('languages/{id}/unarchive', 'Api\v1\Settings\LanguageController@unarchive')->name('languages.unarchive');
            Route::delete('languages/{id}', 'Api\v1\Settings\LanguageController@destroy')->name('languages.destroy');
        });
    });


    // The fallback route should always be the last route registered by your application.
    Route::fallback(function () {
        return response()->json([
            'status' => false,
            'message' => "V1 Resource not found",
        ], 404);
    });
    // Nothing more, this is just route for direct access to the API domain


    // }); // end of Route::middleware('auth.apikey')...


