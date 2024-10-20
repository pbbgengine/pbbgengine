<?php

declare(strict_types=1);

namespace PbbgEngine\Stat\Models;

use Illuminate\Support\Collection;
use PbbgEngine\Attribute\Models\Attributes;
use PbbgEngine\Attribute\Support\AsValidatedAttributes;

/**
 * @property Collection $stats
 */
class Stats extends Attributes
{
    protected $fillable = [
        'model_type',
        'model_id',
        'stats',
    ];

    protected $casts = [
        'stats' => AsValidatedAttributes::class,
    ];
}
