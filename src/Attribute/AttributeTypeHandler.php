<?php

declare(strict_types=1);

namespace PbbgEngine\Attribute;

use Illuminate\Database\Eloquent\Model;
use PbbgEngine\Attribute\Exceptions\InvalidAttributeValidator;
use PbbgEngine\Attribute\Observers\AttributeProxyObserver;
use PbbgEngine\Attribute\Validators\Validator;

class AttributeTypeHandler
{
    /**
     * The validators for each attribute mapped by model type.
     *
     * e.g. [User::class => ['health' => HealthValidator::class]]
     *
     * @var array<class-string<Model>, array<string, class-string<Validator>>> $validators
     */
    public array $validators = [];

    /**
     * The models that have had their attribute observers booted.
     *
     * @var array<class-string<Model>>
     */
    public array $booted = [];

    /**
     * The base class that validators must implement.
     *
     * @var class-string<Validator>
     */
    public string $validator = Validator::class;

    /**
     * The class name of the attribute observer.
     *
     * @var class-string<AttributeProxyObserver>
     */
    public string $observer = AttributeProxyObserver::class;

    /**
     * Boots the attribute observer for the given model.
     * Called when attributes are accessed for the first time on a model.
     */
    public function bootObserver(Model $model): void
    {
        $model::observe($this->observer);
        $this->booted[] = $model::class;
    }

    /**
     * Binds a validator to a model and attribute.
     *
     * @param class-string<Model> $model
     * @param class-string<Validator> $validator
     * @throws InvalidAttributeValidator
     */
    public function bindValidator(string $model, string $attribute, string $validator): void
    {
        if (!is_subclass_of($validator, $this->validator)) {
            throw new InvalidAttributeValidator($validator);
        }

        if (!isset($this->validators[$model])) {
            $this->validators[$model] = [];
        }

        $this->validators[$model][$attribute] = $validator;
    }

}
