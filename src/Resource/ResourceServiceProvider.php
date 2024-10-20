<?php

declare(strict_types=1);

namespace PbbgEngine\Resource;

use Illuminate\Support\ServiceProvider;
use PbbgEngine\Attribute\AttributeManager;
use PbbgEngine\Attribute\Observers\AttributeObserver;
use PbbgEngine\Resource\Models\Resources;
use PbbgEngine\Stat\Models\Stats;

class ResourceServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->publishMigrations();

        // todo: make this configurable
        $manager = app(AttributeManager::class);
        $manager->types['resources'] = ResourceService::class;
        Resources::observe(AttributeObserver::class);
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ResourceService::class);
    }

    /**
     * Publish the stat table migrations.
     */
    public function publishMigrations(): void
    {
        $timestamp = date('Y_m_d_His');

        $this->publishes([
            __DIR__ . '/../../database/migrations/create_resources_table.php' => $this->app->databasePath("migrations/{$timestamp}_create_resources_table.php")
        ]);
    }
}
