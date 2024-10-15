<?php

declare(strict_types=1);

namespace PbbgEngine\Quest\Crafting\Builders;

use Exception;
use Illuminate\Database\Eloquent\Model;
use PbbgEngine\Crafting\Builders\Builder;
use PbbgEngine\Crafting\Models\Blueprint;

class StartQuest extends Builder
{
    public function build(Model $model, Blueprint $blueprint): void
    {
        if (!method_exists($model, 'quests')) {
            throw new Exception("{$model} cannot have quests");
        }

        if ($model->quests()->where('quest_id', $blueprint->model_id)->exists()) {
            $this->messages->add('errors', "Already has quest {$blueprint->model->name}");
            return;
        }

        $model->quests()->create([
            'model_type' => $model::class,
            'quest_id' => $blueprint->model_id,
            'current_quest_stage_id' => $blueprint->model->initial_quest_stage_id,
            'progress' => [],
        ]);

        $this->messages->add('success', "Started quest {$blueprint->model->name}");
    }
}
