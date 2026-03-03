<?php

namespace App\Repositories\Eloquent;

use App\Models\Location;
use App\Repositories\Contracts\LocationRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class LocationRepository implements LocationRepositoryInterface
{
    public function getAll(): Collection
    {
        return Location::query()
            ->orderBy('name')
            ->get();
    }

    public function findById(int $id): ?Location
    {
        return Location::query()->find($id);
    }

    public function create(array $data): Location
    {
        return Location::query()->create($data);
    }

    public function update(Location $location, array $data): Location
    {
        $location->fill($data);
        $location->save();

        return $location;
    }

    public function delete(Location $location): void
    {
        $location->delete();
    }
}
