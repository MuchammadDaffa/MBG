<?php

namespace App\Repositories\Contracts;

use App\Models\Consumption;
use Illuminate\Database\Eloquent\Collection;

interface ConsumptionRepositoryInterface
{
    public function getAll(): Collection;

    public function findById(int $id): ?Consumption;

    public function createWithLines(array $header, array $lines): Consumption;
}
