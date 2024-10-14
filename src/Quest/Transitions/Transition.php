<?php

declare(strict_types=1);

namespace PbbgEngine\Quest\Transitions;

use PbbgEngine\Quest\Models\QuestInstance;
use PbbgEngine\Quest\Models\QuestTransition;

interface Transition
{
    public function handle(QuestInstance $instance, QuestTransition $transition): void;
}
