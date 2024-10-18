<?php

declare(strict_types=1);

namespace PbbgEngine\Stat;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Casts\Json;
use InvalidArgumentException;
use PbbgEngine\Stat\Models\Stat;

class AsValidatedCollection implements Castable
{
    /**
     * Get the caster class to use when casting from / to this cast target.
     *
     * @param  array  $arguments
     * @return CastsAttributes<ValidatedCollection<array-key, mixed>, iterable>
     */
    public static function castUsing(array $arguments)
    {
        return new class($arguments) implements CastsAttributes
        {
            public function __construct(protected array $arguments) {}

            public function get($model, $key, $value, $attributes)
            {
                if (! isset($attributes[$key])) {
                    return null;
                }

                $data = Json::decode($attributes[$key]);

                if (!is_array($data)) {
                    return null;
                }

                $collection = new ValidatedCollection($data);

                // todo: do not perform query here, temporary
                $collection->stats = Stat::where('model_type', $model->model_type)->get()->mapWithKeys(function($stat) {
                    return [$stat->name => $stat->class];
                })->toArray();

                return $collection;
            }

            public function set($model, $key, $value, $attributes)
            {
                return [$key => Json::encode($value)];
            }
        };
    }

    /**
     * Specify the collection for the cast.
     *
     * @param  class-string  $class
     * @return string
     */
    public static function using($class)
    {
        return static::class.':'.$class;
    }
}
