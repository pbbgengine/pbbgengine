<?php

declare(strict_types=1);

namespace PbbgEngine\Attribute\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use PbbgEngine\Attribute\AttributeManager;
use PbbgEngine\Attribute\AttributeService;
use PbbgEngine\Attribute\Exceptions\InvalidAttributeHandler;
use PbbgEngine\Attribute\Models\Attributes;
use PbbgEngine\Stat\Validators\Validator;

/**
 * @template TKey of array-key
 * @template TValue
 * @extends Collection<TKey, TValue>
 */
class ValidatedAttributes extends Collection
{
    public Model $model;
    public Attributes $attributes;

    /**
     * Creates a ValidatedAttributes instance with the
     * model to validate the stats against.
     *
     * @param Attributes $model
     * @param array<int|string, mixed> $items
     * @return ValidatedAttributes<int|string, mixed>
     */
    public static function withModel(Attributes $model, array $items = []): self
    {
        $instance = new self($items);
        $instance->attributes = $model;
        $instance->model = $model->model;
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
        $manager = app(AttributeManager::class);
        /** @var AttributeService<Validator, object> $service */
        $service = app($manager->types[$this->attributes->getTable()]);
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
