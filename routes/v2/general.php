<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::prefix('croxtec')->middleware('web')->name('api.croxtec.')->group( function () {
    Route::post('/contact', 'Api\v2\GeneralController@contact')->name('contact');
    Route::post('/newsletter', 'Api\v2\GeneralController@newsletter')->name('newsletter');
});

 // Profile & Settings
 Route::get('settings', 'Api\v2\CroxxProfileController@settings')->name('profile.settings');
 Route::put('profile', 'Api\v2\CroxxProfileController@update')->name('users.update');
 Route::post('profile/photo', 'Api\v2\CroxxProfileController@photo')->name('users.update_photo');
 Route::get('notifications', 'Api\v2\UserController@notifications')->name('users.notifications');
 Route::get('notifications/seen/{id}', 'Api\v2\UserController@seenNotification')->name('users.notifications');
 Route::post('users/{id}/resend-verification', 'Api\v2\UserController@resendVerification')->name('users.resend_verification');


Route::get('jobs', 'Api\v2\CroxxJobsController@index')->name('jobs.index');
Route::get('jobs/{id}', 'Api\v2\CroxxJobsController@show')->name('jobs.show');
Route::get('{username}', 'Api\v2\CroxxProfileController@index')->name('profile');
