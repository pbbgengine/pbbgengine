<?php

declare(strict_types=1);

namespace PbbgEngine\Quest\Crafting\Conditions;

use Exception;
use Illuminate\Database\Eloquent\Model;
use PbbgEngine\Crafting\Conditions\Condition;
use PbbgEngine\Crafting\Models\Component;

class HasCompletedQuest extends Condition
{
    public function passes(Model $model, Component $component): void
    {
        if (!method_exists($model, 'quests')) {
            throw new Exception("{$model} cannot have quests");
        }

        $hasCompletedQuest = $model->quests()
                ->where('quest_id', $component->model_id)
                ->whereNotNull('completed_at')
                ->count() > 0;

        if (!$hasCompletedQuest) {
            $this->messages->add('errors', "Quest {$component->model->name} has not been completed");
        }
    }
}
