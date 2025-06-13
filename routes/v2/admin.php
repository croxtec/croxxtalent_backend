<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::middleware('auth:sanctum')->prefix('croxxtalent')->group( function () {
    // Users
    Route::get('users', 'Api\v2\UserController@index')->name('users.index');
    Route::get('users/{id}', 'Api\v2\UserController@show')->name('users.show');
    Route::post('users', 'Api\v2\UserController@store')->name('users.store');
    Route::delete('users/{id}', 'Api\v2\UserController@destroy')->name('users.destroy');
    Route::post('users/delete-multiple', 'Api\v2\UserController@destroyMultiple')->name('users.destroy_multiple');
    Route::patch('users/{id}/password', 'Api\v2\UserController@password')->name('users.update_password');
    Route::patch('users/{id}/activate', 'Api\v2\UserController@activate')->name('users.activate');
    Route::patch('users/{id}/archive', 'Api\v2\UserController@archive')->name('users.archive');
    Route::patch('users/{id}/unarchive', 'Api\v2\UserController@unarchive')->name('users.unarchive');
    Route::get('users/{id}/campaigns', 'Api\v2\UserController@campaigns')->name('users.campaigns');
    Route::get('users/{id}/affiliates', 'Api\v2\UserController@affiliates')->name('users.affiliates');
    Route::get('users/{id}/job-invitations', 'Api\v2\UserController@jobInvitations')->name('users.job_invitations');
    // Professional
    Route::resources([ 'professional' => 'Api\v2\Admin\ProfessionalController' ]);
    Route::patch('professional/{id}/archive', 'Api\v2\Admin\ProfessionalController@archive')->name('professional.archive');
    Route::patch('professional/{id}/unarchive', 'Api\v2\Admin\ProfessionalController@unarchive')->name('professional.unarchive');
});
