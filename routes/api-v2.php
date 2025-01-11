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


// require __DIR__.'/v2/guest.php';


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


require __DIR__ .'/v2/auth_guest.php';

require __DIR__ .'/v2/admin.php';

require __DIR__.'/v2/assessment.php';

require __DIR__.'/v2/career.php';

require __DIR__.'/v2/course.php';

require __DIR__.'/v2/company.php';

require __DIR__.'/v2/campaign.php';

require __DIR__.'/v2/employee.php';

require __DIR__.'/v2/goals.php';

require __DIR__.'/v2/configuration.php';

require __DIR__.'/v2/company.php';

require __DIR__.'/v2/talent.php';

require __DIR__.'/v2/general.php';


Route::fallback(function () {
    return response()->json([
        'status' => false,
        'message' => "",
    ], 404);
});
