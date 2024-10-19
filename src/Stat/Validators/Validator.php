<?php

declare(strict_types=1);

namespace PbbgEngine\Stat\Validators;

use Illuminate\Database\Eloquent\Model;

abstract class Validator
{
    /**
     * Creates a new instance of the validator
     * with the model as context if needed.
     */
    public function __construct(public Model $model) {}

    /**
     * Validates the value and returns it.
     */
    abstract public function validate(mixed $value): mixed;

    /**
     * Returns the default value for the stat.
     */
    abstract public function default(): mixed;
}
