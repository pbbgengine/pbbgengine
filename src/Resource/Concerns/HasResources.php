<?php

declare(strict_types=1);

namespace PbbgEngine\Resource\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use PbbgEngine\Attribute\Concerns\HasDynamicAttributes;
use PbbgEngine\Resource\Models\Resources;

/**
 * @mixin Model
 */
trait HasResources
{
    use HasDynamicAttributes;

    /**
     * Get the resources relation for the model.
     *
     * @return HasOne<Resources>
     */
    public function resources(): HasOne
    {
        return $this->hasOne(Resources::class, 'model_id', $this->primaryKey)
            ->where('model_type', self::class);
    }
}
