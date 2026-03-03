<?php

namespace App\Repositories\Eloquent;

use App\Models\Item;
use App\Repositories\Contracts\ItemRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ItemRepository implements ItemRepositoryInterface
{
    public function getAll(): Collection
    {
        return Item::query()->orderBy('name')->get();
    }

    public function findById(int $id): ?Item
    {
        return Item::query()->find($id);
    }

    public function create(array $data): Item
    {
        return Item::query()->create($data);
    }

    public function update(Item $item, array $data): Item
    {
        $item->fill($data);
        $item->save();

        return $item;
    }

    public function delete(Item $item): void
    {
        $item->delete();
    }
}
