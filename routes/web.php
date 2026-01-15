<?php

use App\Http\Controllers\Admin\QuestionController as AdminQuestionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\LessonController;
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
    // Dashboard
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Lesson routes
    Route::get('lessons/{lesson}', [LessonController::class, 'show'])->name('lessons.show');
    Route::post('lessons/{lesson}/attempts', [LessonController::class, 'startAttempt'])
        ->name('lessons.attempts.start');
    Route::post('attempts/{attempt}/interactions', [LessonController::class, 'recordInteraction'])
        ->name('attempts.interactions.store');
    Route::post('attempts/{attempt}/answers', [LessonController::class, 'submitAnswer'])
        ->name('attempts.answers.store');
    Route::post('attempts/{attempt}/complete', [LessonController::class, 'completeAttempt'])
        ->name('attempts.complete');

    // Media streaming from S3
    Route::get('media/{path}', [LessonController::class, 'streamAudio'])
        ->where('path', '.*')
        ->name('media.stream');
});

require __DIR__.'/settings.php';

// Admin routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('questions', AdminQuestionController::class)->except(['show']);
});
