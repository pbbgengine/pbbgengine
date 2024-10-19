<?php

declare(strict_types=1);

namespace PbbgEngine\Stat\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use PbbgEngine\Attribute\Concerns\HasDynamicAttributes;
use PbbgEngine\Stat\Models\Stats;

/**
 * @mixin Model
 */
trait HasStats
{
    use HasDynamicAttributes;

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
}
