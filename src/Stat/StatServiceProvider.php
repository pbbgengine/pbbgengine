<?php

declare(strict_types=1);

namespace PbbgEngine\Stat;

use Illuminate\Support\ServiceProvider;

class StatServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->publishMigrations();
    }

    /**
     * Publish the stat table migrations.
     */
    public function publishMigrations(): void
    {
        $timestamp = date('Y_m_d_His');

        $this->publishes([
            __DIR__.'/../../database/migrations/create_stat_tables.php' => $this->app->databasePath("migrations/{$timestamp}_create_stat_tables.php")
        ]);
    }
}
