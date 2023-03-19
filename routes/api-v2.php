<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - v2
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/



// Direct publicly accessible routes (No API Key)

Route::any('/', function (Request $request) {
    return response()->json([
        'status' => true,
        'message' => "V2  If you're not sure you know what you are doing, you probably shouldn't be using this API.",
        'data' => [
            'service' => 'croxxtalent-api',
            'version' => '2.0',
        ]
    ], 200);
});


Route::prefix('croxtec')->middleware('web')->name('api.croxtec.')->group( function () {
    Route::post('/contact', 'Api\v2\GeneralController@contact')->name('contact');
    Route::post('/newsletter', 'Api\v2\GeneralController@newsletter')->name('newsletter');
});

Route::prefix('auth')->name('api.')->group( function () {
    Route::get('/', 'Api\v2\AuthController@index')->name('auth.index');
    Route::post('login', 'Api\v2\AuthController@login')->name('auth.login');
    Route::post('register', 'Api\v2\AuthController@register')->name('auth.register');
    Route::middleware('auth:sanctum')->group( function () {
        Route::post('logout', 'Api\v2\AuthController@logout')->name('auth.logout');
        Route::post('refresh', 'Api\v2\AuthController@refresh')->name('auth.refresh');
        Route::get('user', 'Api\v2\AuthController@user')->name('auth.user');
    });

    Route::post('forgot-passwword', 'Api\v2\AuthController@sendPasswordVerification')->name('users.send_password_verification');
    Route::post('reset-password', 'Api\v2\AuthController@resetNewPassword')->name('users.reset_new_password');
});


Route::prefix('links')->middleware('web')->name('api.links.')->group( function () {
    // Verifications
    Route::prefix('verifications')->name('verifications.')->group( function () {
        Route::get('/verify-email/{token}', 'Api\v2\Link\VerificationLinkController@verifyEmail')->name('verify_email');
        Route::get('/verify-edit-email/{token}', 'Api\v2\Link\VerificationLinkController@verifyEditEmail')->name('verify_edit_email');
    });

    Route::get('cvs/{id}/{employer}/generate', 'Api\v2\CvController@generate_employer')->name('cvs.generate.employer');
    Route::get('cvs/{id}/{employer}/download', 'Api\v2\CvController@generate_employer')->name('cvs.download.employer');

    // Signed Routes
    Route::middleware('signed')->group( function () {
        // CVs
        Route::get('cvs/{id}/generate', 'Api\v2\CvController@generate')->name('cvs.generate');
        Route::get('cvs/{id}/download', 'Api\v2\CvController@generate')->name('cvs.download');

        Route::get('cvs/{id}/import-linkedin', 'Api\v2\Link\CvLinkController@importLinkedIn')->name('cvs.import_linkedin');
        Route::get('cvs/import-linkedin-callback', 'Api\v2\Link\CvLinkController@importLinkedIn')->name('cvs.import_linkedin_callback');

        // CV References
        Route::get('cv-references/{id}/questionnaire', 'Api\v2\Link\CvReferenceLinkController@questionnaireForm')->name('cv_references.questionnaire_form');
        Route::post('cv-references/{id}/questionnaire', 'Api\v2\Link\CvReferenceLinkController@storeQuestionnaireForm')->name('cv_references.questionnaire_form.store');
        Route::get('cv-references/{id}/questionnaire/successful', 'Api\v2\Link\CvReferenceLinkController@questionnaireFormSuccessful')->name('cv_references.questionnaire_form.successful');

        // Misc
        Route::get('image-cors-proxy', 'Api\v2\Link\ImageCorsProxyController@view')->name('image_cors_proxy');
    });

    // Unsigned Routes
    Route::get('cvs/import-linkedin-callback', 'Api\v2\Link\CvLinkController@importLinkedInCallback')->name('cvs.import_linkedin_callback');
});


