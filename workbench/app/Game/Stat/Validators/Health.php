<?php

declare(strict_types=1);

namespace Workbench\App\Game\Stat\Validators;

use PbbgEngine\Stat\Validators\Validator;

class Health implements Validator
{
    public function default(): int
    {
        return 100;
    }
}
