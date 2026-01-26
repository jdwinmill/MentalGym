<?php

use App\Http\Controllers\Admin\ApiMetricsController as AdminApiMetricsController;
use App\Http\Controllers\Admin\FeedbackController as AdminFeedbackController;
use App\Http\Controllers\Admin\InsightController as AdminInsightController;
use App\Http\Controllers\Admin\PlanController as AdminPlanController;
use App\Http\Controllers\Admin\PracticeModeController as AdminPracticeModeController;
use App\Http\Controllers\Admin\PrincipleController as AdminPrincipleController;
use App\Http\Controllers\Admin\QuestionController as AdminQuestionController;
use App\Http\Controllers\Admin\SkillDimensionController as AdminSkillDimensionController;
use App\Http\Controllers\Admin\TagController as AdminTagController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\BlindSpotController;
use App\Http\Controllers\Api\SkillDimensionController as ApiSkillDimensionController;
use App\Http\Controllers\Api\FeedbackController;
use App\Http\Controllers\Api\PrinciplesApiController;
use App\Http\Controllers\Api\TrainingApiController;
use App\Http\Controllers\BlindSpotDashboardController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmailPreferenceController;
use App\Http\Controllers\InsightsController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\PracticeModeController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Landing page (public)
Route::get('/', [LandingController::class, 'index'])->name('landing');
Route::post('/early-access', [LandingController::class, 'store'])
    ->middleware('throttle:5,1') // 5 attempts per minute
    ->name('early-access.store');

// Pricing page (public)
Route::get('/pricing', function () {
    return Inertia::render('pricing');
})->name('pricing');

// Mental Gym app (moved to /app)
Route::get('/app', function () {
    return Inertia::render('mental-gym');
})->name('home');

// Playbook (public - user-facing insights/learn section)
Route::get('playbook', [InsightsController::class, 'index'])->name('playbook.index');
Route::get('playbook/{insight:slug}', [InsightsController::class, 'show'])->name('playbook.show');

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard - accessible to all authenticated users
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Practice Modes
    Route::get('practice-modes', [PracticeModeController::class, 'index'])->name('practice-modes.index');
    Route::get('practice-modes/{practiceMode:slug}/train', [PracticeModeController::class, 'train'])->name('practice-modes.train');

    // Training API (legacy)
    Route::prefix('api/training')->group(function () {
        Route::post('start/{practiceMode:slug}', [TrainingApiController::class, 'start'])->name('api.training.start');
        Route::post('continue/{session}', [TrainingApiController::class, 'continue'])->name('api.training.continue');
        Route::post('end/{session}', [TrainingApiController::class, 'end'])->name('api.training.end');
    });

    // Training API v2 (drill-based)
    Route::prefix('api/training/v2')->group(function () {
        Route::get('check-context/{mode_slug}', [TrainingApiController::class, 'checkRequiredContext'])->name('api.training.v2.check-context');
        Route::post('update-profile', [TrainingApiController::class, 'updateProfile'])->name('api.training.v2.update-profile');
        Route::post('start/{mode_slug}', [TrainingApiController::class, 'startDrill'])->name('api.training.v2.start');
        Route::get('session/{session}', [TrainingApiController::class, 'showDrill'])->name('api.training.v2.show');
        Route::post('respond/{session}', [TrainingApiController::class, 'respondDrill'])->name('api.training.v2.respond');
        Route::post('continue/{session}', [TrainingApiController::class, 'continueDrill'])->name('api.training.v2.continue');
    });

    // Blind Spots API
    Route::prefix('api/blind-spots')->group(function () {
        Route::get('/', [BlindSpotController::class, 'index'])->name('api.blind-spots.index');
        Route::get('/teaser', [BlindSpotController::class, 'teaser'])->name('api.blind-spots.teaser');
        Route::get('/status', [BlindSpotController::class, 'status'])->name('api.blind-spots.status');
    });

    // Feedback API
    Route::post('api/feedback', [FeedbackController::class, 'store'])->name('api.feedback.store');

    // Principles & Insights API
    Route::get('api/principles', [PrinciplesApiController::class, 'index'])->name('api.principles.index');
    Route::get('api/insights/{slug}', [PrinciplesApiController::class, 'showInsight'])->name('api.insights.show');

    // Skill Dimensions API
    Route::get('api/skill-dimensions', [ApiSkillDimensionController::class, 'index'])->name('api.skill-dimensions.index');

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

    // Principles & Insights management
    Route::resource('principles', AdminPrincipleController::class)->except(['show']);
    Route::resource('insights', AdminInsightController::class)->except(['show']);

    // Skill Dimensions management
    Route::resource('skill-dimensions', AdminSkillDimensionController::class)->except(['show']);

    // API Metrics
    Route::get('api-metrics', [AdminApiMetricsController::class, 'index'])->name('api-metrics.index');

    // Feedback management
    Route::get('feedback', [AdminFeedbackController::class, 'index'])->name('feedback.index');
    Route::delete('feedback/{feedback}', [AdminFeedbackController::class, 'destroy'])->name('feedback.destroy');
});
