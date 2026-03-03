<?php

namespace Tests\Unit\Services;

use App\Models\StockMinimum;
use App\Repositories\Contracts\InventoryLedgerRepositoryInterface;
use App\Repositories\Contracts\StockMinimumRepositoryInterface;
use App\Services\StockService;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

class StockServiceTest extends TestCase
{
    public function test_get_balances_returns_collection_from_repository(): void
    {
        $ledgerRepository = new class implements InventoryLedgerRepositoryInterface {
            public function createMany(array $entries): void
            {
            }

            public function getBalance(int $locationId, int $itemId): float
            {
                return 0;
            }

            public function getBalances(?int $locationId = null, ?int $itemId = null): Collection
            {
                return collect([
                    ['location_id' => 1, 'item_id' => 2, 'balance' => 10.0],
                ]);
            }

            public function getLowStocks(int $locationId): Collection
            {
                return collect();
            }
        };

        $stockMinimumRepository = new class implements StockMinimumRepositoryInterface {
            public function upsertMinimum(int $locationId, int $itemId, float $minQty): StockMinimum
            {
                return new StockMinimum([
                    'location_id' => $locationId,
                    'item_id' => $itemId,
                    'min_qty' => $minQty,
                ]);
            }
        };

        $service = new StockService($ledgerRepository, $stockMinimumRepository);
        $result = $service->getBalances(1, 2);

        $this->assertCount(1, $result);
        $this->assertSame(10.0, $result->first()['balance']);
    }
}
