<?php

declare(strict_types=1);

namespace PbbgEngine\Attribute\Support;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Database\Eloquent\Model;
use PbbgEngine\Attribute\Models\Attributes;

class AsValidatedAttributes implements Castable
{
    /**
     * Get the caster class to use when casting from / to this cast target.
     *
     * @param  array<int, mixed>  $arguments
     * @return CastsAttributes<ValidatedAttributes<string, mixed>, iterable<string, mixed>>
     */
    public static function castUsing(array $arguments)
    {
        return new class() implements CastsAttributes
        {
            public function __construct() {}

            /**
             * @param Attributes $model
             * @param array<string, mixed> $attributes
             * @return ValidatedAttributes<array-key, mixed>|null
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

                return ValidatedAttributes::withModel($model, $data);
            }

            /**
             * @param Attributes $model
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
