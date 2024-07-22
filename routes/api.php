<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdjustmentController;
use App\Http\Controllers\CommissionController;
use App\Http\Controllers\DesignController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TailorController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\UserController;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register'])->name('register');
    Route::post('login', [AuthController::class, 'login'])->name('login');
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('refresh-token', [AuthController::class, 'refresh']);

    // Email Verification routes
    Route::get('email/verify', function () {
        return redirect(env('FRONTEND_URL') . 'account/verify');
    })->name('verification.notice');
    Route::get('email/verify/{id}/{hash}', [AuthController::class, 'verify_email'])
        ->middleware(['signed'])->name('verification.verify');
    Route::post('email/verification-notice/{id}', [AuthController::class, 'resend_email'])
        ->middleware(['throttle:6,1'])->name('verification.name');

    // Password routes
    Route::post('forgot-password', [AuthController::class, 'forgot_password']);
    Route::post('reset-password', [AuthController::class, 'reset_password']);
});

Route::group(['middleware' => ['jwt.auth', 'verified']], function () {
    Route::get('profile', [AuthController::class, 'me']);
    Route::apiResource('roles', RoleController::class);
    Route::apiResource('designs', DesignController::class);
    Route::apiResource('tailors', TailorController::class);
    Route::apiResource('permissions', PermissionController::class);
    Route::apiResource('commissions', CommissionController::class);
    Route::apiResource('adjustments', AdjustmentController::class);
    Route::get('display/{id}', [UserController::class, 'display'])->name('profile');
    Route::apiResource('users', UserController::class);
});
