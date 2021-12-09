<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ResetPasswordController;

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

Route::post("forget_password", [ResetPasswordController::class, "forgetPassword"]);
Route::post("reset_password/{token}/{email}", [ResetPasswordController::class, "resetPassword"]);
