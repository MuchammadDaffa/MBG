<?php

namespace Tests\Unit\Services;

use App\Models\GoodsReceipt;
use App\Repositories\Contracts\GoodsReceiptRepositoryInterface;
use App\Repositories\Contracts\InventoryLedgerRepositoryInterface;
use App\Services\GoodsReceiptService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
use PHPUnit\Framework\TestCase;

class GoodsReceiptServiceTest extends TestCase
{
    public function test_get_all_returns_collection(): void
    {
        $goodsReceiptRepository = new class implements GoodsReceiptRepositoryInterface {
            public function getAll(): Collection
            {
                return new Collection([
                    new GoodsReceipt(['document_no' => 'GR-001']),
                ]);
            }

            public function findById(int $id): ?GoodsReceipt
            {
                return null;
            }

            public function createWithLines(array $header, array $lines): GoodsReceipt
            {
                $receipt = new GoodsReceipt($header);
                $receipt->setAttribute('id', 1);
                $receipt->setRelation('lines', collect($lines));

                return $receipt;
            }
        };

        $ledgerRepository = new class implements InventoryLedgerRepositoryInterface {
            public function createMany(array $entries): void
            {
            }

            public function getBalance(int $locationId, int $itemId): float
            {
                return 0;
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

        $service = new GoodsReceiptService($goodsReceiptRepository, $ledgerRepository);

        $result = $service->getAll();

        $this->assertCount(1, $result);
        $this->assertSame('GR-001', $result->first()->document_no);
    }
}
