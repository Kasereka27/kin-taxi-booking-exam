<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\MainController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\RideController;
use App\Http\Controllers\RideTrackingController;
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
        Route::get('/cgu', 'cgu')->name('legal.cgu');
        Route::get('/confidentialite', 'privacy')->name('legal.privacy');
        Route::post('/contact', [ContactController::class, 'store'])
            ->middleware('throttle:5,1')
            ->name('contact.store');
    });

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/suivi/{ride}', [MainController::class, 'trackRide'])->name('suivi.ride');
});

Route::controller(TwoFactorController::class)->prefix('two-factor')->name('two-factor.')->group(function () {
    Route::get('/', 'show')->name('show');
    Route::post('/', 'store')->middleware('throttle:5,1')->name('store');
    Route::post('/resend', 'resend')->middleware('throttle:3,1')->name('resend');
    Route::post('/cancel', 'cancel')->name('cancel');
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

    Route::controller(ForgotPasswordController::class)->group(function () {
        Route::get('/forgot-password', 'create')->name('password.request');
        Route::post('/forgot-password', 'store')->name('password.email');
    });

    Route::controller(ResetPasswordController::class)->group(function () {
        Route::get('/reset-password/{token}', 'create')->name('password.reset');
        Route::post('/reset-password', 'store')->name('password.update');
    });

    Route::controller(GoogleAuthController::class)->prefix('auth/google')->name('auth.google.')->group(function () {
        Route::get('/redirect', 'redirect')->name('redirect');
        Route::get('/callback', 'callback')->name('callback');
    });
});

Route::middleware(['auth', 'active'])->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::controller(EmailVerificationController::class)->group(function () {
        Route::get('/email/verify', 'notice')->name('verification.notice');
        Route::get('/email/verify/{id}/{hash}', 'verify')
            ->middleware(['signed', 'throttle:6,1'])
            ->name('verification.verify');
        Route::post('/email/verification-notification', 'send')
            ->middleware('throttle:6,1')
            ->name('verification.send');
    });

    Route::controller(ProfileController::class)->prefix('profile')->name('profile.')->group(function () {
        Route::get('/', 'edit')->name('edit');
        Route::patch('/', 'update')->name('update');
        Route::patch('/password', 'updatePassword')->name('password');
        Route::patch('/two-factor', 'updateTwoFactor')->name('two-factor');
    });

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
            Route::get('/live-rides', 'liveRides')->name('live-rides');
            Route::get('/users', 'users')->name('users');
            Route::patch('/users/{user}/toggle-active', 'toggleUserActive')->name('users.toggle');
            Route::delete('/users/{user}', 'destroyUser')->name('users.destroy');
            Route::get('/activity-logs', 'activityLogs')->name('activity-logs');
        });

    Route::controller(NotificationController::class)
        ->prefix('notifications')
        ->name('notifications.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/read-all', 'readAll')->name('readAll');
            Route::post('/{id}/read', 'read')->name('read');
        });

    Route::resource('rides', RideController::class)->only(['index', 'store', 'show', 'destroy']);
    Route::patch('/rides/{ride}/cancel', [RideController::class, 'cancel'])->name('rides.cancel');
    Route::get('/rides/{ride}/tracking', [RideTrackingController::class, 'show'])->name('rides.tracking.show');
    Route::patch('/rides/{ride}/tracking', [RideTrackingController::class, 'update'])->name('rides.tracking');
    Route::patch('/rides/{ride}/accept', [RideController::class, 'accept'])
        ->middleware('role:driver')
        ->name('rides.accept');

    Route::middleware('role:client')->group(function () {
        Route::get('/rides/{ride}/pay', [PaymentController::class, 'create'])->name('rides.pay');
        Route::post('/rides/{ride}/pay', [PaymentController::class, 'store'])->name('rides.pay.store');
        Route::get('/payments/{payment}/status', [PaymentController::class, 'status'])->name('payments.status');
        Route::get('/payments/{payment}/poll', [PaymentController::class, 'poll'])->name('payments.poll');
        Route::get('/payments/{payment}/receipt', [PaymentController::class, 'receipt'])->name('payments.receipt');
    });
});
