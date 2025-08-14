
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;




Route::prefix('links')->middleware('web')->name('api.links.')->group( function () {
    // Verifications
    Route::prefix('verifications')->name('verifications.')->group( function () {
        Route::get('/verify-email/{token}', 'Api\v2\Link\VerificationLinkController@verifyEmail')->name('verify_email');
        Route::get('/verify-employee/{token}', 'Api\v2\Link\VerificationLinkController@verifyEmployee')->name('verify_employee');
        Route::get('/verify-edit-email/{token}', 'Api\v2\Link\VerificationLinkController@verifyEditEmail')->name('verify_edit_email');
    });

    Route::get('cvs/{id}/{employer}/generate', 'Api\v2\Resume\CvController@generate_employer')->name('cvs.generate.employer');
    Route::get('cvs/{id}/{employer}/download', 'Api\v2\Resume\CvController@generate_employer')->name('cvs.download.employer');

    // Signed Routes
    Route::middleware('signed')->group( function () {
        // CVs
        Route::get('cvs/{id}/generate', 'Api\v2\Resume\CvController@generate')->name('cvs.generate');
        Route::get('cvs/{id}/download', 'Api\v2\Resume\CvController@generate')->name('cvs.download');

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

    //Google
    Route::group(['middleware' => ['web']], function () {
        Route::get('/google', 'Api\v2\Auth\GoogleAuthController@redirect');
        Route::get('/google/callback', 'Api\v2\Auth\GoogleAuthController@handleGoogleCallback');
        Route::get('/linkedin', 'Api\v2\Auth\LinkedInController@redirect');
        Route::get('/linkedin/callback', 'Api\v2\Auth\LinkedInController@handleLinkedInCallback');
        Route::get('/linkedin/import', 'Api\v2\Auth\LinkedInController@importProfile');
    });
});
