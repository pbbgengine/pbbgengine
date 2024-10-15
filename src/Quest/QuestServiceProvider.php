<?php

declare(strict_types=1);

namespace PbbgEngine\Quest;

use Illuminate\Support\ServiceProvider;
use PbbgEngine\Crafting\CraftingService;
use PbbgEngine\Quest\Crafting\Conditions\HasCompletedQuest;
use PbbgEngine\Quest\Models\Quest;

class QuestServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->publishMigrations();

        // todo: only apply when crafting is enabled
        $service = app(CraftingService::class);
        $service->conditions[Quest::class] = HasCompletedQuest::class;
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
