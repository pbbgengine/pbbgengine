<?php

declare(strict_types=1);

namespace PbbgEngine\Stat\Exceptions;

use Exception;

class InvalidValidator extends Exception
{
    public function __construct(string $class)
    {
        parent::__construct("invalid stat validator: $class");
    }
}
