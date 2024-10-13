<?php

declare(strict_types=1);

namespace PbbgEngine\Item\Interactions;

use Exception;
use Illuminate\Support\MessageBag;
use PbbgEngine\Item\Models\ItemInstance;
use PbbgEngine\Item\Models\ItemInteraction;

abstract class Interaction
{
    protected ItemInteraction $interaction;

    public function __construct(ItemInteraction $interaction)
    {
        $this->interaction = $interaction;
    }

    /**
     * Attempts to handle the interaction on the instance.
     * Throws an exception when the interaction is not compatible with the item.
     * Returns a message bag with the results of the interaction.
     *
     * @param ItemInstance $instance
     * @param array<string, mixed> $context
     * @throws Exception
     * @return MessageBag
     */
    abstract public function handle(ItemInstance $instance, array $context = []): MessageBag;
}
