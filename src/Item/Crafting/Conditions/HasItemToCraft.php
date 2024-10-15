<?php

declare(strict_types=1);

namespace PbbgEngine\Item\Crafting\Conditions;

use Exception;
use Illuminate\Database\Eloquent\Model;
use PbbgEngine\Crafting\Conditions\Condition;
use PbbgEngine\Crafting\Models\Component;

class HasItemToCraft extends Condition
{
    public function passes(Model $model, Component $component): void
    {
        if (!method_exists($model, 'items')) {
            throw new Exception("{$model} cannot have items");
        }

        if ($model->items()->where('item_id', $component->model_id)->count() == 0) {
            $this->messages->add('errors', "Missing item {$component->model->name}");
        }
    }
}
