<?php

declare(strict_types=1);

namespace PbbgEngine\Attribute;

use Illuminate\Database\Eloquent\Model;
use PbbgEngine\Attribute\Observers\AttributeProxyObserver;
use PbbgEngine\Attribute\Validators\Validator;

class AttributeService
{
    /**
     * The attributes applied to each model.
     *
     * e.g. [User::class => ['health' => HealthValidator::class]]
     *
     * @var array<class-string<Model>, array<string, class-string<Validator>>> $handlers
     */
    public array $handlers = [];

    /**
     * The models that have had their attribute observers booted.
     *
     * @var array<class-string<Model>>
     */
    public array $booted = [];

    /**
     * The class or interface that the attribute handlers must implement.
     *
     * @var class-string<Validator>
     */
    public string $handler = Validator::class;

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
}
