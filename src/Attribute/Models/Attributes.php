<?php

declare(strict_types=1);

namespace PbbgEngine\Attribute\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use PbbgEngine\Attribute\Support\AsValidatedAttributes;
use PbbgEngine\Attribute\Support\ValidatedAttributes;

/**
 * @property int $id
 * @property string $name
 * @property string $model_type
 * @property int $model_id
 * @property ValidatedAttributes $attribute
 */
class Attributes extends Model
{
    protected $fillable = [
        'name',
        'model_type',
        'model_id',
        'attribute',
    ];

    protected $casts = [
        'attribute' => AsValidatedAttributes::class,
    ];

    /**
     * Get the model that the attributes belong to.
     *
     * @return MorphTo<Model, Attributes>
     */
    public function model(): MorphTo
    {
        return $this->morphTo();
    }
}
