<?php

namespace App\Services;

use App\Models\Location;
use App\Repositories\Contracts\LocationRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class LocationService
{
    public function __construct(
        private readonly LocationRepositoryInterface $locationRepository,
    ) {
    }

    public function getAll(): Collection
    {
        return $this->locationRepository->getAll();
    }

    public function getById(int $id): Location
    {
        $location = $this->locationRepository->findById($id);

        if (! $location) {
            throw (new ModelNotFoundException())->setModel(Location::class, [$id]);
        }

        return $location;
    }

    public function create(array $data): Location
    {
        return $this->locationRepository->create($data);
    }

    public function update(int $id, array $data): Location
    {
        $location = $this->getById($id);

        return $this->locationRepository->update($location, $data);
    }

    public function delete(int $id): void
    {
        $location = $this->getById($id);
        $this->locationRepository->delete($location);
    }
}
