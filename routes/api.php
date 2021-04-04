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
use Lcharette\AuthApi\Http\Middleware\RequireAuth;
use Lcharette\AuthApi\Http\Middleware\RequireGuest;

Route::prefix('api')->middleware('api')->group(function () {
    Route::post('register', [AuthController::class, 'register'])->middleware(RequireGuest::class);
    Route::post('login', [AuthController::class, 'login'])->middleware(RequireGuest::class);
    Route::post('logout', [AuthController::class, 'logout'])->middleware(RequireAuth::class);
    Route::post('refresh', [AuthController::class, 'refresh'])->middleware(RequireAuth::class);
    Route::get('user', [AuthController::class, 'user'])->middleware(RequireAuth::class);
});
