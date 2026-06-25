<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RideController;
use App\Http\Controllers\LabyrintheCallbackController;
use Illuminate\Support\Facades\Route;

Route::post('/labyrinthe/callback', [LabyrintheCallbackController::class, 'handle'])
    ->name('labyrinthe.callback');

Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:10,1')
    ->name('api.login');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');
    Route::get('/user', [AuthController::class, 'me'])->name('api.user');

    Route::get('/rides', [RideController::class, 'index'])->name('api.rides.index');
    Route::post('/rides', [RideController::class, 'store'])->name('api.rides.store');
    Route::get('/rides/{ride}', [RideController::class, 'show'])->name('api.rides.show');
    Route::delete('/rides/{ride}', [RideController::class, 'destroy'])->name('api.rides.destroy');
    Route::patch('/rides/{ride}/cancel', [RideController::class, 'cancel'])->name('api.rides.cancel');
});
