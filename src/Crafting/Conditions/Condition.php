<?php

declare(strict_types=1);

namespace PbbgEngine\Crafting\Conditions;

use Illuminate\Database\Eloquent\Model;
use PbbgEngine\Crafting\Models\Component;

interface Condition
{
    /**
     * Checks if the given model satisfies the condition for the provided component.
     */
    public function passes(Model $model, Component $component): bool;
}
