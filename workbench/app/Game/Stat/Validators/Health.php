<?php

declare(strict_types=1);

namespace Workbench\App\Game\Stat\Validators;

use PbbgEngine\Attribute\Validators\Validator;

class Health extends Validator
{
    public int $min = 0;
    public int $max = 100;

    public function validate(mixed $value): mixed
    {
        if ($value > $this->max) {
            return $this->max;
        }
        if ($value < $this->min) {
            return $this->min;
        }
        return $value;
    }

    public function default(): int
    {
        return $this->max;
    }
}
