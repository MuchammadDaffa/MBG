<?php

namespace App\Providers;

use App\Repositories\Contracts\LocationRepositoryInterface;
use App\Repositories\Contracts\ConsumptionRepositoryInterface;
use App\Repositories\Contracts\GoodsReceiptRepositoryInterface;
use App\Repositories\Contracts\InventoryLedgerRepositoryInterface;
use App\Repositories\Contracts\ItemRepositoryInterface;
use App\Repositories\Contracts\RoleRepositoryInterface;
use App\Repositories\Contracts\StockMinimumRepositoryInterface;
use App\Repositories\Eloquent\ConsumptionRepository;
use App\Repositories\Eloquent\GoodsReceiptRepository;
use App\Repositories\Eloquent\InventoryLedgerRepository;
use App\Repositories\Eloquent\ItemRepository;
use App\Repositories\Eloquent\LocationRepository;
use App\Repositories\Eloquent\RoleRepository;
use App\Repositories\Eloquent\StockMinimumRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ConsumptionRepositoryInterface::class, ConsumptionRepository::class);
        $this->app->bind(GoodsReceiptRepositoryInterface::class, GoodsReceiptRepository::class);
        $this->app->bind(InventoryLedgerRepositoryInterface::class, InventoryLedgerRepository::class);
        $this->app->bind(ItemRepositoryInterface::class, ItemRepository::class);
        $this->app->bind(LocationRepositoryInterface::class, LocationRepository::class);
        $this->app->bind(RoleRepositoryInterface::class, RoleRepository::class);
        $this->app->bind(StockMinimumRepositoryInterface::class, StockMinimumRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
