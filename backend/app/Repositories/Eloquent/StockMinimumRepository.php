<?php

namespace App\Repositories\Eloquent;

use App\Models\StockMinimum;
use App\Repositories\Contracts\StockMinimumRepositoryInterface;

class StockMinimumRepository implements StockMinimumRepositoryInterface
{
    public function upsertMinimum(int $locationId, int $itemId, float $minQty): StockMinimum
    {
        return StockMinimum::query()->updateOrCreate(
            [
                'location_id' => $locationId,
                'item_id' => $itemId,
            ],
            [
                'min_qty' => $minQty,
            ]
        );
    }
}
