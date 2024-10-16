<?php

declare(strict_types=1);

namespace PbbgEngine\Stat\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;
use PbbgEngine\Stat\Models\StatInstance;

/**
 * @mixin Model
 */
trait HasStats
{
    /**
     * @return HasOne<StatInstance>
     */
    public function statInstance(): HasOne
    {
        return $this->hasOne(StatInstance::class, 'model_id', $this->primaryKey)
            ->where('model_type', self::class);
    }

    /**
     * @return Collection<string, mixed|null>
     */
    public function getStatsAttribute(): Collection
    {
        if (!isset($this->attributes['stats'])) {
            $this->attributes['stats'] = $this->statInstance?->data;

            if ($this->attributes['stats'] === null) {
                $this->statInstance()->create([
                    'model_type' => self::class,
                    'model_id' => $this->{$this->primaryKey},
                    'data' => [],
                ]);
                $this->unsetRelation('statInstance');
                /** @var StatInstance $instance */
                $instance = $this->statInstance;
                $this->attributes['stats'] = $instance->data;
            }
        }

        return $this->attributes['stats'];
    }

    // todo: replace with some kind of observer, not sustainable to override the save method
    public function save(array $options = [])
    {
        if (array_key_exists('stats', $this->attributes) && $this->relationLoaded('statInstance') && $this->statInstance) {
            $this->statInstance->save();
            unset($this->attributes['stats']);
        }

        return parent::save($options);
    }
}
