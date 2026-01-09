<?php

use App\Http\Controllers\Api\MentalGymController;
use Illuminate\Support\Facades\Route;

Route::get('/question/random', [MentalGymController::class, 'getRandomQuestion']);
Route::post('/response', [MentalGymController::class, 'submitResponse']);
