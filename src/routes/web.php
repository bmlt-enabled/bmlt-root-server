<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CatchAllController;
use App\Http\Controllers\Legacy\LegacyAuthController;
use App\Http\Controllers\Query\ServerInfoXmlController;
use App\Http\Controllers\Query\SwitcherController;

// legacy crouton
Route::get('/client_interface/html', [CatchAllController::class, 'all']);

// reimplemented serverInfo.xml
Route::get('/client_interface/serverInfo.xml', [ServerInfoXmlController::class, 'get']);

// reimplemented query apis
Route::get('/client_interface/{dataFormat}', [SwitcherController::class, 'get'])->middleware('json');

// reimplemented auth
Route::any('/local_server/server_admin/{dataFormat}.php', [LegacyAuthController::class, 'handle'])->where('dataFormat', 'json|xml');
Route::any('/', [LegacyAuthController::class, 'handle']);
Route::any('/index.php', [LegacyAuthController::class, 'handle']);

// Catch-all for everything else - legacy code or UI
Route::get('{any}', [CatchAllController::class, 'all'])->where('any', '.*');
