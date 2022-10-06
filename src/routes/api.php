<?php

use App\Http\Controllers\Admin\FormatController;
use App\Http\Controllers\Admin\MeetingController;
use App\Http\Controllers\Admin\ServiceBodyController;
use App\Http\Controllers\Admin\TokenController;
use App\Http\Controllers\Admin\UserController;
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

Route::get('/openapi.json', fn () => response(File::get(storage_path('api-docs/api-docs.json')))->withHeaders(['content-type' => 'text/json']));
Route::post('/auth/token', [TokenController::class, 'token']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/refresh', [TokenController::class, 'refresh']);
    Route::post('/auth/logout', [TokenController::class, 'logout']);
    Route::apiResource('formats', FormatController::class);
    Route::patch('/formats/{format}', [FormatController::class, 'partialUpdate']);
    Route::apiResource('meetings', MeetingController::class);
    Route::patch('/meetings/{meeting}', [MeetingController::class, 'partialUpdate']);
    Route::apiResource('servicebodies', ServiceBodyController::class, ['parameters' => ['servicebodies' => 'serviceBody']]);
    Route::patch('/servicebodies/{serviceBody}', [ServiceBodyController::class, 'partialUpdate']);
    Route::apiResource('users', UserController::class);
    Route::patch('/users/{user}', [UserController::class, 'partialUpdate']);
});
