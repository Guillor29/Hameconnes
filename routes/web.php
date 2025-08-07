<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\FishingSpotController;
use App\Http\Controllers\API\FishSpeciesController;
use App\Http\Controllers\API\FishCatchController;
use App\Http\Controllers\API\EquipmentController;
use App\Http\Controllers\AuthController;


// Authentication Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected routes that require authentication
Route::middleware('auth')->group(function () {
    // Main application route
    Route::get('/', function () {
        return view('app');
    });

    // User profile routes
    Route::get('/profile', [\App\Http\Controllers\UserProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [\App\Http\Controllers\UserProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [\App\Http\Controllers\UserProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/password', [\App\Http\Controllers\UserProfileController::class, 'editPassword'])->name('profile.password');
    Route::put('/profile/password', [\App\Http\Controllers\UserProfileController::class, 'updatePassword'])->name('profile.password.update');

    // Routes that require user role
    Route::middleware('App\Http\Middleware\CheckRole:user')->group(function () {
        // User-specific routes here
    });

    // Routes that require admin role
    Route::middleware('App\Http\Middleware\CheckRole:admin')->group(function () {
        // Admin user management routes
        Route::prefix('admin')->name('admin.')->group(function () {
            Route::resource('users', \App\Http\Controllers\Admin\UserController::class);
        });
    });
});

// API Routes
Route::prefix('api')->group(function () {
    // Public API routes (accessible to guests)
    Route::get('fishing-spots', [FishingSpotController::class, 'index']);

    // Protected API routes (require authentication)
    Route::middleware('auth')->group(function () {
        // Current user information
        Route::get('user', [\App\Http\Controllers\API\UserController::class, 'current']);
        // API routes that require user role
        Route::middleware('App\Http\Middleware\CheckRole:user')->group(function () {
            Route::apiResource('fishing-spots', FishingSpotController::class)->except(['index']);
            Route::apiResource('fish-species', FishSpeciesController::class);
            Route::apiResource('catches', FishCatchController::class);
            Route::apiResource('equipment', EquipmentController::class);
        });
    });
});
