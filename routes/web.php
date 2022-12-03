<?php

use App\Events\NotificationMessage;
use App\Notifications\CroxxTalentUsers;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
// TEMP TESTING
// Route::domain('croxxtalent.com')->group(function () {
//     Route::any('/', function (Request $request) {
//         echo "<title>CROXXTALENT</title>";
//         echo "<div style=\"text-align:center\"><b>CROXXTALENT</b> <br> croxxtalent.com <br><br> Wow! Something amazing is coming here. Check back soon.</div>";
//     });
// });

// // TEMP TESTING
// Route::domain('www.croxxtalent.com')->group(function () {
//     Route::any('/', function (Request $request) {
//         echo "<title>CROXXTALENT</title>";
//         echo "<div style=\"text-align:center\"><b>CROXXTALENT</b> <br> www.croxxtalent.com <br><br> Wow! Something amazing is coming here. Check back soon.</div>";
//     });
// });


// Landing Pages Route
Route::get('/', function (Request $request) {
    // return \File::get(public_path() . '/landing/index.html');
    return null;
    return view('welcome');
})->name('home');

Route::get('/about', function (Request $request) {
    return view('about');
})->name('about');

Route::get('/services', function (Request $request) {
    return view('services');
})->name('services');

Route::get('/contact', function (Request $request) {
    return view('contact');
})->name('contact');

Route::post('/contact', 'LandingPagesControlller@postContact')->name('contact.post');
Route::post('/subscribe', 'LandingPagesControlller@subscribe')->name('subscribe');

Route::get('/terms', function (Request $request) {
    return view('terms');
})->name('terms');

Route::get('/privacy', function (Request $request) {
    return view('privacy');
})->name('privacy');


//
Route::get('/app/{path}', function (Request $request) {
    return \File::get(public_path() . '/app/index.html');
    return view('app');
})->where('path','([A-z\d\/\-_.]+)?');

Route::get('/login', function (Request $request) {
    return redirect('/app/login');
})->name('login');

Route::get('/register', function (Request $request) {
    return redirect('/app/register');
})->name('register');

Route::get('/dashboard', function (Request $request) {
    return redirect('/app/dashboard');
})->name('dashboard');
