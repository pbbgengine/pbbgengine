<?php

declare(strict_types=1);

namespace PbbgEngine\Quest\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use PbbgEngine\Quest\Models\QuestInstance;
use PbbgEngine\Quest\Models\QuestTransition;

class TransitionEvent
{
    use Dispatchable, SerializesModels;

    /**
     * The quest instance that triggered the transition.
     */
    public QuestInstance $instance;

    /**
     * The transition that was actioned.
     */
    public QuestTransition $transition;

    /**
     * Create a new event instance.
     */
    public function __construct(
        QuestInstance $instance,
        QuestTransition $transition,
    ) {
        $this->instance = $instance;
        $this->transition = $transition;
    }
}
