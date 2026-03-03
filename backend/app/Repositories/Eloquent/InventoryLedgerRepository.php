<?php

namespace App\Repositories\Eloquent;

use App\Models\InventoryLedger;
use App\Repositories\Contracts\InventoryLedgerRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class InventoryLedgerRepository implements InventoryLedgerRepositoryInterface
{
    public function createMany(array $entries): void
    {
        InventoryLedger::query()->insert($entries);
    }

    public function getBalance(int $locationId, int $itemId): float
    {
        $aggregate = InventoryLedger::query()
            ->where('location_id', $locationId)
            ->where('item_id', $itemId)
            ->selectRaw('COALESCE(SUM(qty_in), 0) as total_in, COALESCE(SUM(qty_out), 0) as total_out')
            ->first();

        $totalIn = (float) ($aggregate?->total_in ?? 0);
        $totalOut = (float) ($aggregate?->total_out ?? 0);

        return $totalIn - $totalOut;
    }

    public function getBalances(?int $locationId = null, ?int $itemId = null): Collection
    {
        $query = InventoryLedger::query()
            ->join('items', 'items.id', '=', 'inventory_ledgers.item_id')
            ->join('locations', 'locations.id', '=', 'inventory_ledgers.location_id')
            ->selectRaw('inventory_ledgers.location_id')
            ->selectRaw('locations.code as location_code')
            ->selectRaw('locations.name as location_name')
            ->selectRaw('inventory_ledgers.item_id')
            ->selectRaw('items.sku as item_sku')
            ->selectRaw('items.name as item_name')
            ->selectRaw('COALESCE(SUM(inventory_ledgers.qty_in), 0) - COALESCE(SUM(inventory_ledgers.qty_out), 0) as balance')
            ->groupBy(
                'inventory_ledgers.location_id',
                'locations.code',
                'locations.name',
                'inventory_ledgers.item_id',
                'items.sku',
                'items.name',
            )
            ->orderBy('locations.name')
            ->orderBy('items.name');

        if ($locationId) {
            $query->where('inventory_ledgers.location_id', $locationId);
        }

        if ($itemId) {
            $query->where('inventory_ledgers.item_id', $itemId);
        }

        return $query->get();
    }

    public function getLowStocks(int $locationId): Collection
    {
        $balanceSubQuery = InventoryLedger::query()
            ->selectRaw('location_id, item_id, COALESCE(SUM(qty_in), 0) - COALESCE(SUM(qty_out), 0) as balance')
            ->groupBy('location_id', 'item_id');

        return DB::query()
            ->from('stock_minimums')
            ->join('items', 'items.id', '=', 'stock_minimums.item_id')
            ->join('locations', 'locations.id', '=', 'stock_minimums.location_id')
            ->leftJoinSub($balanceSubQuery, 'balances', function ($join): void {
                $join->on('balances.location_id', '=', 'stock_minimums.location_id')
                    ->on('balances.item_id', '=', 'stock_minimums.item_id');
            })
            ->where('stock_minimums.location_id', $locationId)
            ->whereRaw('COALESCE(balances.balance, 0) <= stock_minimums.min_qty')
            ->selectRaw('stock_minimums.location_id')
            ->selectRaw('locations.code as location_code')
            ->selectRaw('locations.name as location_name')
            ->selectRaw('stock_minimums.item_id')
            ->selectRaw('items.sku as item_sku')
            ->selectRaw('items.name as item_name')
            ->selectRaw('stock_minimums.min_qty')
            ->selectRaw('COALESCE(balances.balance, 0) as balance')
            ->orderBy('items.name')
            ->get();
    }
}
