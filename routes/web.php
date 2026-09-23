<?php

use App\Http\Controllers\Admin\CameraController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AlertController;
use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Root redirect to dashboard if authenticated
Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : view('welcome');
});

// Authenticated routes
Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard / Map Monitoring
    Route::get('/dashboard', [MonitoringController::class, 'index'])->name('dashboard');

    // Alert Log
    Route::get('/alerts', [AlertController::class, 'index'])->name('alerts.index');
    Route::post('/alerts/{cameraId}/acknowledge', [AlertController::class, 'acknowledge'])->name('alerts.acknowledge');

    // Admin-only routes
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::resource('cameras', CameraController::class)->except(['show']);
        Route::resource('users', UserController::class)->except(['show']);
    });

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
