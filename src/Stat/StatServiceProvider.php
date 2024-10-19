<?php

declare(strict_types=1);

namespace PbbgEngine\Stat;

use Illuminate\Support\ServiceProvider;
use PbbgEngine\Attribute\AttributeManager;

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
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(StatService::class);

        // todo: make this configurable
        $manager = app(AttributeManager::class);
        $manager->types['stats'] = StatService::class;
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
