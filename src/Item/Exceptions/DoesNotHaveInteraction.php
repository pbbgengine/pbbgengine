<?php

declare(strict_types=1);

namespace PbbgEngine\Item\Exceptions;

use Exception;
use Illuminate\Database\Eloquent\Model;

class DoesNotHaveInteraction extends Exception
{
    public function __construct(Model $model, string $interaction)
    {
        /** @var int $pk */
        $pk = $model->getAttribute($model->getKeyName());
        parent::__construct(sprintf("%s:%d does not have interaction: %s", $model::class, $pk, $interaction));
    }
}
