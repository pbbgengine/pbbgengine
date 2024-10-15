<?php

declare(strict_types=1);

namespace PbbgEngine\Crafting\Exceptions;

use Exception;
use PbbgEngine\Crafting\Models\Blueprint;

class HasNoComponents extends Exception
{
    public function __construct(Blueprint $blueprint)
    {
        parent::__construct("attempted to craft blueprint $blueprint->id which has no components");
    }
}
