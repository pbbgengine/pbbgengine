<?php

declare(strict_types=1);

namespace PbbgEngine\Stat\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;
use PbbgEngine\Stat\Models\Stats;
use PbbgEngine\Stat\StatService;
use PbbgEngine\Stat\Validators\Validator;

/**
 * @mixin Model
 */
trait HasStats
{
    /**
     * Get the stats relation for the model.
     *
     * @return HasOne<Stats>
     */
    public function stats(): HasOne
    {
        return $this->hasOne(Stats::class, 'model_id', $this->primaryKey)
            ->where('model_type', self::class);
    }

    /**
     * Get the stats attribute for the model from the associated stats instance.
     * Creates the stats instance and populates it if it does not exist.
     *
     * @return Collection<string, mixed|null>
     */
    public function getStatsAttribute(): Collection
    {
        /** @var StatService $service */
        $service = app(StatService::class);
        if (!in_array(self::class, $service->booted)) {
            $service->bootObserver($this);
        }

        if (!isset($this->attributes['stats'])) {
            if (!isset($this->relations['stats'])) {
                $this->load('stats');
            }

            $this->attributes['stats'] = $this->relations['stats']?->stats;

            if ($this->attributes['stats'] === null) {
                $data = [];
                $defaultValues = app(StatService::class)->stats[$this::class] ?? [];
                foreach ($defaultValues as $stat => $class) {
                    if (is_subclass_of($class, Validator::class)) {
                        $validator = new $class;
                        $data[$stat] = $validator->default();
                    }
                }

                $this->stats()->create([
                    'model_type' => self::class,
                    'model_id' => $this->{$this->primaryKey},
                    'stats' => $data,
                ]);
                $this->unsetRelation('stats');
                $this->load('stats');
                /** @var Stats $instance */
                $instance = $this->relations['stats'];
                $this->attributes['stats'] = $instance->stats;
            }
        }

        return $this->attributes['stats'];
    }

    /**
     * Saves the stats for the model.
     * Unassigns the stats attribute from the model.
     */
    public function saveStats(): void
    {
        if (isset($this->relations['stats'])) {
            $this->relations['stats']->save();
        }
        unset($this->attributes['stats']);
    }
}
