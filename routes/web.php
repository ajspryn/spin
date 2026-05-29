<?php

use App\Http\Controllers\VisitorController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PrizeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ── Visitor / Tablet ──────────────────────────────────────────────────────────
Route::get('/', [VisitorController::class, 'showRegistration'])->name('visitor.register');
Route::post('/register', [VisitorController::class, 'register'])->name('visitor.register.submit');
Route::get('/controller', [VisitorController::class, 'showController'])->name('visitor.controller');

// ── TV Display ────────────────────────────────────────────────────────────────
Route::view('/tv-display', 'tv.display')->name('tv.display');

// ── Admin Auth ────────────────────────────────────────────────────────────────
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Protected admin routes
    Route::middleware('admin')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::post('/god-mode', [DashboardController::class, 'setGodMode'])->name('god-mode');
        Route::post('/event-settings', [DashboardController::class, 'saveEventSettings'])->name('event-settings');
        Route::get('/simulate-spin', [DashboardController::class, 'simulateSpin'])->name('simulate-spin');

        Route::resource('prizes', PrizeController::class)->except(['show']);
        Route::delete('/winners/{id}', [\App\Http\Controllers\Admin\SpinLogController::class, 'destroy'])->name('winners.destroy');
    });
});
