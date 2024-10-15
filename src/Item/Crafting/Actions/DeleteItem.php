<?php

declare(strict_types=1);

namespace PbbgEngine\Item\Crafting\Actions;

use Exception;
use Illuminate\Database\Eloquent\Model;
use PbbgEngine\Crafting\Actions\Action;
use PbbgEngine\Crafting\Models\Component;

class DeleteItem implements Action
{
    public function run(Model $model, Component $component): void
    {
        if (!method_exists($model, 'items')) {
            throw new Exception("{$model} cannot have items");
        }

        $item = $model->items()->where('item_id', $component->model_id)->first();
        $item->delete();
    }
}
