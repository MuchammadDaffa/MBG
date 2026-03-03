<?php

use App\Http\Controllers\Api\V1\LocationController;
use App\Http\Controllers\Api\V1\ConsumptionController;
use App\Http\Controllers\Api\V1\GoodsReceiptController;
use App\Http\Controllers\Api\V1\ItemController;
use App\Http\Controllers\Api\V1\RoleController;
use App\Http\Controllers\Api\V1\StockController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::apiResource('consumptions', ConsumptionController::class)->only(['index', 'store', 'show']);
    Route::apiResource('goods-receipts', GoodsReceiptController::class)->only(['index', 'store', 'show']);
    Route::apiResource('items', ItemController::class);
    Route::get('roles', [RoleController::class, 'index']);
    Route::apiResource('locations', LocationController::class);
    Route::get('stocks/balances', [StockController::class, 'balances']);
    Route::get('stocks/low', [StockController::class, 'low']);
    Route::post('stocks/minimums', [StockController::class, 'setMinimum']);
});
