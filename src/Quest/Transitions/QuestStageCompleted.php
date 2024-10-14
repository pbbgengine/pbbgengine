<?php

declare(strict_types=1);

namespace PbbgEngine\Quest\Transitions;

use Exception;
use PbbgEngine\Quest\Models\QuestInstance;
use PbbgEngine\Quest\Models\QuestTransition;

class QuestStageCompleted implements Transition
{
    /**
     * Updates the stage of the quest instance.
     *
     * @throws Exception
     */
    public function handle(QuestInstance $instance, QuestTransition $transition): void
    {
        if ($instance->quest_id === $transition->actionable->quest_id) {
            $instance->current_quest_stage_id = $transition->actionable_id;
            $instance->save();
        }

        // allow progression of external quest
        $instance = QuestInstance::whereHas('quest.stages', function ($query) use ($transition) {
            $query->where('id', $transition->actionable_id);
        })->first();

        if (!$instance) {
            throw new Exception("does not have stage {$transition->actionable_id}");
        }

        $instance->current_quest_stage_id = $transition->actionable_id;
        $instance->save();
    }
}
