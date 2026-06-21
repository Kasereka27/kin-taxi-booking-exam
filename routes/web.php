<?php

use App\Http\Controllers\MainController;
use Illuminate\Support\Facades\Route;

Route::controller(MainController::class)
    ->group(function () {
        Route::get('/', 'home')->name('home')->name('home');
        Route::get('/reservation', 'reservation')->name('reservation');
        Route::get('/suivi', 'suivi')->name('suivi');
        Route::get('/tarifs', 'tarifs')->name('tarifs');
        Route::get('/about', 'about')->name('about');
        Route::get('/contact', 'contact')->name('contact');
    });
