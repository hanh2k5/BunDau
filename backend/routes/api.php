<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\RevenueController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Bún Đậu Quán
|--------------------------------------------------------------------------
*/

// ── Public Routes (Anyone can see) ──────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->name('auth.login');
});

Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');


// ── Protected routes (require Sanctum token) ──────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::prefix('auth')->group(function () {
        Route::get('/me', [AuthController::class, 'me'])->name('auth.me');
        Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
    });

    // Products — admin only: create, update, toggle, delete
    Route::middleware('admin')->group(function () {
        Route::post('/products', [ProductController::class, 'store'])->name('products.store');
        Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::patch('/products/{product}/toggle', [ProductController::class, 'toggle'])->name('products.toggle');
        Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

        // Staff Management
        Route::apiResource('users', UserController::class)->except(['show']);
    });

    // Orders — access controlled by OrderPolicy
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
    Route::patch('/orders/{order}/pay', [OrderController::class, 'pay'])->name('orders.pay');
    Route::patch('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
    Route::delete('/orders/{order}', [OrderController::class, 'destroy'])->name('orders.destroy');

    // Revenue — admin only
    Route::middleware('admin')->prefix('revenue')->group(function () {
        Route::get('/today', [RevenueController::class, 'today'])->name('revenue.today');
        Route::get('/summary', [RevenueController::class, 'summary'])->name('revenue.summary');
        Route::get('/by-date', [RevenueController::class, 'byDate'])->name('revenue.by-date');
        Route::get('/by-range', [RevenueController::class, 'byRange'])->name('revenue.by-range');
    });
});
