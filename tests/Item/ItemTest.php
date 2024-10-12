<?php

declare(strict_types=1);

namespace PbbgEngine\Tests\Item;

use Illuminate\Support\Facades\Hash;
use PbbgEngine\Item\Models\Item;
use PbbgEngine\Item\Models\ItemInstance;
use PbbgEngine\Tests\TestCase;
use Workbench\App\Models\User;
use Workbench\Database\Factories\UserFactory;

class ItemTest extends TestCase
{
    private Item $item;
    private User $user;
    private ItemInstance $instance;

    public function setUp(): void
    {
        parent::setUp();

        $this->item = Item::create([
            'name' => 'Item',
        ]);

        $this->user = UserFactory::new()->createOne();

        $this->instance = $this->item->instances()->create([
            'model_type' => User::class,
            'model_id' => $this->user->id,
        ]);
    }

    public function testItemIsCreated(): void
    {
        $this->assertDatabaseHas('items', [
            'id' => $this->item->id,
            'name' => $this->item->name,
        ]);

        $this->assertDatabaseHas('item_instances', [
            'id' => $this->instance->id,
            'model_type' => User::class,
            'model_id' => $this->instance->model_id,
        ]);
    }

    public function testModelHasItemInstance(): void
    {
        $this->assertTrue($this->user->items()->exists());
        $this->assertNotNull($this->user->items()->first());
        $this->assertEquals($this->instance->id, $this->user->items()->first()->id);
    }
}
