<?php

declare(strict_types=1);

namespace PbbgEngine\Stat\Validators;

interface Validator
{
    public function validate(mixed $value): mixed;
    public function default(): mixed;
}
