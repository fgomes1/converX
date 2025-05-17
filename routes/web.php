<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TestController;
use App\Http\Controllers\OcrController;
use App\Http\Controllers\TogetherController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::get('/together',   [TogetherController::class, 'showForm']);
Route::post('/together/chat',      [TogetherController::class, 'chatCompletions']);
Route::post('/together/stream',    [TogetherController::class, 'streamCompletions']);

Route::get('/test-image', [TestController::class, 'testImage']);

Route::post('/test-upload', [TestController::class, 'testUpload']);

Route::get('/ocr-upload-form', function () {
    return view('ocr_upload');
});

Route::post('/ocr-upload', [OcrController::class, 'uploadAndProcess']);


Route::post('/together/chat',      [TogetherController::class, 'chatCompletions']);
Route::post('/together/stream',    [TogetherController::class, 'streamCompletions']);


