<?php

declare(strict_types=1);

namespace PbbgEngine\Resource\Models;

use Illuminate\Support\Collection;
use PbbgEngine\Attribute\Models\Attributes;
use PbbgEngine\Attribute\Support\AsValidatedAttributes;

/**
 * @property Collection $stats
 */
class Resources extends Attributes
{
    protected $fillable = [
        'model_type',
        'model_id',
        'resources',
    ];

    protected $casts = [
        'resources' => AsValidatedAttributes::class,
    ];
}
