<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\MainController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::controller(MainController::class)
    ->group(function () {
        Route::get('/', 'home')->name('home');
        Route::get('/reservation', 'reservation')->name('reservation');
        Route::get('/suivi', 'suivi')->name('suivi');
        Route::get('/tarifs', 'tarifs')->name('tarifs');
        Route::get('/about', 'about')->name('about');
        Route::get('/contact', 'contact')->name('contact');
    }
);

Route::controller(LoginController::class)
    ->group(function () {
        Route::get('/login', 'login')->name('login');
    }
);

Route::controller(RegisterController::class)
    ->group(function () {
        Route::get('/register', 'register')->name('register');
    }
);

Route::controller(UserController::class)
    ->prefix('user')
    ->name('user.')
    ->group(function () {
        Route::get('/dashboard/driver', 'dashboardDriver')->name('dashboardDriver');
        Route::get('/dashboard/client', 'dashboardClient')->name('dashboardClient');
    }
);