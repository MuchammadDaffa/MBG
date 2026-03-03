<?php

namespace App\Services;

use App\Models\Item;
use App\Repositories\Contracts\ItemRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ItemService
{
    public function __construct(
        private readonly ItemRepositoryInterface $itemRepository,
    ) {
    }

    public function getAll(): Collection
    {
        return $this->itemRepository->getAll();
    }

    public function getById(int $id): Item
    {
        $item = $this->itemRepository->findById($id);

        if (! $item) {
            throw (new ModelNotFoundException())->setModel(Item::class, [$id]);
        }

        return $item;
    }

    public function create(array $data): Item
    {
        return $this->itemRepository->create($data);
    }

    public function update(int $id, array $data): Item
    {
        $item = $this->getById($id);

        return $this->itemRepository->update($item, $data);
    }

    public function delete(int $id): void
    {
        $item = $this->getById($id);
        $this->itemRepository->delete($item);
    }
}
