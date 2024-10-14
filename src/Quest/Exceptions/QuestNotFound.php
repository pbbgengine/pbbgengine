<?php

declare(strict_types=1);

namespace PbbgEngine\Quest\Exceptions;

use Exception;

class QuestNotFound extends Exception
{
    public function __construct(int $id)
    {
        parent::__construct("quest not found $id");
    }
}
