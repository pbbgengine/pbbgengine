<?php

declare(strict_types=1);

namespace PbbgEngine\Quest\Exceptions;

use Exception;

class StageNotFound extends Exception
{
    public function __construct(int $id)
    {
        parent::__construct("stage not found $id");
    }
}
