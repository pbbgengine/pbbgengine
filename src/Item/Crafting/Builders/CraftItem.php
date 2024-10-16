<?php

declare(strict_types=1);

namespace PbbgEngine\Item\Crafting\Builders;

use Exception;
use Illuminate\Database\Eloquent\Model;
use PbbgEngine\Crafting\Builders\Builder;
use PbbgEngine\Crafting\Models\Blueprint;

class CraftItem extends Builder
{
    public function build(Model $model, Blueprint $blueprint): void
    {
        if (!method_exists($model, 'items')) {
            throw new Exception("{$model} cannot have items");
        }

        $model->items()->create([
            'model_type' => $model::class,
            'item_id' => $blueprint->model_id,
        ]);

        $this->messages->add('success', "Crafted item {$blueprint->model->name}");
    }
}
