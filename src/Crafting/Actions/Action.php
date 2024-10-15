<?php

declare(strict_types=1);

namespace PbbgEngine\Crafting\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\MessageBag;
use PbbgEngine\Crafting\Models\Component;

abstract class Action
{
    public function __construct(protected MessageBag $messages) {}

    /**
     * Executes an action after all component conditions are satisfied.
     *
     * This method may define any additional actions that need to be performed
     * after crafting conditions are met, and before blueprint building occurs.
     */
    abstract public function run(Model $model, Component $component): void;
}
