<?php

declare(strict_types=1);

namespace PbbgEngine\Stat;

use Illuminate\Database\Eloquent\Model;
use PbbgEngine\Stat\Models\Stat;
use PbbgEngine\Stat\Validators\Validator;

class StatService
{
    /**
     * @var array<class-string<Model>, array<string, class-string<Validator>>>
     */
    public array $stats = [];

    public function __construct()
    {
        $this->load();
    }

    public function load(): void
    {
        /** @var array<class-string<Model>, array<string, class-string<Validator>>> $stats */
        $stats = Stat::all()->groupBy('model_type')->mapWithKeys(function ($stats, $modelType) {
            return [$modelType => $stats->mapWithKeys(function ($stat) {
                return [$stat->name => $stat->class];
            })];
        })->toArray();
        $this->stats = $stats;
    }
}
