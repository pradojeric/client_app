<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

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

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');


// Route::get('/oauth/request', [AuthController::class,'getOAuthToken'])->name('oauth.request');
// Route::get('/auth/callback', [AuthController::class,'getCallback'])->name('oauth.callback');
// Route::get('/oauth/login', [AuthController::class,'login'])->name('oauth.login');
// Route::post('/oauth/login-password', [AuthController::class,'loginViaPassword'])->name('oauth.login.password');
// Route::get('/oauth/logout', [AuthController::class,'logout'])->name('oauth.logout');

Route::get('/collect-students', function(){
    $response = Http::withHeaders([
        'Accept' => 'application/json',
        'Authorization' => 'Bearer ' . '9|qnycnKMtGJLwybb3eQR7acXFSU6JhDhsLnKak9aw',
    ])->get('http://users-app.test/api/v1/get-students', [
        'school_year' => '2021-2022',
        'term' => '2'
    ]);

    return $response->json();
});

Route::get('/collect-faculties', function(){
    $response = Http::withHeaders([
        'Accept' => 'application/json',
        'Authorization' => 'Bearer ' . '9|qnycnKMtGJLwybb3eQR7acXFSU6JhDhsLnKak9aw',
    ])->get('http://users-app.test/api/v1/get-faculties');

    return $response->json();
});


require __DIR__.'/auth.php';
