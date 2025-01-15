<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::middleware('auth:sanctum')->name('api.')->group( function () {


    Route::resources([
        'projects' => 'Api\v2\Project\ProjectController',
        'milestones' => 'Api\v2\Project\MilestoneController',
    ]);

});
