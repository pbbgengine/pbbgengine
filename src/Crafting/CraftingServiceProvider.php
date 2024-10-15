<?php

declare(strict_types=1);

namespace PbbgEngine\Crafting;

use Illuminate\Support\ServiceProvider;

class CraftingServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->publishMigrations();
    }

    /**
     * Publish the crafting table migrations.
     */
    public function publishMigrations(): void
    {
        $timestamp = date('Y_m_d_His');

        $this->publishes([
            __DIR__.'/../../database/migrations/create_crafting_tables.php' => $this->app->databasePath("migrations/{$timestamp}_create_crafting_tables.php")
        ]);
    }
}
