<?php

declare(strict_types=1);

namespace PbbgEngine\Crafting\Conditions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\MessageBag;
use PbbgEngine\Crafting\Models\Component;

abstract class Condition
{
    public function __construct(protected MessageBag $messages) {}

    /**
     * Checks if the given model satisfies the condition for the provided component.
     */
    abstract public function passes(Model $model, Component $component): void;
}
