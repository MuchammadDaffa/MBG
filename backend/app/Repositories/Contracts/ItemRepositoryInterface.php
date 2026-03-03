<?php

namespace App\Repositories\Contracts;

use App\Models\Item;
use Illuminate\Database\Eloquent\Collection;

interface ItemRepositoryInterface
{
    public function getAll(): Collection;

    public function findById(int $id): ?Item;

    public function create(array $data): Item;

    public function update(Item $item, array $data): Item;

    public function delete(Item $item): void;
}
