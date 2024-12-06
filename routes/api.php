<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;

/**
 * API Routes
 * Prefix: api/v1
 */
Route::prefix('v1')->group(function () {
    // Auth Routes
    Route::controller(AuthController::class)->group(function () {
        Route::post('auth/login', 'login');
        Route::post('auth/register', 'register');

        // Protected Auth Routes
        Route::middleware('jwt.auth')->group(function () {
            Route::post('auth/logout', 'logout');
            Route::post('auth/refresh', 'refresh');
            Route::get('auth/me', 'me');
        });
    });

    // Protected routes
    Route::middleware(['jwt.auth'])->group(function () {
        // Admin routes
        Route::middleware(['check.role:admin'])->group(function () {
            Route::post('/orders', [OrderController::class, 'store']);
        });

        // Supplier routes
        Route::middleware(['check.role:supplier'])->group(function () {
            Route::put('/orders/{orderItem}/delivery', [OrderController::class, 'updateDelivery']);
        });

        // Common routes (accessible by both roles)
        Route::apiResource('products', ProductController::class);
        Route::get('/orders', [OrderController::class, 'index']);
        Route::get('/orders/{order}', [OrderController::class, 'show']);
    });
});
