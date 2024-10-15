<?php

declare(strict_types=1);

namespace PbbgEngine\Crafting\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int $id
 * @property string $name
 * @property string $model_type
 * @property int $model_id
 */
class Blueprint extends Model
{
    protected $fillable = ['name', 'model_type', 'model_id'];

    /**
     * Get the components of the blueprint.
     *
     * @return HasMany<Component>
     */
    public function components(): HasMany
    {
        return $this->hasMany(Component::class);
    }

    /**
     * Get the model is crafted using the blueprint.
     *
     * @return MorphTo<Model, Blueprint>
     */
    public function model(): MorphTo
    {
        return $this->morphTo();
    }
}