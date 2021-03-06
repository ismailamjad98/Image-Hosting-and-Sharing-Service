<?php

use App\Http\Controllers\UserController;
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

//User Routes
Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);
Route::get('emailVerify/{token}/{email}', [UserController::class, 'EmailVerify']);


//User Routes with middleware
Route::middleware(['token'])->group(function () {
    //User Routes
    Route::post('/profile', [UserController::class, 'Profile']);
    Route::post('/profile/update', [UserController::class, 'update']);
    Route::post('/logout', [UserController::class, 'logout']);
});
