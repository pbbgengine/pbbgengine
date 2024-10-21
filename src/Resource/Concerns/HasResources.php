<?php

declare(strict_types=1);

namespace PbbgEngine\Resource\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use PbbgEngine\Attribute\Concerns\HasDynamicAttributes;
use PbbgEngine\Attribute\Models\Attributes;

/**
 * @mixin Model
 */
trait HasResources
{
    use HasDynamicAttributes;

    /**
     * Get the resources relation for the model.
     *
     * @return HasOne<Attributes>
     */
    public function resources(): HasOne
    {
        return $this->hasOne(Attributes::class, 'model_id', $this->primaryKey)
            ->where('name', 'resources')
            ->where('model_type', self::class);
    }
}
