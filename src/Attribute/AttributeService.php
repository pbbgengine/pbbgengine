<?php

declare(strict_types=1);

namespace PbbgEngine\Attribute;

use Illuminate\Database\Eloquent\Model;

/**
 * @template THandler of object
 * @template TObserver of object
 */
class AttributeService
{
    /**
     * The attributes applied to each model.
     *
     * e.g. [User::class => ['health' => HealthValidator::class]]
     *
     * @var array<class-string<Model>, array<string, class-string<THandler>>> $handlers
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
     * @var class-string<THandler>
     */
    public string $handler;

    /**
     * The class name of the attribute observer.
     *
     * @var class-string<TObserver>
     */
    public string $observer;

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
