<?php

declare(strict_types=1);

namespace PbbgEngine\Quest\Transitions;

use Exception;
use PbbgEngine\Quest\Models\QuestInstance;
use PbbgEngine\Quest\Models\QuestTransition;

interface Transition
{
    /**
     * Applies a quest transition to an actionable model associated with the instance.
     *
     * @throws Exception
     */
    public function handle(QuestInstance $instance, QuestTransition $transition): void;
}
