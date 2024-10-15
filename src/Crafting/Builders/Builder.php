<?php

declare(strict_types=1);

namespace PbbgEngine\Crafting\Builders;

use Illuminate\Database\Eloquent\Model;
use PbbgEngine\Crafting\Models\Blueprint;

interface Builder
{
    public function build(Model $model, Blueprint $blueprint): bool;
}
