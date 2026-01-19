<?php

use App\Http\Controllers\Admin\ApiMetricsController as AdminApiMetricsController;
use App\Http\Controllers\Admin\PlanController as AdminPlanController;
use App\Http\Controllers\Admin\PracticeModeController as AdminPracticeModeController;
use App\Http\Controllers\Admin\QuestionController as AdminQuestionController;
use App\Http\Controllers\Admin\TagController as AdminTagController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LandingController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Landing page (public)
Route::get('/', [LandingController::class, 'index'])->name('landing');
Route::post('/early-access', [LandingController::class, 'store'])
    ->middleware('throttle:5,1') // 5 attempts per minute
    ->name('early-access.store');

// Mental Gym app (moved to /app)
Route::get('/app', function () {
    return Inertia::render('mental-gym');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard - accessible to all authenticated users
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

require __DIR__.'/settings.php';

// Admin routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('questions', AdminQuestionController::class)->except(['show']);

    // Plans management (read-only, config-based)
    Route::get('plans', [AdminPlanController::class, 'index'])->name('plans.index');

    // User management
    Route::resource('users', AdminUserController::class)->only(['index', 'edit', 'update']);
    Route::post('users/{user}/extend-trial', [AdminUserController::class, 'extendTrial'])->name('users.extend-trial');

    // Practice Mode management
    Route::resource('practice-modes', AdminPracticeModeController::class)->except(['show']);

    // Tag management
    Route::resource('tags', AdminTagController::class)->except(['show']);

    // API Metrics
    Route::get('api-metrics', [AdminApiMetricsController::class, 'index'])->name('api-metrics.index');
});
