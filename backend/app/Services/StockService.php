<?php

namespace App\Services;

use App\Models\StockMinimum;
use App\Repositories\Contracts\InventoryLedgerRepositoryInterface;
use App\Repositories\Contracts\StockMinimumRepositoryInterface;
use Illuminate\Support\Collection;

class StockService
{
    public function __construct(
        private readonly InventoryLedgerRepositoryInterface $inventoryLedgerRepository,
        private readonly StockMinimumRepositoryInterface $stockMinimumRepository,
    ) {
    }

    public function getBalances(?int $locationId = null, ?int $itemId = null): Collection
    {
        return $this->inventoryLedgerRepository->getBalances($locationId, $itemId);
    }

    public function getLowStocks(int $locationId): Collection
    {
        return $this->inventoryLedgerRepository->getLowStocks($locationId);
    }

    public function setMinimum(int $locationId, int $itemId, float $minQty): StockMinimum
    {
        return $this->stockMinimumRepository->upsertMinimum($locationId, $itemId, $minQty);
    }
}
