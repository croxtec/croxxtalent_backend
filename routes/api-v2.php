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
    Route::post('company/login', 'Api\v2\AuthController@companyLogin')->name('company.login');
    Route::post('register', 'Api\v2\AuthController@register')->name('auth.register');
    Route::middleware('auth:sanctum')->group( function () {
        Route::post('logout', 'Api\v2\AuthController@logout')->name('auth.logout');
        Route::post('refresh', 'Api\v2\AuthController@refresh')->name('auth.refresh');
        Route::get('user', 'Api\v2\AuthController@user')->name('auth.user');
    });

    Route::get('confirm-code', 'Api\v2\AuthController@confirmResetCode')->name('users.confirm_reset_code');
    Route::post('forgot-passwword', 'Api\v2\AuthController@sendPasswordVerification')->name('users.send_password_verification');
    Route::post('reset-password', 'Api\v2\AuthController@resetNewPassword')->name('users.reset_new_password');
});


Route::prefix('links')->middleware('web')->name('api.links.')->group( function () {
    // Verifications
    Route::prefix('verifications')->name('verifications.')->group( function () {
        Route::get('/verify-email/{token}', 'Api\v2\Link\VerificationLinkController@verifyEmail')->name('verify_email');
        Route::get('/verify-employee/{token}', 'Api\v2\Link\VerificationLinkController@verifyEmployee')->name('verify_employee');
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
        Route::prefix('talent')->name('talent')->group( function () {
            Route::get('company', 'Api\v2\Talent\TalentCompanyController@index')->name('company.employee');
            Route::get('company/supervisor', 'Api\v2\Talent\TalentCompanyController@supervisor')->name('company.supervisor');
            Route::get('company/employee/{id}', 'Api\v2\Talent\TalentCompanyController@employeeInformation')->name('company.employee');
            Route::get('company/team/performance', 'Api\v2\Talent\TalentCompanyController@teamPerformanceProgress')->name('company.performance');
            // Competence
            Route::get('career/suggestion', 'Api\v2\Talent\TalentCompetencyController@suggestion')->name('competence.suggestion');
            Route::get('competence', 'Api\v2\Talent\TalentCompetencyController@index')->name('competence.index');
            // Old
            // Route::get('competence/skill', 'Api\v2\Talent\TalentCompetencyController@skill')->name('competence.skill');
            // Route::get('competence/experience', 'Api\v2\Talent\TalentCompetencyController@experience')->name('competence.experience');
            // Route::get('competence/manager', 'Api\v2\Talent\TalentCompetencyController@manager')->name('competence.manager');
            // Resume
            Route::get('resume', 'Api\v2\Resume\TalentCVController@index')->name('resume.index');
            Route::post('resume', 'Api\v2\Resume\TalentCVController@storeInformation')->name('resume.store');
            Route::post('resume/import', 'Api\v2\Resume\TalentCVController@importResume')->name('resume.import');

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
        // Training & Learning Path
        Route::get('trainings/employee/{code}', 'Api\v2\Learning\TrainingHubController@employee');//->name('assesments.index');
        Route::get('trainings/paths', 'Api\v2\Learning\TrainingHubController@paths');//->name('assesments.index');

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
    });

    // Croxx Jobs
    Route::middleware('auth:sanctum')->name('api.')->group( function () {
        Route::get('jobs/recommendations', 'Api\v2\CroxxJobsController@recommendations')->name('jobs.recommendations');
        Route::post('jobs/applied', 'Api\v2\CroxxJobsController@apply')->name('jobs.apply');
        Route::post('jobs/saved', 'Api\v2\CroxxJobsController@saved')->name('jobs.saved');
        Route::get('top/employers', 'Api\v2\CroxxJobsController@topEmployers')->name('jobs.employerss');

        Route::get('myjob/applied', 'Api\v2\MyJobController@indexApplied')->name('myjob.applied');
        Route::post('myjob/unapplied/{id}', 'Api\v2\MyJobController@unapplyJob')->name('myjob.unapplied');
        Route::get('myjob/saved', 'Api\v2\MyJobController@indexSaved')->name('myjob.saved');
        Route::delete('myjob/saved/{id}', 'Api\v2\MyJobController@destroySaved')->name('myjob.delete.saved');

        Route::get('job-invitations', 'Api\v2\JobInvitationController@index')->name('job_invitations.index');
        Route::get('job-invitations/{id}', 'Api\v2\JobInvitationController@show')->name('job_invitations.show');
        Route::patch('job-invitations/{id}/accept', 'Api\v2\JobInvitationController@accept')->name('job_invitations.accept');
        Route::patch('job-invitations/{id}/reject', 'Api\v2\JobInvitationController@reject')->name('job_invitations.reject');
        // Route::put('job-invitations/{id}', 'Api\v2\JobInvitationController@update')->name('job_invitations.update');

        Route::get('candidate/{id}', 'Api\v2\CandidateController@index')->name('candidate.index');
        // Route::post('candidate/check', 'Api\v2\CandidateController@check')->name('job_invitations.check');
        // Route::get('candidate/invited', 'Api\v2\CandidateController@index')->name('candidate.index');
        Route::post('candidate/{id}/rating', 'Api\v2\CandidateController@rateCandidate')->name('candidate.rating');
        Route::post('candidate/{id}/invite', 'Api\v2\CandidateController@invite')->name('candidate.invite');
        Route::post('candidate/{id}/withdraw', 'Api\v2\CandidateController@withdraw')->name('candidate.withdraw');
        // Route::post('candidate/{id}/result', 'Api\v2\CandidateController@result')->name('candidate.result');
    });
    Route::get('jobs', 'Api\v2\CroxxJobsController@index')->name('jobs.index');
    Route::get('jobs/{id}', 'Api\v2\CroxxJobsController@show')->name('jobs.show');

    // Company
    Route::middleware('auth:sanctum')->prefix('employers')->name('employers.')->group( function () {
        Route::post('employee/import', 'Api\v2\Company\EmployeeController@importEmployees')->name('employee.import');
        Route::resources([
            'employee' => 'Api\v2\Company\EmployeeController',
            'supervisor' => 'Api\v2\Company\SupervisorController',
            'department' => 'Api\v2\Company\DepartmentController'
        ]);
        Route::get('competency/mapping', 'Api\v2\EmployerCompetencyController@index')->name('competency.index');
        Route::post('competency/mapping/{id}', 'Api\v2\EmployerCompetencyController@storeCompetency')->name('competency.store');
        Route::get('onboarding/welcome', 'Api\v2\EmployerCompetencyController@confirmWelcome')->name('confirm.welcome');

        // Route::get('competency/gap', 'Api\v2\EmployerCompetencyController@competency')->name('competency.skill');
        // Route::patch('employee/{id}/archive', 'Api\v2\EmployeeController@archive')->name('employee.archive');
        // Route::patch('employee/{id}/unarchive', 'Api\v2\EmployeeController@unarchive')->name('employee.unarchive');
    });

    Route::middleware('auth:sanctum')->group( function () {
        Route::resources([
            'campaigns' => 'Api\v2\CampaignController',
            'goals' => 'Api\v2\GoalController',
            'assessments/evaluation' => 'Api\v2\Operations\EvaluationAssessmentController',
            'assessments' => 'Api\v2\Operations\ExperienceAssessmentController',
            'courses' => 'Api\v2\Learning\CourseController',
            'lessons' => 'Api\v2\Learning\LessonController',
        ]);
        Route::get('goals/employee/{code}', 'Api\v2\GoalController@employee');//->name('assesments.index');
        Route::get('goals/overview/performance', 'Api\v2\GoalController@overview')->name('goals.overview');
        Route::get('goals/overview/calendar', 'Api\v2\GoalController@calendarOverview')->name('goals.overview.calendar');
        Route::patch('goals/{id}/archive', 'Api\v2\GoalController@archive')->name('goals.archive');
        Route::patch('goals/{id}/unarchive', 'Api\v2\GoalController@unarchive')->name('goals.unarchive');
        // Course
        Route::patch('courses/{id}/publish', 'Api\v2\Learning\CourseController@publish')->name('courses.publish');
        // Assesment Options
        // Route::patch('assessments/{id}/unpublish', 'Api\v2\AssesmentController@unpublish')->name('assessments.unpublish');
        Route::patch('assessments/{id}/publish', 'Api\v2\Operations\ExperienceAssessmentController@publish')->name('assessments.publish');
        Route::patch('assessments/{id}/archive', 'Api\v2\AssesmentController@archive')->name('assessments.archive');
        Route::patch('assessments/{id}/unarchive', 'Api\v2\AssesmentController@unarchive')->name('assessments.unarchive');
        // Employee Assessment
        Route::get('assessments/employee/{code}', 'Api\v2\Operations\EmployeeAssessmentController@employee');//->name('assesments.index');
        Route::get('assessments/feedbacks/{code}', 'Api\v2\Operations\EmployeeAssessmentController@feedbacks');//->name('assesments.index');
        Route::post('assessments/talent/answer', 'Api\v2\Operations\EmployeeAssessmentController@storeTalentAnswer');//->name('assessments.index');
        Route::patch('assessments/{id}/talent/publish', 'Api\v2\Operations\EmployeeAssessmentController@publishTalentAnswers');//->name('assessments.index');
        // Manage Assesment
        Route::get('assessments/{id}/assigned/employees', 'Api\v2\ScoresheetController@employeeList');//->name('assessments.index');
        Route::get('assessments/{code}/result/{talent}', 'Api\v2\ScoresheetController@assessmentResult');//->name('assessments.index');
        Route::get('assessments/{code}/feedback/{talent}', 'Api\v2\ScoresheetController@assessmentFeedback');//->name('assessments.index');
        Route::post('assessments/{id}/supervisor/scoresheet', 'Api\v2\ScoresheetController@gradeAssessmentScoreSheet');//->name('assesments.index');
        Route::patch('assessments/{id}/supervisor/feedback', 'Api\v2\ScoresheetController@publishSupervisorFeedback');//->name('assesments.index');
        // Assesment Questions
        Route::post('assesments/questions', 'Api\v2\AssesmentQuestionController@store');//->name('assesments.index');
        Route::patch('assesments/questions/{id}/archive', 'Api\v2\AssesmentQuestionController@archive')->name('assesments.archive');
        Route::patch('assesments/questions/{id}/unarchive', 'Api\v2\AssesmentQuestionController@unarchive')->name('assesments.unpublish');
        Route::delete('assesments/questions/{id}', 'Api\v2\AssesmentQuestionController@destroy');//->name('assesments.index');

        // Campaigns
        Route::post('campaigns/{id}/photo', 'Api\v2\CampaignController@photo')->name('campaigns.update_photo');
        Route::patch('campaigns/{id}/publish', 'Api\v2\CampaignController@publish')->name('campaigns.publish');
        Route::patch('campaigns/{id}/unpublish', 'Api\v2\CampaignController@unpublish')->name('campaigns.unpublish');;
        Route::patch('campaigns/{id}/archive', 'Api\v2\CampaignController@archive')->name('campaigns.archive');
        Route::patch('campaigns/{id}/unarchive', 'Api\v2\CampaignController@unarchive')->name('campaigns.unarchive');;
        Route::post('campaigns/delete-multiple', 'Api\v2\CampaignController@destroyMultiple')->name('campaigns.destroy_multiple');
        // Route::get('campaigns', 'Api\v2\CampaignController@index')->name('campaigns.index');
        // Route::get('campaigns/{id}', 'Api\v2\CampaignController@show')->name('campaigns.show');
        // Route::post('campaigns', 'Api\v2\CampaignController@store')->name('campaigns.store');
        // Route::put('campaigns/{id}', 'Api\v2\CampaignController@update')->name('campaigns.update');
        // Route::delete('campaigns/{id}', 'Api\v2\CampaignController@destroy')->name('campaigns.destroy');
    });



    Route::middleware('auth:sanctum')->prefix('croxxtalent')->group( function () {
        // Professional
        Route::resources([ 'professional' => 'Api\v2\ProfessionalController' ]);
        Route::patch('professional/{id}/archive', 'Api\v2\ProfessionalController@archive')->name('professional.archive');
        Route::patch('professional/{id}/unarchive', 'Api\v2\ProfessionalController@unarchive')->name('professional.unarchive');
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
            'message' => "Resource not found",
        ], 404);
    });
    // Nothing more, this is just route for direct access to the API domain


    // }); // end of Route::middleware('auth.apikey')...


    // dataTableFilter: {
    //     per_page: 25, // 25, -1 or all = all records,
    //     page: 1,
    //     search: null,
    //     active: "no",
    //     sort_by: "created_at",
    //     sort_dir: "desc"
    //   },
