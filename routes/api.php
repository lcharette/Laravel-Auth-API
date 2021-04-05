<?php
/**
 * Laravel Auth API
 *
 * @author    Louis Charette
 * @copyright Copyright (c) 2021 Louis Charette
 * @link      https://github.com/lcharette/laravel-auth-api
 * @license   https://github.com/lcharette/laravel-auth-api/blob/master/LICENSE.md (MIT License)
 */

use Illuminate\Support\Facades\Route;
use Lcharette\AuthApi\Http\Controllers\AuthController;
use Lcharette\AuthApi\Http\Controllers\ResetPasswordController;
use Lcharette\AuthApi\Http\Middleware\RequireAuth;
use Lcharette\AuthApi\Http\Middleware\RequireGuest;

Route::prefix('api')->middleware('api')->group(function () {
    
    Route::middleware(RequireGuest::class)->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);
        Route::post('password/email', [ResetPasswordController::class, 'sendResetLinkEmail']);
        Route::post('password/reset', [ResetPasswordController::class, 'reset']);
    });
    
    Route::middleware(RequireAuth::class)->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::get('user', [AuthController::class, 'user']);
    });
});
