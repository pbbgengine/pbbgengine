<?php

declare(strict_types=1);

namespace PbbgEngine\Stat\Models;

use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property string $name
 * @property string $model_type
 * @property string|null $class
 * @property Collection $data
 */
class Stat extends Model
{
    protected $fillable = ['name', 'model_type', 'class', 'data'];

    protected $casts = [
        'data' => AsCollection::class,
    ];
}
