<?php

declare(strict_types=1);

namespace PbbgEngine\Crafting\Actions;

use Illuminate\Database\Eloquent\Model;
use PbbgEngine\Crafting\Models\Component;

interface Action
{
    /**
     * Executes an action after all component conditions are satisfied.
     *
     * This method may define any additional actions that need to be performed
     * after crafting conditions are met, and before blueprint building occurs.
     */
    public function run(Model $model, Component $component): void;
}
