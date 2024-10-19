<?php

declare(strict_types=1);

namespace PbbgEngine\Stat\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use PbbgEngine\Attribute\Exceptions\InvalidAttributeHandler;
use PbbgEngine\Stat\StatService;

/**
 * @template TKey of array-key
 * @template TValue
 * @extends Collection<TKey, TValue>
 */
class ValidatedCollection extends Collection
{
    public Model $model;

    /**
     * Creates a ValidatedCollection instance with the
     * model type to validate the stats against.
     *
     * @param Model $model
     * @param array<int|string, mixed> $items
     * @return ValidatedCollection<int|string, mixed>
     */
    public static function withModel(Model $model, array $items = []): self
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
     * @throws InvalidAttributeHandler
     */
    public function offsetSet($key, $value): void
    {
        $service = app(StatService::class);
        if (isset($this->model) && isset($service->handlers[$this->model::class][$key])) {
            if (!is_subclass_of($service->handlers[$this->model::class][$key], $service->handler)) {
                throw new InvalidAttributeHandler($service->handlers[$this->model::class][$key]);
            }
            $validator = new $service->handlers[$this->model::class][$key]($this->model);
            $value = $validator->validate($value);
        }

        parent::offsetSet($key, $value);
    }
}
