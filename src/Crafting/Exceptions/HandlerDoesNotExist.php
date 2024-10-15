<?php

declare(strict_types=1);

namespace PbbgEngine\Crafting\Exceptions;

use Exception;

class HandlerDoesNotExist extends Exception
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
