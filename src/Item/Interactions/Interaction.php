<?php

declare(strict_types=1);

namespace PbbgEngine\Item\Interactions;

use Exception;
use Illuminate\Support\MessageBag;
use PbbgEngine\Item\Models\ItemInstance;

interface Interaction
{
    /**
     * Attempts to handle the interaction on the instance.
     * Throws an exception when the interaction is not compatible with the item.
     * Returns a message bag with the results of the interaction.
     *
     * @throws Exception
     */
    public function handle(ItemInstance $instance): MessageBag;
}
