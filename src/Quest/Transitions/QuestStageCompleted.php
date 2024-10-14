<?php

declare(strict_types=1);

namespace PbbgEngine\Quest\Transitions;

use PbbgEngine\Quest\Models\QuestInstance;
use PbbgEngine\Quest\Models\QuestTransition;

class QuestStageCompleted implements Transition
{
    public function handle(QuestInstance $instance, QuestTransition $transition): void
    {
        $instance->current_quest_stage_id = $transition->actionable_id;
        $instance->save();
    }
}
