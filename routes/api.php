<?php

use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Auth\OtpController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware(['tenant', 'locale'])->group(function (): void {
    Route::post('auth/login', [LoginController::class, 'store'])->name('api.v1.auth.login');
    Route::post('auth/otp/send', [OtpController::class, 'send'])->name('api.v1.auth.otp.send');
    Route::post('auth/otp/verify', [OtpController::class, 'verify'])->name('api.v1.auth.otp.verify');

    Route::middleware(['auth:sanctum', 'api-tenant'])->group(function (): void {
        Route::post('auth/logout', [LoginController::class, 'destroy'])->name('api.v1.auth.logout');
        Route::get('auth/me', [LoginController::class, 'me'])->name('api.v1.auth.me');
    });
});
