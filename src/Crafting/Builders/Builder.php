<?php

declare(strict_types=1);

namespace PbbgEngine\Crafting\Builders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\MessageBag;
use PbbgEngine\Crafting\Models\Blueprint;

abstract class Builder
{
    public function __construct(protected MessageBag $messages) {}

    /**
     * Builds the blueprint.
     *
     * The model is typically the owner of what is created by the blueprint.
     */
    abstract public function build(Model $model, Blueprint $blueprint): void;
}
