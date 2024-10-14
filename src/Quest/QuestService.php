<?php

declare(strict_types=1);

namespace PbbgEngine\Quest;

use PbbgEngine\Quest\Models\Quest;
use PbbgEngine\Quest\Models\QuestStage;
use PbbgEngine\Quest\Transitions\QuestStageCompleted;
use PbbgEngine\Quest\Transitions\QuestStartedOrCompleted;

class QuestService
{
    public static array $transitions = [
        Quest::class => QuestStartedOrCompleted::class,
        QuestStage::class => QuestStageCompleted::class,
    ];
}
