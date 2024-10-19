<?php

declare(strict_types=1);

namespace PbbgEngine\Stat;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use PbbgEngine\Stat\Validators\Validator;

/**
 * @template TKey of array-key
 * @template TValue
 * @extends Collection<TKey, TValue>
 */
class ValidatedCollection extends Collection
{
    public string $model;

    /**
     * @param string $model
     * @param array<int|string, mixed> $items
     * @return ValidatedCollection<int|string, mixed>
     */
    public static function withModel(string $model, array $items = []): self
    {
        $instance = new self($items);
        $instance->model = $model;
        return $instance;
    }

    public function __construct($items = [])
    {
        parent::__construct($items);
    }

    /**
     * Performs validation on the value before setting it in the collection.
     *
     * @param TKey $key
     * @param TValue $value
     */
    public function offsetSet($key, $value): void
    {
        $service = app(StatService::class);
        if (isset($this->model) && isset($service->stats[$this->model][$key])) {
            /** @var Validator $validator */
            $validator = new $service->stats[$this->model][$key];
            $value = $validator->validate($value);
        }

        parent::offsetSet($key, $value);
    }
}
