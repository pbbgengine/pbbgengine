<?php

declare(strict_types=1);

namespace PbbgEngine\Quest\Exceptions;

use Exception;
use Illuminate\Database\Eloquent\Model;

class QuestsUnsupported extends Exception
{
    public function __construct(Model $model)
    {
        parent::__construct(sprintf("%s does not support quests", $model::class));
    }
}
