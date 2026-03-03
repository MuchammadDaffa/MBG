<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreConsumptionRequest;
use App\Services\ConsumptionService;
use Illuminate\Http\JsonResponse;

class ConsumptionController extends Controller
{
    public function __construct(
        private readonly ConsumptionService $consumptionService,
    ) {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => $this->consumptionService->getAll(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreConsumptionRequest $request): JsonResponse
    {
        $consumption = $this->consumptionService->create(
            $request->validated(),
            optional($request->user())->id
        );

        return response()->json([
            'message' => 'Consumption created successfully.',
            'data' => $consumption,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        return response()->json([
            'data' => $this->consumptionService->getById((int) $id),
        ]);
    }
}
