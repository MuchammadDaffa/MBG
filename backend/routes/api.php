<?php

use App\Http\Controllers\Api\V1\LocationController;
use App\Http\Controllers\Api\V1\ConsumptionController;
use App\Http\Controllers\Api\V1\GoodsReceiptController;
use App\Http\Controllers\Api\V1\ItemController;
use App\Http\Controllers\Api\V1\RoleController;
use App\Http\Controllers\Api\V1\StockController;
use App\Http\Controllers\Api\V1\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function (): void {
    Route::post('login', [AuthController::class, 'login']);
});

Route::middleware('auth:sanctum')->group(function (): void {
    Route::prefix('auth')->group(function (): void {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
    });

    Route::prefix('v1')->group(function (): void {
        Route::apiResource('consumptions', ConsumptionController::class)->only(['index', 'store', 'show']);
        Route::apiResource('goods-receipts', GoodsReceiptController::class)->only(['index', 'store', 'show']);
        Route::apiResource('items', ItemController::class);
        Route::get('roles', [RoleController::class, 'index'])->middleware('role:admin_pusat');
        Route::apiResource('locations', LocationController::class)->middleware('role:admin_lokasi,admin_pusat');
        Route::get('stocks/balances', [StockController::class, 'balances']);
        Route::get('stocks/low', [StockController::class, 'low']);
        Route::post('stocks/minimums', [StockController::class, 'setMinimum'])->middleware('role:admin_lokasi,admin_pusat');
    });
});
