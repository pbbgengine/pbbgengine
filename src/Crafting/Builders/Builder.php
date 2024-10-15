<?php

declare(strict_types=1);

namespace PbbgEngine\Crafting\Builders;

use Illuminate\Database\Eloquent\Model;
use PbbgEngine\Crafting\Models\Blueprint;

interface Builder
{
    /**
     * Builds the blueprint.
     *
     * The model is typically the owner of what is created by the blueprint.
     */
    public function build(Model $model, Blueprint $blueprint): bool;
}
