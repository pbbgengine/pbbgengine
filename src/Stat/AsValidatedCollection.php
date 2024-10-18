<?php

declare(strict_types=1);

namespace PbbgEngine\Stat;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Database\Eloquent\Model;
use PbbgEngine\Stat\Models\Stats;

class AsValidatedCollection implements Castable
{
    /**
     * Get the caster class to use when casting from / to this cast target.
     *
     * @param  array<int, mixed>  $arguments
     * @return CastsAttributes<ValidatedCollection<string, mixed>, iterable<string, mixed>>
     */
    public static function castUsing(array $arguments)
    {
        return new class() implements CastsAttributes
        {
            public function __construct() {}

            /**
             * @param Stats $model
             * @param array<string, mixed> $attributes
             * @return ValidatedCollection<array-key, mixed>|null
             */
            public function get(Model $model, string $key, mixed $value, array $attributes)
            {
                if (! isset($attributes[$key])) {
                    return null;
                }

                $data = Json::decode($attributes[$key]);

                if (!is_array($data)) {
                    return null;
                }

                return ValidatedCollection::withModel($model->model_type, $data);
            }

            /**
             * @param Stats $model
             * @param array<string, mixed> $attributes
             * @return array<string, mixed>
             */
            public function set(Model $model, string $key, mixed $value, array $attributes)
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
