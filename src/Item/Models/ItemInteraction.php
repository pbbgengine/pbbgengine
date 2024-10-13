<?php

declare(strict_types=1);

namespace PbbgEngine\Item\Models;

use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property int $item_id
 * @property string $class
 * @property Collection $data
 */
class ItemInteraction extends Model
{
    protected $fillable = [
        'item_id',
        'class',
        'data',
    ];

    protected $casts = [
        'data' => AsCollection::class,
    ];

    /**
     * Get the items that have this interaction type.
     *
     * @return HasMany<Item>
     */
    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }
}
