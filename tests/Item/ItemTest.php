<?php

declare(strict_types=1);

namespace PbbgEngine\Tests\Item;

use PbbgEngine\Item\Models\Item;
use PbbgEngine\Tests\TestCase;

class ItemTest extends TestCase
{
    private Item $item;

    public function setUp(): void
    {
        parent::setUp();

        $this->item = Item::create([
            'name' => 'Item',
        ]);
    }

    public function testItemIsCreated(): void
    {
        $this->assertDatabaseHas('items', [
            'id' => $this->item->id,
            'name' => $this->item->name,
        ]);
    }
}
