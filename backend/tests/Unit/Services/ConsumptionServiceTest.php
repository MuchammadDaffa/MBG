<?php

namespace Tests\Unit\Services;

use App\Models\Consumption;
use App\Repositories\Contracts\ConsumptionRepositoryInterface;
use App\Repositories\Contracts\InventoryLedgerRepositoryInterface;
use App\Services\ConsumptionService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use PHPUnit\Framework\TestCase;

class ConsumptionServiceTest extends TestCase
{
    public function test_get_by_id_throws_exception_when_consumption_not_found(): void
    {
        $consumptionRepository = new class implements ConsumptionRepositoryInterface {
            public function getAll(): Collection
            {
                return new Collection();
            }

            public function findById(int $id): ?Consumption
            {
                return null;
            }

            public function createWithLines(array $header, array $lines): Consumption
            {
                return new Consumption($header);
            }
        };

        $ledgerRepository = new class implements InventoryLedgerRepositoryInterface {
            public function createMany(array $entries): void
            {
            }

            public function getBalance(int $locationId, int $itemId): float
            {
                return 2.00;
            }

            public function getBalances(?int $locationId = null, ?int $itemId = null): SupportCollection
            {
                return collect();
            }

            public function getLowStocks(int $locationId): SupportCollection
            {
                return collect();
            }
        };

        $service = new ConsumptionService($consumptionRepository, $ledgerRepository);

        $this->expectException(ModelNotFoundException::class);
        $service->getById(999);
    }
}
