<?php

declare(strict_types=1);

namespace PbbgEngine\Attribute\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use PbbgEngine\Attribute\Exceptions\InvalidAttributeHandler;
use PbbgEngine\Stat\StatService;

/**
 * @mixin Model
 */
trait HasDynamicAttributes
{
    /**
     * @param $key
     * @return mixed
     * @throws InvalidAttributeHandler
     */
    public function __get($key): mixed
    {
        // todo: get attribute types mapped by model type
        // from property in attribute service
        if (in_array($key, ['stats', 'resources'])) {
            return $this->getDynamicAttribute($key);
        }

        return parent::__get($key);
    }

    /**
     * Dynamically retrieve the related model's attributes by relation.
     *
     * @param string $relation
     * @return Collection<string, mixed|null>
     * @throws InvalidAttributeHandler
     */
    public function getDynamicAttribute(string $relation): Collection
    {
        // todo: get service by relation from property in attribute service
        /** @var StatService $service */
        $service = app(StatService::class);

        if (!in_array(self::class, $service->booted)) {
            $service->bootObserver($this);
        }

        if (!isset($this->attributes[$relation])) {
            if (!isset($this->relations[$relation])) {
                $this->load($relation);
            }

            $this->attributes[$relation] = $this->relations[$relation]?->{$relation};

            if ($this->attributes[$relation] === null) {
                $data = [];
                $defaultValues = $service->handlers[$this::class] ?? [];

                foreach ($defaultValues as $stat => $class) {
                    if (!is_subclass_of($class, $service->handler)) {
                        throw new InvalidAttributeHandler($class);
                    }
                    $validator = new $class($this);
                    $data[$stat] = $validator->default();
                }

                $this->{$relation}()->create([
                    'model_type' => self::class,
                    'model_id' => $this->{$this->primaryKey},
                    $relation => $data,
                ]);

                $this->unsetRelation($relation);
                $this->load($relation);

                /** @var Model $instance */
                $instance = $this->relations[$relation];
                $this->attributes[$relation] = $instance->{$relation};
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
        }
        unset($this->attributes[$relation]);
    }
}
