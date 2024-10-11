<?php

declare(strict_types=1);

namespace PbbgEngine\Item\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $name
 */
class Item extends Model
{
    protected $fillable = ['name'];

    /**
     * Get the instances of the item.
     * @return HasMany<ItemInstance>
     */
    public function instances(): HasMany
    {
        return $this->hasMany(ItemInstance::class);
    }
}
