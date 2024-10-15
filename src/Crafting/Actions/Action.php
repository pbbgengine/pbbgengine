<?php

declare(strict_types=1);

namespace PbbgEngine\Crafting\Actions;

use Illuminate\Database\Eloquent\Model;
use PbbgEngine\Crafting\Models\Component;

interface Action
{
    public function run(Model $model, Component $component): void;
}
