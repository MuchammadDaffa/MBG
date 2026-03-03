<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGoodsReceiptRequest;
use App\Services\GoodsReceiptService;
use Illuminate\Http\JsonResponse;

class GoodsReceiptController extends Controller
{
    public function __construct(
        private readonly GoodsReceiptService $goodsReceiptService,
    ) {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => $this->goodsReceiptService->getAll(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreGoodsReceiptRequest $request): JsonResponse
    {
        $receipt = $this->goodsReceiptService->create(
            $request->validated(),
            optional($request->user())->id
        );

        return response()->json([
            'message' => 'Goods receipt created successfully.',
            'data' => $receipt,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        return response()->json([
            'data' => $this->goodsReceiptService->getById((int) $id),
        ]);
    }
}
