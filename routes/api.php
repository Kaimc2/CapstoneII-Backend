<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdjustmentController;
use App\Http\Controllers\CommissionController;
use App\Http\Controllers\DesignController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ColorController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\SizeController;
use App\Http\Controllers\StoreColorController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\StoreMaterialController;
use App\Http\Controllers\StoreSizeController;
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
    Route::post('current-password', [AuthController::class, 'current_password']);
    Route::post('forgot-password', [AuthController::class, 'forgot_password']);
    Route::post('reset-password', [AuthController::class, 'reset_password']);
    Route::post('reset-password/resend', [AuthController::class, 'resend_reset_password']);
    Route::post('reset-existing-password', [AuthController::class, 'reset_existing_password']);
});

Route::group(['middleware' => ['jwt.auth', 'verified']], function () {
    Route::get('profile', [AuthController::class, 'me']);
    Route::put('profile/update/{id}', [AuthController::class, 'update']);
    Route::get('dashboard/stats', [DashboardController::class, 'get_stats']);
    Route::apiResource('roles', RoleController::class);
    Route::get('designs/deleted', [DesignController::class, 'show_deleted']);
    Route::get('designs/recent', [DesignController::class, 'show_recent']);
    Route::apiResource('designs', DesignController::class);
    Route::put('designs/{id}/restore', [DesignController::class, 'restore']);
    Route::apiResource('stores', StoreController::class);
    Route::apiResource('permissions', PermissionController::class);
    Route::get('user/commissions', [CommissionController::class, 'my_commissions']);
    Route::get('store/commissions', [CommissionController::class, 'store_commissions']);
    Route::get('commissions/recent', [CommissionController::class, 'show_recent']);
    Route::apiResource('commissions', CommissionController::class);
    Route::apiResource('adjustments', AdjustmentController::class);
    Route::get('display/{id}', [UserController::class, 'display'])->name('profile');
    Route::post('role/{id}', [UserController::class, 'assign_new_role']);
    Route::apiResource('materials', MaterialController::class);
    Route::apiResource('colors', ColorController::class);
    Route::apiResource('sizes', SizeController::class);
    Route::apiResource('store/materials', StoreMaterialController::class);
    Route::apiResource('store/colors', StoreColorController::class);
    Route::apiResource('store/sizes', StoreSizeController::class);
    Route::apiResource('users', UserController::class);
});

Route::get('test', function () {
    return 'Test route is working!';
});

