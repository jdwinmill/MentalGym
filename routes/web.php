<?php

use App\Http\Controllers\Admin\QuestionController as AdminQuestionController;
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
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');
});

require __DIR__.'/settings.php';

// Admin routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('questions', AdminQuestionController::class)->except(['show']);
});
