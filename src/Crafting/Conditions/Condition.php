<?php

declare(strict_types=1);

namespace PbbgEngine\Crafting\Conditions;

use Illuminate\Database\Eloquent\Model;
use PbbgEngine\Crafting\Models\Component;

interface Condition
{
    public function passes(Model $model, Component $component): bool;
}
