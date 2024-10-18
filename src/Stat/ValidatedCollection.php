<?php

declare(strict_types=1);

namespace PbbgEngine\Stat;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use PbbgEngine\Stat\Models\Stat;

class ValidatedCollection extends Collection
{
    public array $stats;

    public function __construct($items = [])
    {
        parent::__construct($items);
    }

    public function offsetSet($key, $value): void
    {
        if (isset($this->stats[$key])) {
            $value = (new $this->stats[$key])->validate($value);
        }

        parent::offsetSet($key, $value);
    }
}
