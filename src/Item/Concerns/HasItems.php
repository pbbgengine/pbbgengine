<?php

declare(strict_types=1);

namespace PbbgEngine\Item\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use PbbgEngine\Item\Models\ItemInstance;

/**
 * @mixin Model
 */
trait HasItems
{
    /**
     * Get item instances that belong to this model.
     * @return HasMany<ItemInstance>
     */
    public function items(): HasMany
    {
        return $this->hasMany(ItemInstance::class, 'model_id', $this->primaryKey)
            ->where('model_type', self::class)
            ->with('item');
    }
}
