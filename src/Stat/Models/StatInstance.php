<?php

declare(strict_types=1);

namespace PbbgEngine\Stat\Models;

use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property string $model_type
 * @property int $model_id
 * @property Collection $data
 */
class StatInstance extends Model
{
    protected $fillable = [
        'model_type',
        'model_id',
        'data',
    ];

    protected $casts = [
        'data' => AsCollection::class,
    ];

    /**
     * Get the model that the stats belong to.
     *
     * @return MorphTo<Model, StatInstance>
     */
    public function model(): MorphTo
    {
        return $this->morphTo();
    }
}
