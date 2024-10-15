<?php

declare(strict_types=1);

namespace PbbgEngine\Quest\Exceptions;

use Exception;

class UnknownTransition extends Exception
{
    public function __construct(string $class)
    {
        parent::__construct("unknown transition type: $class");
    }
}
