<?php

declare(strict_types=1);

namespace PbbgEngine\Attribute\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;
use PbbgEngine\Attribute\AttributeRegistry;
use PbbgEngine\Attribute\Exceptions\InvalidAttributeValidator;
use PbbgEngine\Attribute\Exceptions\InvalidAttributeTypeHandler;
use PbbgEngine\Attribute\Models\Attributes;
use PbbgEngine\Attribute\Validators\Validator;

/**
 * @mixin Model
 */
trait HasDynamicAttributes
{
    /**
     * Handle dynamic method calls to create attribute relations.
     *
     * @param string $method
     * @param array<int, mixed> $parameters
     * @return HasOne<Attributes>|mixed
     */
    public function __call($method, $parameters): mixed
    {
        $registry = app(AttributeRegistry::class);
        if (in_array($method, array_keys($registry->handlers))) {
            return $this->hasOne(Attributes::class, 'model_id', $this->primaryKey)
                ->where('name', $method)
                ->where('model_type', self::class);
        }

        return parent::__call($method, $parameters);
    }

    /**
     * Dynamically retrieve the related model's attributes by relation.
     *
     * @param $key
     * @return mixed
     * @throws InvalidAttributeValidator|InvalidAttributeTypeHandler
     */
    public function __get($key): mixed
    {
        $registry = app(AttributeRegistry::class);
        if (in_array($key, array_keys($registry->handlers))) {
            return $this->getDynamicAttribute($key);
        }

        return parent::__get($key);
    }

    /**
     * Dynamically retrieve the related model's attributes by relation.
     *
     * @param string $relation
     * @return Collection<string, mixed|null>
     * @throws InvalidAttributeValidator|InvalidAttributeTypeHandler
     */
    public function getDynamicAttribute(string $relation): Collection
    {
        $registry = app(AttributeRegistry::class);

        if (!isset($registry->handlers[$relation])) {
            throw new InvalidAttributeTypeHandler($this::class);
        }

        $service = $registry->handlers[$relation];

        if (!in_array(self::class, $service->booted)) {
            $service->bootObserver($this);
        }

        if (!isset($this->attributes[$relation])) {
            if (!isset($this->relations[$relation])) {
                $this->load($relation);
            }

            $this->attributes[$relation] = $this->relations[$relation]?->attribute;

            if ($this->attributes[$relation] === null) {
                $data = [];
                $defaultValues = $service->validators[$this::class] ?? [];

                foreach ($defaultValues as $stat => $class) {
                    if (!is_subclass_of($class, $service->validator)) {
                        throw new InvalidAttributeValidator($class);
                    }
                    /** @var Validator $validator */
                    $validator = new $class($this);
                    $data[$stat] = $validator->default();
                }

                $this->{$relation}()->create([
                    'name' => $relation,
                    'model_type' => self::class,
                    'model_id' => $this->{$this->primaryKey},
                    'attribute' => $data,
                ]);

                $this->unsetRelation($relation);
                $this->load($relation);

                /** @var Attributes $instance */
                $instance = $this->relations[$relation];
                $this->attributes[$relation] = $instance->attribute;
            }
        }

        return $this->attributes[$relation];
    }

    /**
     * Save the dynamically retrieved relation data.
     *
     * @param string $relation
     */
    public function saveDynamicRelation(string $relation): void
    {
        if (isset($this->relations[$relation])) {
            $this->relations[$relation]->save();
            unset($this->attributes[$relation]);
        }
    }
}
