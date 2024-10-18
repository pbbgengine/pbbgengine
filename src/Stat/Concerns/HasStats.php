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
     * @return HasOne<Stats>
     */
    public function stats(): HasOne
    {
        return $this->hasOne(Stats::class, 'model_id', $this->primaryKey)
            ->where('model_type', self::class);
    }

    /**
     * @return Collection<string, mixed|null>
     */
    public function getStatsAttribute(): Collection
    {
        if (!isset($this->attributes['stats'])) {
            if (!isset($this->relations['stats'])) {
                $this->load('stats');
            }

            $this->attributes['stats'] = $this->relations['stats']?->data;

            if ($this->attributes['stats'] === null) {
                $data = [];
                $defaultValues = app(StatService::class)->stats[$this::class] ?? [];
                foreach ($defaultValues as $stat => $class) {
                    if ($class) {
                        /** @var Validator $validator */
                        $validator = new $class;
                        $data[$stat] = $validator->default();
                    }
                }

                $this->stats()->create([
                    'model_type' => self::class,
                    'model_id' => $this->{$this->primaryKey},
                    'data' => $data,
                ]);
                $this->unsetRelation('stats');
                $this->load('stats');
                /** @var Stats $instance */
                $instance = $this->relations['stats'];
                $this->attributes['stats'] = $instance->data;
            }
        }

        return $this->attributes['stats'];
    }

    // todo: replace with some kind of observer, not sustainable to override the save method
    public function save(array $options = [])
    {
        if (isset($this->relations['stats'])) {
            $this->relations['stats']->save();
            unset($this->attributes['stats']);
        }

        return parent::save($options);
    }
}