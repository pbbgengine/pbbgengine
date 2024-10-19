<?php

declare(strict_types=1);

namespace PbbgEngine\Attribute;

use Illuminate\Support\ServiceProvider;

class AttributeServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(AttributeManager::class);
    }
}