// Below API routes secured with API Key
// Route::middleware('auth.apikey')->group( function () {

    // Authenticated requests API
    Route::middleware('auth:sanctum')->name('api.')->group( function () {

        Route::prefix('talent')->name('cvs.work_experience.')->group( function () {
            // Resume
            Route::get('resume', 'Api\v2\TalentCVController@index')->name('resume.index');
            Route::post('resume', 'Api\v2\TalentCvController@store')->name('resume.store');
            Route::post('resume/contact', 'Api\v2\TalentCvController@contact')->name('resume.contact');
            Route::post('resume/photo', 'Api\v2\TalentCvController@photo')->name('resume.update_photo');
            Route::patch('resume/publish', 'Api\v2\TalentCvController@publish')->name('resume.publish');
            Route::patch('resume/unpublish', 'Api\v2\TalentCvController@unpublish')->name('resume.unpublish');
            // CV Work Experiences
            Route::prefix('resume/work-experiences')->name('resume.work_experience.')->group( function () {
                Route::get('/', 'Api\v2\CvWorkExperienceController@index')->name('index');
                Route::post('/', 'Api\v2\CvWorkExperienceController@store')->name('store');
                Route::get('/{cv_work_experience_id}', 'Api\v2\CvWorkExperienceController@show')->name('show');
                Route::put('/{cv_work_experience_id}', 'Api\v2\CvWorkExperienceController@update')->name('update');
                Route::delete('/{cv_work_experience_id}', 'Api\v2\CvWorkExperienceController@destroy')->name('destroy');
            });

            // CV Educations
            Route::prefix('resume/educations')->name('resume.educations.')->group( function () {
                Route::get('/', 'Api\v2\CvEducationController@index')->name('index');
                Route::get('/{cv_education_id}', 'Api\v2\CvEducationController@show')->name('show');
                Route::post('/', 'Api\v2\CvEducationController@store')->name('store');
                Route::put('/{cv_education_id}', 'Api\v2\CvEducationController@update')->name('update');
                Route::delete('/{cv_education_id}', 'Api\v2\CvEducationController@destroy')->name('destroy');
            });
            // CV Certifications
            Route::prefix('resume/certifications')->name('resume.certifications.')->group( function () {
                Route::get('/', 'Api\v2\CvCertificationController@index')->name('index');
                Route::get('/{cv_certification_id}', 'Api\v2\CvCertificationController@show')->name('show');
                Route::post('/', 'Api\v2\CvCertificationController@store')->name('store');
                Route::put('/{cv_certification_id}', 'Api\v2\CvCertificationController@update')->name('update');
                Route::delete('/{cv_certification_id}', 'Api\v2\CvCertificationController@destroy')->name('destroy');
            });
            // CV Skills
            Route::prefix('resume/skills')->name('resume.skills.')->group( function () {
                Route::get('/', 'Api\v2\CvSkillController@index')->name('index');
                Route::get('/{cv_skill_id}', 'Api\v2\CvSkillController@show')->name('show');
                Route::post('/', 'Api\v2\CvSkillController@store')->name('store');
                Route::put('/{cv_skill_id}', 'Api\v2\CvSkillController@update')->name('update');
                Route::delete('/{cv_skill_id}', 'Api\v2\CvSkillController@destroy')->name('destroy');
            });
            // CV Hobbies
            Route::prefix('resume/hobbies')->name('resume.hobbies.')->group( function () {
                Route::get('/', 'Api\v2\CvHobbyController@index')->name('index');
                Route::get('/{cv_hobby_id}', 'Api\v2\CvHobbyController@show')->name('show');
                Route::post('/', 'Api\v2\CvHobbyController@store')->name('store');
                Route::put('/{cv_hobby_id}', 'Api\v2\CvHobbyController@update')->name('update');
                Route::delete('/{cv_hobby_id}', 'Api\v2\CvHobbyController@destroy')->name('destroy');
            });
            // CV Awards
            Route::prefix('resume/awards')->name('resume.awards.')->group( function () {
                Route::get('/', 'Api\v2\CvAwardController@index')->name('index');
                Route::get('/{cv_award_id}', 'Api\v2\CvAwardController@show')->name('show');
                Route::post('/', 'Api\v2\CvAwardController@store')->name('store');
                Route::put('/{cv_award_id}', 'Api\v2\CvAwardController@update')->name('update');
                Route::delete('/{cv_award_id}', 'Api\v2\CvAwardController@destroy')->name('destroy');
            });
            // CV Languages
            Route::prefix('resume/languages')->name('resume.languages.')->group( function () {
                Route::get('/', 'Api\v2\CvLanguageController@index')->name('index');
                Route::get('/{cv_language_id}', 'Api\v2\CvLanguageController@show')->name('show');
                Route::post('/', 'Api\v2\CvLanguageController@store')->name('store');
                Route::put('/{cv_language_id}', 'Api\v2\CvLanguageController@update')->name('update');
                Route::delete('/{cv_language_id}', 'Api\v2\CvLanguageController@destroy')->name('destroy');
            });
            // CV References
            Route::prefix('resume/references')->name('resume.references.')->group( function () {
                Route::get('/', 'Api\v2\CvReferenceController@index')->name('index');
                Route::get('/{cv_reference_id}', 'Api\v2\CvReferenceController@show')->name('show');
                Route::post('/', 'Api\v2\CvReferenceController@store')->name('store');
                Route::put('/{cv_reference_id}', 'Api\v2\CvReferenceController@update')->name('update');
                Route::delete('/{cv_reference_id}', 'Api\v2\CvReferenceController@destroy')->name('destroy');
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
        // Users
        Route::get('users', 'Api\v2\UserController@index')->name('users.index');
        Route::get('users/{id}', 'Api\v2\UserController@show')->name('users.show');
        Route::post('users', 'Api\v2\UserController@store')->name('users.store');
        Route::put('users/{id}', 'Api\v2\UserController@update')->name('users.update');
        Route::delete('users/{id}', 'Api\v2\UserController@destroy')->name('users.destroy');
        Route::post('users/delete-multiple', 'Api\v2\UserController@destroyMultiple')->name('users.destroy_multiple');
        Route::post('users/{id}/resend-verification', 'Api\v2\UserController@resendVerification')->name('users.resend_verification');
        Route::patch('users/{id}/password', 'Api\v2\UserController@password')->name('users.update_password');
        Route::post('users/{id}/photo', 'Api\v2\UserController@photo')->name('users.update_photo');

        Route::patch('users/{id}/activate', 'Api\v2\UserController@activate')->name('users.activate');
        Route::patch('users/{id}/archive', 'Api\v2\UserController@archive')->name('users.archive');
        Route::patch('users/{id}/unarchive', 'Api\v2\UserController@unarchive')->name('users.unarchive');
        Route::get('users/{id}/campaigns', 'Api\v2\UserController@campaigns')->name('users.campaigns');
        Route::get('users/{id}/affiliates', 'Api\v2\UserController@affiliates')->name('users.affiliates');
        Route::get('users/{id}/job-invitations', 'Api\v2\UserController@jobInvitations')->name('users.job_invitations');

        Route::get('users/{id}/notifications', 'Api\v2\UserController@notifications')->name('users.notifications');
        Route::get('notifications/seen/{id}', 'Api\v2\UserController@seenNotification')->name('users.notifications');

        Route::get('trending/employers', 'Api\v2\CroxxJobsController@trendingEmployers')->name('trending.employers');
        // Croxx Jobs
        Route::get('jobs/available', 'Api\v2\CroxxJobsController@available')->name('jobs.available');
        Route::get('jobs/available/{id}', 'Api\v2\CroxxJobsController@show')->name('jobs.show');
        Route::get('jobs/applied', 'Api\v2\CroxxJobsController@index')->name('jobs.index');
        Route::post('jobs/applied', 'Api\v2\CroxxJobsController@store')->name('jobs.applied');
        Route::post('jobs/unapplied/{id}', 'Api\v2\CroxxJobsController@unapplyJob')->name('jobs.unapplied');


        Route::get('job-invitations', 'Api\v2\JobInvitationController@index')->name('job_invitations.index');
        Route::get('job-invitations/{id}', 'Api\v2\JobInvitationController@show')->name('job_invitations.show');
        Route::post('job-invitations', 'Api\v2\JobInvitationController@store')->name('job_invitations.store');
        // Route::put('job-invitations/{id}', 'Api\v2\JobInvitationController@update')->name('job_invitations.update');
        Route::patch('job-invitations/{id}/accept', 'Api\v2\JobInvitationController@accept')->name('job_invitations.accept');
        Route::patch('job-invitations/{id}/reject', 'Api\v2\JobInvitationController@reject')->name('job_invitations.reject');
        Route::post('job-invitations/check', 'Api\v2\JobInvitationController@check')->name('job_invitations.check');
    });

    // Campaigns
    Route::get('campaigns', 'Api\v2\CampaignController@index')->name('campaigns.index');
    Route::get('campaigns/{id}', 'Api\v2\CampaignController@show')->name('campaigns.show');
    Route::middleware('auth:sanctum')->group( function () {
        Route::post('campaigns', 'Api\v2\CampaignController@store')->name('campaigns.store');
        Route::put('campaigns/{id}', 'Api\v2\CampaignController@update')->name('campaigns.update');
        // Route::post('campaigns/{id}/photo', 'Api\v2\CampaignController@photo')->name('campaigns.update_photo');
        Route::patch('campaigns/{id}/publish', 'Api\v2\CampaignController@publish')->name('campaigns.publish');
        Route::patch('campaigns/{id}/unpublish', 'Api\v2\CampaignController@unpublish')->name('campaigns.unpublish');;
        Route::delete('campaigns/{id}', 'Api\v2\CampaignController@destroy')->name('campaigns.destroy');
        Route::post('campaigns/delete-multiple', 'Api\v2\CampaignController@destroyMultiple')->name('campaigns.destroy_multiple');
    });

    // Settings API
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
        Route::get('skills', 'Api\v2\Settings\SkillController@index')->name('skills.index');
        Route::get('skills/{id}', 'Api\v2\Settings\SkillController@show')->name('skills.show');
        Route::middleware('auth:sanctum')->group( function () {
            Route::post('skills', 'Api\v2\Settings\SkillController@store')->name('skills.store');
            Route::post('skills/file', 'Api\v2\Settings\SkillController@uploadSkill')->name('skills.upload');
            Route::put('skills/{id}', 'Api\v2\Settings\SkillController@update')->name('skills.update');
            Route::patch('skills/{id}/archive', 'Api\v2\Settings\SkillController@archive')->name('skills.archive');
            Route::patch('skills/{id}/unarchive', 'Api\v2\Settings\SkillController@unarchive')->name('skills.unarchive');
            Route::delete('skills/{id}', 'Api\v2\Settings\SkillController@destroy')->name('skills.destroy');
        });
        Route::middleware('auth:sanctum')->group( function () {
            // Skills Levels
            Route::get('/skills/levels/tertiary/{secondary}', 'Api\v2\Settings\SkillLevelsController@indexTertiary')->name('skills.tertiary');
            Route::post('/skills/levels/secondary', 'Api\v2\Settings\SkillLevelsController@storeSecondary')->name('skills.store.secondary');
            Route::post('/skills/levels/tertiary', 'Api\v2\Settings\SkillLevelsController@storeTertiary')->name('skills.store.tertiary');
            Route::put('skills/levels/secondary/{id}', 'Api\v2\Settings\SkillLevelsController@updateSecondary')->name('skills.update.secondary');
            Route::put('skills/levels/tertiary/{id}', 'Api\v2\Settings\SkillLevelsController@updateTertiary')->name('skills.update.tertiary');
            // Route::post('/skills/levels/secondary', 'Api\v2\Settings\SkillLevelsController@storeSecondary')->name('skills.store.secondary');
            // Route::patch('skills/{id}/archive', 'Api\v2\Settings\SkillController@archive')->name('skills.archive');
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


    // The fallback route should always be the last route registered by your application.
    Route::fallback(function () {
        return response()->json([
            'status' => false,
            'message' => "V2 Resource not found",
        ], 404);
    });
    // Nothing more, this is just route for direct access to the API domain


    // }); // end of Route::middleware('auth.apikey')...


