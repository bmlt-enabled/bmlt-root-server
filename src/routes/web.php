<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SwitcherController;
use App\Http\Controllers\Legacy\LegacyController;

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


Route::get('/client_interface/{dataFormat}', [SwitcherController::class, 'get']);

Route::any('{all}', [LegacyController::class, 'all'])
    ->where('all', '.*');
