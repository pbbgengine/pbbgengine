<?php

declare(strict_types=1);

namespace PbbgEngine\Crafting;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\MessageBag;
use PbbgEngine\Crafting\Actions\Action;
use PbbgEngine\Crafting\Builders\Builder;
use PbbgEngine\Crafting\Conditions\Condition;
use PbbgEngine\Crafting\Exceptions\HandlerDoesNotExist;
use PbbgEngine\Crafting\Exceptions\HasNoComponents;
use PbbgEngine\Crafting\Exceptions\InvalidHandler;
use PbbgEngine\Crafting\Models\Blueprint;
use PbbgEngine\Crafting\Models\Component;

class CraftingService
{
    /**
     * Stores component condition handlers per model type.
     * Each component of a blueprint must pass to allow the crafting to occur.
     *
     * @var array<string, string>
     */
    public array $conditions = [];

    /**
     * Stores component action runners per model type.
     * Action runners will run for each component of the blueprint once all conditions pass.
     *
     * @var array<string, string>
     */
    public array $actions = [];

    /**
     * Stores blueprint builders per model type.
     * Runs after the action runners when all component conditions are met.
     *
     * @var array<string, string>
     */
    public array $builders = [];

    /**
     * The result of the crafting check or attempt.
     * The "errors" key will be populated to denote any failures.
     *
     * The crafting occurred successfully if there is no "errors" key set.
     */
    public MessageBag $messages;

    /**
     * Checks whether the required conditions for each component in the
     * blueprint are satisfied to be able to craft the given blueprint.
     *
     * The returned message bag will contain errors if a condition was unmet.
     *
     * @throws Exception
     */
    public function canCraft(Model $model, Blueprint $blueprint): MessageBag
    {
        $this->messages = new MessageBag();

        if ($blueprint->components->count() === 0) {
            throw new HasNoComponents($blueprint);
        }

        foreach ($blueprint->components as $component) {
            if (!isset($this->conditions[$component->model_type]) || !class_exists($this->conditions[$component->model_type])) {
                throw new HandlerDoesNotExist("component condition handler does not exist for model: $component->model_type");
            }
            if (!is_subclass_of($this->conditions[$component->model_type], Condition::class)) {
                throw new InvalidHandler("invalid component condition handler: $component->model_type");
            }
            $handler = new $this->conditions[$component->model_type]($this->messages);
            $handler->passes($model, $component);
            if ($this->messages->has('errors')) {
                return $this->messages;
            }
        }

        return $this->messages;
    }

    /**
     * Executes the crafting process for the provided model and blueprint.
     *
     * Runs optional actions for each component and builds the final result
     * using the specified builder for the blueprint model type.
     *
     * The returned message bag will contain errors if a condition was unmet.
     *
     * @throws Exception
     */
    public function craft(Model $model, Blueprint $blueprint): MessageBag
    {
        if ($this->canCraft($model, $blueprint)->has('errors')) {
            return $this->messages;
        }

        // ensure builder exists for the blueprint before running actions
        $this->validateBuilder($blueprint->model_type);

        foreach ($blueprint->components as $component) {
            $this->runComponentAction($model, $component);
        }

        /** @var Builder $builder */
        $builder = new $this->builders[$blueprint->model_type]($this->messages);
        $builder->build($model, $blueprint);

        return $this->messages;
    }

    /**
     * Validates if the builder for the provided model is valid.
     *
     * This method is used to prevent running actions if
     * the blueprint model cannot be crafted.
     */
    private function validateBuilder(string $model): void
    {
        if (!isset($this->builders[$model]) || !class_exists($this->builders[$model])) {
            throw new HandlerDoesNotExist("blueprint builder does not exist for model: $model");
        }
        if (!is_subclass_of($this->builders[$model], Builder::class)) {
            throw new InvalidHandler("invalid blueprint builder: $model");
        }
    }

    /**
     * Runs the component action for the provided component if it exists.
     */
    private function runComponentAction(Model $model, Component $component): void
    {
        if (!isset($this->actions[$component->model_type])) {
            // actions are optional
            return;
        }
        if (!class_exists($this->actions[$component->model_type])) {
            throw new HandlerDoesNotExist("specified component action runner does not exist for model: $component->model_type");
        }
        if (!is_subclass_of($this->actions[$component->model_type], Action::class)) {
            throw new InvalidHandler("invalid component action runner: $component->model_type");
        }
        $handler = new $this->actions[$component->model_type]($this->messages);
        $handler->run($model, $component);
    }
}
