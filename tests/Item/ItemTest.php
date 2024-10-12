<?php

declare(strict_types=1);

namespace PbbgEngine\Tests\Item;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use PbbgEngine\Item\Models\Item;
use PbbgEngine\Item\Models\ItemInstance;
use PbbgEngine\Tests\TestCase;
use Workbench\App\Models\User;
use Workbench\Database\Factories\UserFactory;
use function PHPUnit\Framework\assertEquals;

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
            'data' => ['a' => 'a', 'b' => 'b', 'c' => 'c'],
        ]);

        $this->user = UserFactory::new()->createOne();

        $this->instance = $this->item->instances()->create([
            'model_type' => User::class,
            'model_id' => $this->user->id,
            'data' => ['b' => 'B', 'd' => 'd'],
        ]);
    }

    public function testItemIsCreated(): void
    {
        $this->assertDatabaseHas('items', [
            'id' => $this->item->id,
            'name' => $this->item->name,
            'data' => $this->item->data->toJson(),
        ]);

        $this->assertDatabaseHas('item_instances', [
            'id' => $this->instance->id,
            'model_type' => User::class,
            'model_id' => $this->instance->model_id,
            'data' => $this->instance->data->toJson(),
        ]);
    }

    public function testModelHasItemInstance(): void
    {
        $query = $this->user->items();
        $this->assertTrue($query->exists());
        $instance = $query->first();
        $this->assertNotNull($instance);
        $this->assertEquals($this->instance->id, $instance->id);
    }

    public function testModelHasUniqueItemInstance(): void
    {
        $query = $this->user->unique_items();
        $this->assertTrue($query->exists());
        $instance = $query->first();
        $this->assertNotNull($instance);
        $this->assertEquals($this->instance->id, $instance->id);
    }

    public function testModelHasData(): void
    {
        $this->assertInstanceOf(Collection::class, $this->item->data);
        $this->assertInstanceOf(Collection::class, $this->instance->data);

        $this->assertEquals(['a' => 'a', 'b' => 'b', 'c' => 'c'], $this->item->data->toArray());
        $this->assertEquals(['b' => 'B', 'd' => 'd'], $this->instance->data->toArray());

        $this->assertEquals(
            ['a' => 'a', 'b' => 'B', 'c' => 'c', 'd' => 'd'],
            $this->instance->data_combined->toArray(),
        );
    }
}
