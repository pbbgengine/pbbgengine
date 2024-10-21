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
        // todo: make this configurable
        $manager = app(AttributeManager::class);
        $manager->types['stats'] = StatService::class;
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(StatService::class);
    }
}
