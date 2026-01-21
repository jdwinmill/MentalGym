<?php

use App\Http\Controllers\Admin\ApiMetricsController as AdminApiMetricsController;
use App\Http\Controllers\Admin\PlanController as AdminPlanController;
use App\Http\Controllers\Admin\PracticeModeController as AdminPracticeModeController;
use App\Http\Controllers\Admin\QuestionController as AdminQuestionController;
use App\Http\Controllers\Admin\TagController as AdminTagController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\BlindSpotController;
use App\Http\Controllers\Api\TrainingApiController;
use App\Http\Controllers\BlindSpotDashboardController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmailPreferenceController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\PracticeModeController;
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

    // Practice Modes
    Route::get('practice-modes', [PracticeModeController::class, 'index'])->name('practice-modes.index');
    Route::get('practice-modes/{practiceMode:slug}/train', [PracticeModeController::class, 'train'])->name('practice-modes.train');

    // Training API
    Route::prefix('api/training')->group(function () {
        Route::post('start/{practiceMode:slug}', [TrainingApiController::class, 'start'])->name('api.training.start');
        Route::post('continue/{session}', [TrainingApiController::class, 'continue'])->name('api.training.continue');
        Route::post('end/{session}', [TrainingApiController::class, 'end'])->name('api.training.end');
    });

    // Blind Spots API
    Route::prefix('api/blind-spots')->group(function () {
        Route::get('/', [BlindSpotController::class, 'index'])->name('api.blind-spots.index');
        Route::get('/teaser', [BlindSpotController::class, 'teaser'])->name('api.blind-spots.teaser');
        Route::get('/status', [BlindSpotController::class, 'status'])->name('api.blind-spots.status');
    });

    // Blind Spots Dashboard
    Route::get('blind-spots', [BlindSpotDashboardController::class, 'index'])->name('blind-spots.index');
});

require __DIR__.'/settings.php';

// Email preferences (signed routes)
Route::get('/email/unsubscribe/{type}', [EmailPreferenceController::class, 'unsubscribe'])
    ->name('email.unsubscribe')
    ->middleware(['signed', 'auth']);

// Admin routes
Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
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
