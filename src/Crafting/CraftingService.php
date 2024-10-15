<?php

declare(strict_types=1);

namespace PbbgEngine\Crafting;

use Exception;
use Illuminate\Database\Eloquent\Model;
use PbbgEngine\Crafting\Actions\Action;
use PbbgEngine\Crafting\Builders\Builder;
use PbbgEngine\Crafting\Conditions\Condition;
use PbbgEngine\Crafting\Models\Blueprint;

class CraftingService
{
    /**
     * @var array<string, string>
     */
    public array $conditions = [];

    /**
     * @var array<string, string>
     */
    public array $actions = [];

    /**
     * @var array<string, string>
     */
    public array $builders = [];

    /**
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
     * @throws Exception
     */
    public function craft(Model $model, Blueprint $blueprint): bool
    {
        if (!$this->canCraft($model, $blueprint)) {
            return false;
        }

        // ensure builder exists for the blueprint before running actions
        if (!isset($this->builders[$blueprint->model_type])) {
            throw new Exception("blueprint builder does not exist for model: $blueprint->model_type");
        }
        if (!class_exists($this->builders[$blueprint->model_type])) {
            throw new Exception("specified component action runner does not exist for model: $blueprint->model_type");
        }
        if (!is_subclass_of($this->builders[$blueprint->model_type], Builder::class)) {
            throw new Exception("invalid component action runner: $blueprint->model_type");
        }

        foreach ($blueprint->components as $component) {
            if (!isset($this->actions[$component->model_type])) {
                // actions are optional
                continue;
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

        $builder = new $this->builders[$blueprint->model_type];
        $builder->build($model, $blueprint);

        return true;
    }
}
