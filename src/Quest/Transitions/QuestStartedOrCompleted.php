<?php

declare(strict_types=1);

namespace PbbgEngine\Quest\Transitions;

use PbbgEngine\Quest\Models\QuestInstance;
use PbbgEngine\Quest\Models\QuestTransition;

class QuestStartedOrCompleted implements Transition
{
    /**
     * Completes or starts the actionable quest depending on if an instance of it exists.
     */
    public function handle(QuestInstance $instance, QuestTransition $transition): void
    {
        if ($instance->quest_id === $transition->actionable_id) {
            $instance->completed_at = now();
            $instance->save();
        } else {
            $quest = $instance->model->quests()
                ->where('id', $transition->actionable_id)
                ->whereNotNull('completed_at')
                ->first();

            if ($quest) {
                $quest->completed_at = now();
                $quest->save();
                return;
            }

            $instance->model->quests()->create([
                'model_type' => $instance->model::class,
                'quest_id' => $transition->actionable_id,
                'progress' => [],
                'current_quest_stage_id' => $transition->actionable->initial_quest_stage_id,
            ]);
        }
    }
}
