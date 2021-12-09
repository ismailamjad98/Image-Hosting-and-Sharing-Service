<?php

use App\Http\Controllers\AccessImageController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UploadImageCotroller;

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


Route::post('/upload_image', [UploadImageCotroller::class, 'uploadImage']);
Route::post('/delete_image/{id}', [UploadImageCotroller::class, 'deleteImage']);
Route::post('/my_images', [UploadImageCotroller::class, 'myImages']);
Route::post('/search/{image}', [UploadImageCotroller::class, 'searchImage']);

//Image Routes with middleware
Route::middleware(['token'])->group(function () {
    Route::post('/image_permission', [AccessImageController::class, 'givePermission']);
    Route::get('/view_image', [AccessImageController::class, 'ViewImage']);
});
