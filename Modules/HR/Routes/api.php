<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\HR\Http\Controllers\Api\Company\HolidayController;
use Modules\HR\Http\Controllers\Api\Company\PolicyController;

/*
    |--------------------------------------------------------------------------
    | API Routes
    |--------------------------------------------------------------------------
    |
    | Here is where you can register API routes for your application. These
    | routes are loaded by the RouteServiceProvider within a group which
    | is assigned the "api" middleware group. Enjoy building your API!
    |
*/

Route::middleware(['auth:sanctum'])->prefix('hr')->name('api.')->group(function () {
    // Route::get('hr', fn (Request $request) => $request->user())->name('hr');

    Route::resources([ 'holidays' =>  HolidayController::class ]);
    Route::resources([ 'policies' =>  PolicyController::class ]);

});
