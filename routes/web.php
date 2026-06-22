<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\MainController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\RideController;
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
    });

Route::middleware('guest')->group(function () {
    Route::controller(LoginController::class)->group(function () {
        Route::get('/login', 'login')->name('login');
        Route::post('/login', 'store')->name('login.store');
    });

    Route::controller(RegisterController::class)->group(function () {
        Route::get('/register', 'register')->name('register');
        Route::post('/register', 'store')->name('register.store');
    });
});

Route::middleware(['auth', 'active'])->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::controller(UserController::class)
        ->prefix('user')
        ->name('user.')
        ->group(function () {
            Route::get('/dashboard/driver', 'dashboardDriver')
                ->middleware('role:driver')
                ->name('dashboardDriver');
            Route::get('/dashboard/client', 'dashboardClient')
                ->middleware('role:client')
                ->name('dashboardClient');
        });

    Route::middleware('role:admin')
        ->prefix('admin')
        ->name('admin.')
        ->controller(AdminController::class)
        ->group(function () {
            Route::get('/dashboard', 'dashboard')->name('dashboard');
            Route::get('/users', 'users')->name('users');
            Route::patch('/users/{user}/toggle-active', 'toggleUserActive')->name('users.toggle');
        });

    Route::resource('rides', RideController::class)->only(['index', 'store', 'show', 'destroy']);
    Route::patch('/rides/{ride}/cancel', [RideController::class, 'cancel'])->name('rides.cancel');
});
