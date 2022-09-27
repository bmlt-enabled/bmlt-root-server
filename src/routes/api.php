<?php

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

Route::post('/auth/token', [TokenController::class, 'token']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/refresh', [TokenController::class, 'refresh']);
    Route::post('/auth/logout', [TokenController::class, 'logout']);
    Route::apiResource('servicebodies', ServiceBodyController::class, ['parameters' => ['servicebodies' => 'serviceBody']]);
    Route::apiResource('users', UserController::class);
});
