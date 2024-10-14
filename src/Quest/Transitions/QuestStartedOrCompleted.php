<?php

declare(strict_types=1);

namespace PbbgEngine\Quest\Transitions;

use PbbgEngine\Quest\Models\QuestInstance;
use PbbgEngine\Quest\Models\QuestTransition;

class QuestStartedOrCompleted implements Transition
{
    public function handle(QuestInstance $instance, QuestTransition $transition): void
    {
        if ($instance->quest_id === $transition->actionable_id) {
            $instance->completed_at = now();
            $instance->save();
        } else {
            // todo: allow completion of other active quests
            $instance->model->quests()->create([
                'model_type' => $instance->model::class,
                'quest_id' => $transition->actionable_id,
                'progress' => [],
                'current_quest_stage_id' => $transition->actionable->initial_quest_stage_id,
            ]);
        }
    }
}
