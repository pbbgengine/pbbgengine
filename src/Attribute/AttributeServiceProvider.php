<?php

declare(strict_types=1);

namespace PbbgEngine\Attribute;

use Illuminate\Support\ServiceProvider;
use PbbgEngine\Attribute\Models\Attributes;
use PbbgEngine\Attribute\Observers\AttributeObserver;

class AttributeServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->publishMigrations();

        Attributes::observe(AttributeObserver::class);
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(AttributeManager::class);
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
