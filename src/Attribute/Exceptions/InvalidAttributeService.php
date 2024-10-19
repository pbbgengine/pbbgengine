<?php

declare(strict_types=1);

namespace PbbgEngine\Attribute\Exceptions;

use Exception;

class InvalidAttributeService extends Exception
{
    public function __construct(string $class)
    {
        parent::__construct("missing attribute service: $class");
    }
}
