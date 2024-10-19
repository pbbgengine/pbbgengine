<?php

declare(strict_types=1);

namespace PbbgEngine\Attribute\Exceptions;

use Exception;

class InvalidAttributeHandler extends Exception
{
    public function __construct(string $class)
    {
        parent::__construct("invalid attribute validator: $class");
    }
}
