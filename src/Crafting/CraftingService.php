<?php

declare(strict_types=1);

namespace PbbgEngine\Crafting;

use Exception;
use Illuminate\Database\Eloquent\Model;
use PbbgEngine\Crafting\Conditions\Condition;
use PbbgEngine\Crafting\Models\Blueprint;

class CraftingService
{
    /**
     * @var array<string, string>
     */
    public array $conditions = [];

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
}
