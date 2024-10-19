<?php

declare(strict_types=1);

namespace PbbgEngine\Stat;

use Illuminate\Database\Eloquent\Model;
use PbbgEngine\Stat\Validators\Validator;

class StatService
{
    /**
     * The stats applied to each model.
     *
     * e.g. [User::class => ['health' => HealthValidator::class]]
     *
     * @var array<class-string<Model>, array<string, class-string<Validator>>>
     */
    public array $stats = [];

    /**
     * The models that have had their stat observers booted.
     *
     * @var array<class-string<Model>>
     */
    public array $booted = [];

    /**
     * Boots the stat observer for the given model.
     * Called when stats are accessed for the first time on a HasStats model.
     */
    public function bootObserver(Model $model): void
    {
        $model::observe(StatsObserver::class);
        $this->booted[] = $model::class;
    }
}
