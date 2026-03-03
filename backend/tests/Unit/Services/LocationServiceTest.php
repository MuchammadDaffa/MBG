<?php

namespace Tests\Unit\Services;

use App\Models\Location;
use App\Repositories\Contracts\LocationRepositoryInterface;
use App\Services\LocationService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use PHPUnit\Framework\TestCase;

class LocationServiceTest extends TestCase
{
    public function test_get_all_returns_collection_from_repository(): void
    {
        $repository = new class implements LocationRepositoryInterface {
            public function getAll(): Collection
            {
                return new Collection([
                    new Location(['code' => 'LOC-001', 'name' => 'Titik A']),
                ]);
            }

            public function findById(int $id): ?Location
            {
                return null;
            }

            public function create(array $data): Location
            {
                return new Location($data);
            }

            public function update(Location $location, array $data): Location
            {
                return $location;
            }

            public function delete(Location $location): void
            {
            }
        };

        $service = new LocationService($repository);
        $locations = $service->getAll();

        $this->assertCount(1, $locations);
        $this->assertSame('LOC-001', $locations->first()->code);
    }

    public function test_get_by_id_throws_exception_when_location_not_found(): void
    {
        $repository = new class implements LocationRepositoryInterface {
            public function getAll(): Collection
            {
                return new Collection();
            }

            public function findById(int $id): ?Location
            {
                return null;
            }

            public function create(array $data): Location
            {
                return new Location($data);
            }

            public function update(Location $location, array $data): Location
            {
                return $location;
            }

            public function delete(Location $location): void
            {
            }
        };

        $service = new LocationService($repository);

        $this->expectException(ModelNotFoundException::class);
        $service->getById(999);
    }
}
