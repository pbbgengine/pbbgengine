<?php

declare(strict_types=1);

namespace PbbgEngine\Attribute\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int $id
 * @property string $model_type
 * @property int $model_id
 */
class Attributes extends Model
{
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
