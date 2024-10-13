<?php

declare(strict_types=1);

namespace PbbgEngine\Item\Exceptions;

use Exception;

class InvalidInteraction extends Exception
{
    public function __construct(string $interaction)
    {
        parent::__construct("invalid interaction: $interaction");
    }
}
