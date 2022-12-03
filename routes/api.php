<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::any('/', function (Request $request) {
    return response()->json([
        'status' => true, 
        'message' => "If you're not sure you know what you are doing, you probably shouldn't be using this API...",
        'data' => [
            'service' => 'croxxtalent-api',
            'version' => '1.0',
        ]
    ], 200);
});
// Route::get('/ok', 'Api\v1\AuthController@testAuth');
// The fallback route should always be the last route registered by your application.
Route::fallback(function () {
    return response()->json([
        'status' => false, 
        'message' => "Resource not found",
    ], 404);
});

// Nothing more, this is just route for direct access to the API domain