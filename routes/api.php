<?php

use App\Http\Controllers\LabyrintheCallbackController;
use Illuminate\Support\Facades\Route;

// Webhook Labyrinthe (groupe API : pas de CSRF, stateless).
Route::post('/labyrinthe/callback', [LabyrintheCallbackController::class, 'handle'])
    ->name('labyrinthe.callback');
