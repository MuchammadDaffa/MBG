<?php

namespace App\Services;

use App\Models\GoodsReceipt;
use App\Repositories\Contracts\GoodsReceiptRepositoryInterface;
use App\Repositories\Contracts\InventoryLedgerRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class GoodsReceiptService
{
    public function __construct(
        private readonly GoodsReceiptRepositoryInterface $goodsReceiptRepository,
        private readonly InventoryLedgerRepositoryInterface $inventoryLedgerRepository,
    ) {
    }

    public function getAll(): Collection
    {
        return $this->goodsReceiptRepository->getAll();
    }

    public function getById(int $id): GoodsReceipt
    {
        $receipt = $this->goodsReceiptRepository->findById($id);

        if (! $receipt) {
            throw (new ModelNotFoundException())->setModel(GoodsReceipt::class, [$id]);
        }

        return $receipt;
    }

    public function create(array $data, ?int $createdBy = null): GoodsReceipt
    {
        return DB::transaction(function () use ($data, $createdBy): GoodsReceipt {
            $documentNo = $this->generateDocumentNo();

            $header = [
                'trx_date' => $data['trx_date'],
                'document_no' => $documentNo,
                'location_id' => $data['location_id'],
                'notes' => $data['notes'] ?? null,
                'created_by' => $createdBy,
            ];

            $lines = array_map(function (array $line) use ($data): array {
                return [
                    'location_id' => $data['location_id'],
                    'item_id' => $line['item_id'],
                    'qty' => $line['qty'],
                    'unit_cost' => $line['unit_cost'] ?? 0,
                ];
            }, $data['lines']);

            $receipt = $this->goodsReceiptRepository->createWithLines($header, $lines);

            $now = now();
            $ledgerEntries = [];

            foreach ($receipt->lines as $line) {
                $ledgerEntries[] = [
                    'trx_date' => $receipt->trx_date,
                    'location_id' => $receipt->location_id,
                    'item_id' => $line->item_id,
                    'mutation_type' => 'receipt',
                    'qty_in' => $line->qty,
                    'qty_out' => 0,
                    'unit_cost' => $line->unit_cost,
                    'reference_type' => GoodsReceipt::class,
                    'reference_id' => $receipt->id,
                    'notes' => $receipt->notes,
                    'created_by' => $createdBy,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            $this->inventoryLedgerRepository->createMany($ledgerEntries);

            return $receipt;
        });
    }

    private function generateDocumentNo(): string
    {
        return 'GR-'.now()->format('Ymd-His').'-'.str()->upper(str()->random(4));
    }
}
