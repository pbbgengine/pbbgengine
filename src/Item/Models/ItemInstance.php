<?php

declare(strict_types=1);

namespace PbbgEngine\Item\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property string $model_type
 * @property int $model_id
 * @property int $item_id
 */
class ItemInstance extends Model
{
    protected $fillable = [
        'model_type',
        'model_id',
        'item_id',
    ];

    /**
     * Get the underlying item model.
     * @return BelongsTo<Item, ItemInstance>
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Get the model that owns the item instance.
     * @return MorphTo<Model, ItemInstance>
     */
    public function model(): MorphTo
    {
        return $this->morphTo();
    }
}
