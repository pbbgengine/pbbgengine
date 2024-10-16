<?php

declare(strict_types=1);

namespace PbbgEngine\Crafting\Models;

use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property int $blueprint_id
 * @property string $model_type
 * @property int $model_id
 * @property Collection $data
 */
class Component extends Model
{
    protected $fillable = ['blueprint_id', 'model_type', 'model_id', 'data'];

    protected $casts = [
        'data' => AsCollection::class,
    ];

    /**
     * Get the blueprint of this component.
     *
     * @return BelongsTo<Blueprint, Component>
     */
    public function blueprint(): BelongsTo
    {
        return $this->belongsTo(Blueprint::class);
    }

    /**
     * Get the model that represents the component.
     *
     * @return MorphTo<Model, Component>
     */
    public function model(): MorphTo
    {
        return $this->morphTo();
    }
}
