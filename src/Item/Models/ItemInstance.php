<?php

declare(strict_types=1);

namespace PbbgEngine\Item\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ItemInstance extends Model
{
    protected $fillable = [
        'model_type',
        'model_id',
        'item_id',
    ];

    /**
     * Get the underlying item model.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Get the model that owns the item instance.
     */
    public function model(): MorphTo
    {
        return $this->morphTo();
    }
}
