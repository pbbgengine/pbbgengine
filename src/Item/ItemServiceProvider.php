<?php

declare(strict_types=1);

namespace PbbgEngine\Item;

use Illuminate\Support\ServiceProvider;
use PbbgEngine\Crafting\CraftingService;
use PbbgEngine\Item\Crafting\Conditions\HasItemToCraft;
use PbbgEngine\Item\Models\Item;

class ItemServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->publishMigrations();

        // todo: only apply when crafting is enabled
        $service = app(CraftingService::class);
        $service->conditions[Item::class] = HasItemToCraft::class;
    }

    /**
     * Publish the item table migrations.
     */
    public function publishMigrations(): void
    {
        $timestamp = date('Y_m_d_His');

        $this->publishes([
            __DIR__.'/../../database/migrations/create_item_tables.php' => $this->app->databasePath("migrations/{$timestamp}_create_item_tables.php")
        ]);
    }
}
