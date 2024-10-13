<?php

declare(strict_types=1);

namespace Workbench\App\Game\Item\Interactions;

use Illuminate\Support\MessageBag;
use PbbgEngine\Item\Interactions\Interaction;
use PbbgEngine\Item\Models\ItemInstance;

class Drink implements Interaction
{
    public function handle(ItemInstance $instance): MessageBag
    {
        $messages = new MessageBag();

        if ($instance->data->get('empty') === true) {
            $messages->add('errors', 'Cannot drink as it is already empty');
            return $messages;
        }

        $instance->data->put('empty', true);
        $messages->add('success', "You drank the {$instance->item->name}");
        return $messages;
    }
}
