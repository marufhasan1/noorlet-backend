<?php

use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\WishlistController;
use Illuminate\Support\Facades\Route;

// ─── Public routes ───────────────────────────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login',    [AuthController::class, 'login']);
    Route::post('logout',   [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::get('user',      [AuthController::class, 'user'])->middleware('auth:sanctum');
});

// Products — public (no auth required)
Route::get('products',     [ProductController::class, 'index']);
Route::get('products/{id}',[ProductController::class, 'show']);

// ─── Authenticated routes ─────────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Profile
    Route::get('profile',             [ProfileController::class, 'show']);
    Route::patch('profile',           [ProfileController::class, 'update']);
    Route::post('profile/password',   [ProfileController::class, 'changePassword']);

    // Orders
    Route::get('orders',              [OrderController::class, 'index']);
    Route::post('orders',             [OrderController::class, 'store']);
    Route::get('orders/{order}',      [OrderController::class, 'show']);

    // Addresses
    Route::get('addresses',           [AddressController::class, 'index']);
    Route::post('addresses',          [AddressController::class, 'store']);
    Route::patch('addresses/{address}', [AddressController::class, 'update']);
    Route::delete('addresses/{address}',[AddressController::class, 'destroy']);

    // Wishlist
    Route::get('wishlist',            [WishlistController::class, 'index']);
    Route::get('wishlist/ids',        [WishlistController::class, 'ids']);
    Route::post('wishlist',           [WishlistController::class, 'store']);
    Route::delete('wishlist/{productId}', [WishlistController::class, 'destroy']);
});
