<?php

namespace App\Services;

use App\Models\Consumption;
use App\Repositories\Contracts\ConsumptionRepositoryInterface;
use App\Repositories\Contracts\InventoryLedgerRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ConsumptionService
{
    public function __construct(
        private readonly ConsumptionRepositoryInterface $consumptionRepository,
        private readonly InventoryLedgerRepositoryInterface $inventoryLedgerRepository,
    ) {
    }

    public function getAll(): Collection
    {
        return $this->consumptionRepository->getAll();
    }

    public function getById(int $id): Consumption
    {
        $consumption = $this->consumptionRepository->findById($id);

        if (! $consumption) {
            throw (new ModelNotFoundException())->setModel(Consumption::class, [$id]);
        }

        return $consumption;
    }

    public function create(array $data, ?int $createdBy = null): Consumption
    {
        return DB::transaction(function () use ($data, $createdBy): Consumption {
            foreach ($data['lines'] as $line) {
                $balance = $this->inventoryLedgerRepository->getBalance((int) $data['location_id'], (int) $line['item_id']);

                if ($balance < (float) $line['qty']) {
                    throw new InvalidArgumentException(
                        sprintf(
                            'Insufficient stock for item_id %d at location_id %d. Available: %.2f, Requested: %.2f',
                            (int) $line['item_id'],
                            (int) $data['location_id'],
                            $balance,
                            (float) $line['qty'],
                        )
                    );
                }
            }

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
                ];
            }, $data['lines']);

            $consumption = $this->consumptionRepository->createWithLines($header, $lines);

            $now = now();
            $ledgerEntries = [];

            foreach ($consumption->lines as $line) {
                $ledgerEntries[] = [
                    'trx_date' => $consumption->trx_date,
                    'location_id' => $consumption->location_id,
                    'item_id' => $line->item_id,
                    'mutation_type' => 'consumption',
                    'qty_in' => 0,
                    'qty_out' => $line->qty,
                    'unit_cost' => 0,
                    'reference_type' => Consumption::class,
                    'reference_id' => $consumption->id,
                    'notes' => $consumption->notes,
                    'created_by' => $createdBy,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            $this->inventoryLedgerRepository->createMany($ledgerEntries);

            return $consumption;
        });
    }

    private function generateDocumentNo(): string
    {
        return 'CS-'.now()->format('Ymd-His').'-'.str()->upper(str()->random(4));
    }
}
