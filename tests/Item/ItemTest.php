<?php

declare(strict_types=1);

namespace PbbgEngine\Tests\Item;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\MessageBag;
use PbbgEngine\Item\Exceptions\DoesNotHaveInteraction;
use PbbgEngine\Item\Exceptions\InteractionDoesNotExist;
use PbbgEngine\Item\Exceptions\InvalidInteraction;
use PbbgEngine\Item\Models\Item;
use PbbgEngine\Item\Models\ItemInstance;
use PbbgEngine\Tests\TestCase;
use Workbench\App\Game\Item\Interactions\Drink;
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
        $this->assertModelExists($this->item);
        $this->assertModelExists($this->instance);
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

    public function testCanInteractWithItem(): void
    {
        $item = Item::create([
            'name' => 'Can of Cola',
            'data' => ['empty' => false],
        ]);

        $item->interactions()->create(['class' => Drink::class]);

        $instance = $item->instances()->create([
            'model_type' => User::class,
            'model_id' => $this->user->id,
            'data' => ['empty' => true],
        ]);

        $this->assertThrows(function() use($instance) {
            $instance->interact('fake_interaction');
        }, InteractionDoesNotExist::class);

        // User is not an interaction
        $this->assertThrows(function() use($instance) {
            $instance->interact(User::class);
        }, InvalidInteraction::class);

        $messages = $instance->interact(Drink::class);
        $this->assertInstanceOf(MessageBag::class, $messages);
        $this->assertTrue($messages->has('errors'));

        $instance->data->put('empty', false);

        $messages = $instance->interact(Drink::class);
        $this->assertInstanceOf(MessageBag::class, $messages);
        $this->assertFalse($messages->has('errors'));
        $this->assertTrue($messages->has('success'));

        $this->assertThrows(function() {
            $this->instance->interact(Drink::class);
        }, DoesNotHaveInteraction::class);
    }
}
