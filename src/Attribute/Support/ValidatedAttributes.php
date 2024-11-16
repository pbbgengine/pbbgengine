<?php

declare(strict_types=1);

namespace PbbgEngine\Attribute\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use PbbgEngine\Attribute\AttributeRegistry;
use PbbgEngine\Attribute\AttributeTypeHandler;
use PbbgEngine\Attribute\Exceptions\InvalidAttributeValidator;
use PbbgEngine\Attribute\Models\Attributes;

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

    /**
     * Performs validation on the value before setting it in the collection.
     *
     * @param TKey $key
     * @param TValue $value
     * @throws InvalidAttributeValidator
     */
    public function offsetSet($key, $value): void
    {
        $registry = app(AttributeRegistry::class);
        /** @var AttributeTypeHandler $service */
        $service = $registry->handlers[$this->attributes->name];
        if (isset($this->model) && isset($service->validators[$this->model::class][$key])) {
            if (!is_subclass_of($service->validators[$this->model::class][$key], $service->validator)) {
                throw new InvalidAttributeValidator($service->validators[$this->model::class][$key]);
            }
            $validator = new $service->validators[$this->model::class][$key]($this->model);
            $value = $validator->validate($value);
        }

        parent::offsetSet($key, $value);
    }
}
