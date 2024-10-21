<?php

declare(strict_types=1);

namespace PbbgEngine\Resource;

use Illuminate\Support\ServiceProvider;
use PbbgEngine\Attribute\AttributeManager;

class ResourceServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // todo: make this configurable
        $manager = app(AttributeManager::class);
        $manager->types['resources'] = ResourceService::class;
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ResourceService::class);
    }
}
