<?php

declare(strict_types=1);

namespace PbbgEngine\Item\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\MessageBag;
use PbbgEngine\Item\Interactions\Interaction;
use PbbgEngine\Item\Models\ItemInstance;

class ItemInteractionEvent
{
    use Dispatchable, SerializesModels;

    /**
     * The item instance that was interacted with.
     */
    public ItemInstance $instance;

    /**
     * The interaction that was performed.
     */
    public Interaction $interaction;

    /**
     * The result of the interaction.
     */
    public MessageBag $result;

    /**
     * Create a new event instance.
     */
    public function __construct(
        ItemInstance $instance,
        Interaction $interaction,
        MessageBag $result,
    ) {
        $this->instance = $instance;
        $this->interaction = $interaction;
        $this->result = $result;
    }
}
