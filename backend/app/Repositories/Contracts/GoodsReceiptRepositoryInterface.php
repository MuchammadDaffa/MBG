<?php

namespace App\Repositories\Contracts;

use App\Models\GoodsReceipt;
use Illuminate\Database\Eloquent\Collection;

interface GoodsReceiptRepositoryInterface
{
    public function getAll(): Collection;

    public function findById(int $id): ?GoodsReceipt;

    public function createWithLines(array $header, array $lines): GoodsReceipt;
}
