<?php

declare(strict_types=1);

namespace PbbgEngine\Quest\Exceptions;

use Exception;

class InvalidTransition extends Exception
{
    public function __construct(string $class)
    {
        parent::__construct("invalid transition handler: $class");
    }
}
