<?php

use App\Http\Controllers\SpinController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Session middleware is applied via bootstrap/app.php so the spin
| endpoint can read the session set by the web controller.
*/

// Public — TV display fetches prize list to render the wheel
Route::get('/prizes', [SpinController::class, 'prizes'])->name('api.prizes');

// Session-gated — only reachable after a successful registration
Route::post('/spin/execute', [SpinController::class, 'execute'])
     ->middleware(['web'])          // needs session + CSRF
     ->name('api.spin.execute');

// Session keep-alive endpoint
Route::post('/ping', function () {
     return response()->json(['pong' => true]);
})->middleware(['web'])->name('api.ping');
