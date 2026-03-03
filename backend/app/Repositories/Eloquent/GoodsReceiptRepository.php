<?php

namespace App\Repositories\Eloquent;

use App\Models\GoodsReceipt;
use App\Repositories\Contracts\GoodsReceiptRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class GoodsReceiptRepository implements GoodsReceiptRepositoryInterface
{
    public function getAll(): Collection
    {
        return GoodsReceipt::query()
            ->with('lines')
            ->orderByDesc('trx_date')
            ->orderByDesc('id')
            ->get();
    }

    public function findById(int $id): ?GoodsReceipt
    {
        return GoodsReceipt::query()->with('lines')->find($id);
    }

    public function createWithLines(array $header, array $lines): GoodsReceipt
    {
        $receipt = GoodsReceipt::query()->create($header);
        $receipt->lines()->createMany($lines);

        return $receipt->load('lines');
    }
}
