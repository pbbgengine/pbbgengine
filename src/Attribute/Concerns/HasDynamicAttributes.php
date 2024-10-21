<?php

declare(strict_types=1);

namespace PbbgEngine\Attribute\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use PbbgEngine\Attribute\AttributeManager;
use PbbgEngine\Attribute\AttributeService;
use PbbgEngine\Attribute\Exceptions\InvalidAttributeHandler;
use PbbgEngine\Attribute\Exceptions\InvalidAttributeService;
use PbbgEngine\Attribute\Models\Attributes;
use PbbgEngine\Attribute\Validators\Validator;

/**
 * @mixin Model
 */
trait HasDynamicAttributes
{
    /**
     * @param $key
     * @return mixed
     * @throws InvalidAttributeHandler|InvalidAttributeService
     */
    public function __get($key): mixed
    {
        $manager = app(AttributeManager::class);
        if (isset($manager->types[$key]) && method_exists($this, $key)) {
            return $this->getDynamicAttribute($key);
        }

        return parent::__get($key);
    }

    /**
     * Dynamically retrieve the related model's attributes by relation.
     *
     * @param string $relation
     * @return Collection<string, mixed|null>
     * @throws InvalidAttributeHandler|InvalidAttributeService
     */
    public function getDynamicAttribute(string $relation): Collection
    {
        $manager = app(AttributeManager::class);

        if (!isset($manager->types[$relation])) {
            throw new InvalidAttributeService($this::class);
        }

        /** @var AttributeService $service */
        $service = app($manager->types[$relation]);

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
                $defaultValues = $service->handlers[$this::class] ?? [];

                foreach ($defaultValues as $stat => $class) {
                    if (!is_subclass_of($class, $service->handler)) {
                        throw new InvalidAttributeHandler($class);
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
     * Save the dynamically retrieved relation data
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
