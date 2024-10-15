<?php

declare(strict_types=1);

namespace PbbgEngine\Quest;

use Illuminate\Support\ServiceProvider;

class QuestServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->publishMigrations();
    }

    /**
     * Publish the quest table migrations.
     */
    public function publishMigrations(): void
    {
        $timestamp = date('Y_m_d_His');

        $this->publishes([
            __DIR__.'/../../database/migrations/create_quest_tables.php' => $this->app->databasePath("migrations/{$timestamp}_create_quest_tables.php")
        ]);
    }
}
