<?php

namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;

interface InventoryLedgerRepositoryInterface
{
    public function createMany(array $entries): void;

    public function getBalance(int $locationId, int $itemId): float;

    public function getBalances(?int $locationId = null, ?int $itemId = null): Collection;

    public function getLowStocks(int $locationId): Collection;
}
