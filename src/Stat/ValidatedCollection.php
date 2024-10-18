<?php

declare(strict_types=1);

namespace PbbgEngine\Stat;

use Illuminate\Support\Collection;
use PbbgEngine\Stat\Models\Stat;
use PbbgEngine\Stat\Validators\Validator;

/**
 * @template TKey of array-key
 * @template TValue
 * @extends Collection<TKey, TValue>
 */
class ValidatedCollection extends Collection
{
    /**
     * @var array<string, mixed>
     */
    public array $stats;

    public function __construct($items = [])
    {
        parent::__construct($items);
    }

    public function offsetSet($key, $value): void
    {
        if (isset($this->stats[$key])) {
            /** @var Validator $validator */
            $validator = new $this->stats[$key];
            $value = $validator->validate($value);
        }

        parent::offsetSet($key, $value);
    }
}
