<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

  // Croxx Jobs
  Route::middleware('auth:sanctum')->name('api.')->group( function () {
    Route::get('jobs/dashboard', 'Api\v2\CroxxJobsController@dashboard')->name('jobs.dashboard');
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
    Route::post('candidate/{id}/rating', 'Api\v2\CandidateController@rateCandidate')->name('candidate.rating');
    Route::post('candidate/invite', 'Api\v2\CandidateController@invite')->name('candidate.invite');
    // Route::post('candidate/check', 'Api\v2\CandidateController@check')->name('job_invitations.check');
    // Route::get('candidate/invited', 'Api\v2\CandidateController@index')->name('candidate.index');
    Route::post('candidate/{id}/withdraw', 'Api\v2\CandidateController@withdraw')->name('candidate.withdraw');
    // Route::post('candidate/{id}/result', 'Api\v2\CandidateController@result')->name('candidate.result');
});
