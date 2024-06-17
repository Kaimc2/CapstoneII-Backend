<?php

use App\Http\Controllers\AdjustmentController;
use App\Http\Controllers\CommissionController;
use App\Http\Controllers\DesignController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TailorController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\UserController;

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/verify_email', [AuthController::class, 'verify_email']);
    Route::post('/forgot_password', [AuthController::class, 'forgot_password']);
    Route::post('/reset_password', [AuthController::class, 'reset_password']);

    Route::group(['middleware' => 'jwt.auth'], function () {
        Route::apiResource('roles', RoleController::class);
        Route::apiResource('designs', DesignController::class);
        Route::apiResource('tailors', TailorController::class);
        Route::apiResource('permissions', PermissionController::class);
        Route::apiResource('commissions', CommissionController::class);
        Route::apiResource('adjustments', AdjustmentController::class);
        Route::apiResource('users', UserController::class);
    });
});
