<?php

declare(strict_types=1);

namespace PbbgEngine\Crafting;

use Exception;
use Illuminate\Database\Eloquent\Model;
use PbbgEngine\Crafting\Actions\Action;
use PbbgEngine\Crafting\Builders\Builder;
use PbbgEngine\Crafting\Conditions\Condition;
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
     * Checks whether the required conditions for each component in the
     * blueprint are satisfied to be able to craft the given blueprint.
     *
     * @throws Exception
     */
    public function canCraft(Model $model, Blueprint $blueprint): bool
    {
        foreach ($blueprint->components as $component) {
            if (!isset($this->conditions[$component->model_type]) || !class_exists($this->conditions[$component->model_type])) {
                throw new Exception("component condition handler does not exist for model: $component->model_type");
            }
            if (!is_subclass_of($this->conditions[$component->model_type], Condition::class)) {
                throw new Exception("invalid component condition handler: $component->model_type");
            }
            $handler = new $this->conditions[$component->model_type];
            if (!$handler->passes($model, $component)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Executes the crafting process for the provided model and blueprint.
     *
     * Runs optional actions for each component and builds the final result
     * using the specified builder for the blueprint model type.
     *
     * @throws Exception
     */
    public function craft(Model $model, Blueprint $blueprint): bool
    {
        if (!$this->canCraft($model, $blueprint)) {
            return false;
        }

        // ensure builder exists for the blueprint before running actions
        $this->validateBuilder($blueprint->model_type);

        foreach ($blueprint->components as $component) {
            $this->runComponentAction($model, $component);
        }

        /** @var Builder $builder */
        $builder = new $this->builders[$blueprint->model_type];
        $builder->build($model, $blueprint);

        return true;
    }

    /**
     * Validates if the builder for the provided model is valid.
     *
     * This method is used to prevent running actions if
     * the blueprint model cannot be crafted.
     */
    private function validateBuilder(string $model): void
    {
        if (!isset($this->builders[$model])) {
            throw new Exception("blueprint builder does not exist for model: $model");
        }
        if (!class_exists($this->builders[$model])) {
            throw new Exception("specified component action runner does not exist for model: $model");
        }
        if (!is_subclass_of($this->builders[$model], Builder::class)) {
            throw new Exception("invalid component action runner: $model");
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
            throw new Exception("specified component action runner does not exist for model: $component->model_type");
        }
        if (!is_subclass_of($this->actions[$component->model_type], Action::class)) {
            throw new Exception("invalid component action runner: $component->model_type");
        }
        $handler = new $this->actions[$component->model_type];
        $handler->run($model, $component);
    }
}
