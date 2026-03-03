<?php

namespace App\Repositories\Eloquent;

use App\Models\Consumption;
use App\Repositories\Contracts\ConsumptionRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ConsumptionRepository implements ConsumptionRepositoryInterface
{
    public function getAll(): Collection
    {
        return Consumption::query()
            ->with('lines')
            ->orderByDesc('trx_date')
            ->orderByDesc('id')
            ->get();
    }

    public function findById(int $id): ?Consumption
    {
        return Consumption::query()->with('lines')->find($id);
    }

    public function createWithLines(array $header, array $lines): Consumption
    {
        $consumption = Consumption::query()->create($header);
        $consumption->lines()->createMany($lines);

        return $consumption->load('lines');
    }
}
