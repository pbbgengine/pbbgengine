<?php

declare(strict_types=1);

namespace PbbgEngine\Stat;

use Illuminate\Database\Eloquent\Model;
use PbbgEngine\Stat\Validators\Validator;

class StatService
{
    /**
     * @var array<class-string<Model>, array<string, class-string<Validator>>>
     */
    public array $stats = [];

    /**
     * @var array<class-string<Model>>
     */
    public array $booted = [];

    public function bootObserver(Model $model): void
    {
        $model::observe(StatsObserver::class);
        $this->booted[] = $model::class;
    }
}
