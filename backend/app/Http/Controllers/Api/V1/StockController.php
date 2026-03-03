<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStockMinimumRequest;
use App\Services\StockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function __construct(
        private readonly StockService $stockService,
    ) {
    }

    public function balances(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'location_id' => ['nullable', 'integer', 'exists:locations,id'],
            'item_id' => ['nullable', 'integer', 'exists:items,id'],
        ]);

        return response()->json([
            'data' => $this->stockService->getBalances(
                isset($validated['location_id']) ? (int) $validated['location_id'] : null,
                isset($validated['item_id']) ? (int) $validated['item_id'] : null,
            ),
        ]);
    }

    public function low(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'location_id' => ['required', 'integer', 'exists:locations,id'],
        ]);

        return response()->json([
            'data' => $this->stockService->getLowStocks((int) $validated['location_id']),
        ]);
    }

    public function setMinimum(StoreStockMinimumRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $minimum = $this->stockService->setMinimum(
            (int) $validated['location_id'],
            (int) $validated['item_id'],
            (float) $validated['min_qty'],
        );

        return response()->json([
            'message' => 'Stock minimum upserted successfully.',
            'data' => $minimum,
        ], 201);
    }
}
