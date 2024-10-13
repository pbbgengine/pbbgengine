<?php

declare(strict_types=1);

namespace PbbgEngine\Item\Exceptions;

use Exception;

class InteractionDoesNotExist extends Exception
{
    public function __construct(string $interaction)
    {
        parent::__construct("interaction does not exist: $interaction");
    }
}
