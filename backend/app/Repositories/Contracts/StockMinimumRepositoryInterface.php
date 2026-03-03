<?php

namespace App\Repositories\Contracts;

use App\Models\StockMinimum;

interface StockMinimumRepositoryInterface
{
    public function upsertMinimum(int $locationId, int $itemId, float $minQty): StockMinimum;
}
