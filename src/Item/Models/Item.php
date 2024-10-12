<?php

declare(strict_types=1);

namespace PbbgEngine\Item\Models;

use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property string $name
 * @property Collection $data
 */
class Item extends Model
{
    protected $fillable = ['name', 'data'];

    protected $casts = [
        'data' => AsCollection::class,
    ];

    /**
     * Get the instances of the item.
     * @return HasMany<ItemInstance>
     */
    public function instances(): HasMany
    {
        return $this->hasMany(ItemInstance::class);
    }
}
