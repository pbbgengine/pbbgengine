<?php

declare(strict_types=1);

namespace PbbgEngine\Crafting;

use Exception;
use Illuminate\Database\Eloquent\Model;
use PbbgEngine\Crafting\Models\Blueprint;
use PbbgEngine\Item\Models\Item;
use PbbgEngine\Quest\Models\Quest;

class CraftingService
{
    public function canCraft(Model $model, Blueprint $blueprint): bool
    {
        foreach ($blueprint->components as $component) {
            switch ($component->model_type) {
                case Item::class:
                    // todo: create per model handlers
                    if (!method_exists($model, 'items')) {
                        throw new Exception("{$this->model} cannot have items");
                    }
                    if ($model->items()->where('item_id', $component->model_id)->count() == 0) {
                        return false;
                    }
                    break;
                default:
                    throw new Exception("invalid crafting component model type: $component->model_type");
            }
        }

        return true;
    }
}
