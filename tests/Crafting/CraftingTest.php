<?php

declare(strict_types=1);

namespace PbbgEngine\Tests\Crafting;

use PbbgEngine\Crafting\CraftingService;
use PbbgEngine\Crafting\CraftingServiceProvider;
use PbbgEngine\Crafting\Exceptions\HandlerDoesNotExist;
use PbbgEngine\Crafting\Exceptions\HasNoComponents;
use PbbgEngine\Crafting\Exceptions\InvalidHandler;
use PbbgEngine\Crafting\Models\Blueprint;
use PbbgEngine\Item\ItemServiceProvider;
use PbbgEngine\Item\Models\Item;
use PbbgEngine\Quest\Models\Quest;
use PbbgEngine\Quest\QuestServiceProvider;
use PbbgEngine\Tests\TestCase;
use Workbench\Database\Factories\UserFactory;

class CraftingTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            ItemServiceProvider::class,
            QuestServiceProvider::class,
            CraftingServiceProvider::class,
        ];
    }

    public function testBlueprintHasComponents(): void
    {
        $dough = Item::create(['name' => 'Bread dough']);
        $blueprint = Blueprint::create([
            'name' => 'Recipe: Bread dough',
            'model_type' => $dough::class,
            'model_id' => $dough->id],
        );

        $flour = Item::create(['name' => 'Pot of flour']);
        $blueprint->components()->create([
            'model_type' => $flour::class,
            'model_id' => $flour->id,
        ]);

        $water = Item::create(['name' => 'Bucket of water']);
        $blueprint->components()->create([
            'model_type' => $water::class,
            'model_id' => $water->id,
        ]);

        $this->assertModelExists($blueprint);
        $this->assertModelExists($dough);
        $this->assertModelExists($flour);
        $this->assertModelExists($water);

        $this->assertCount(2, $blueprint->components);

        $this->assertEquals(
            [$flour->id, $water->id],
            $blueprint->components->pluck('model_id')->toArray(),
        );
    }

    public function testModelCanCraft(): void
    {
        $user = UserFactory::new()->createOne();

        $dough = Item::create(['name' => 'Bread dough']);
        $blueprint = Blueprint::create([
            'name' => 'Recipe: Bread dough',
            'model_type' => $dough::class,
            'model_id' => $dough->id],
        );

        $flour = Item::create(['name' => 'Pot of flour']);
        $blueprint->components()->create([
            'model_type' => $flour::class,
            'model_id' => $flour->id,
        ]);

        $water = Item::create(['name' => 'Bucket of water']);
        $blueprint->components()->create([
            'model_type' => $water::class,
            'model_id' => $water->id,
        ]);

        $service = app(CraftingService::class);
        $result = $service->canCraft($user, $blueprint);
        $this->assertHasErrors($result);
        $this->assertEquals(["Missing item $flour->name"], $result->get('errors'));

        $user->items()->create([
            'model_type' => $user::class,
            'item_id' => $flour->id
        ]);

        // cannot craft, is missing the bucket of water
        $result = $service->canCraft($user, $blueprint);
        $this->assertHasErrors($result);
        $this->assertEquals(["Missing item $water->name"], $result->get('errors'));

        $user->items()->create([
            'model_type' => $user::class,
            'item_id' => $water->id
        ]);

        // can craft, has all the required component
        $this->assertHasNoErrors($service->canCraft($user, $blueprint));

        // let's add a new component required to craft the blueprint
        // the user will be required to have completed this quest
        $quest = Quest::create(['name' => 'Bread making class']);
        $stage = $quest->stages()->create(['name' => 'Learn the dough recipe']);
        $quest->initial_quest_stage_id = $stage->id;
        $quest->save();

        $blueprint->components()->create([
            'model_type' => $quest::class,
            'model_id' => $quest->id,
        ]);

        $blueprint->refresh();

        // cannot craft, user has not completed the quest or even started it
        $result = $service->canCraft($user, $blueprint);
        $this->assertHasErrors($result);
        $this->assertEquals(["Quest $quest->name has not been completed"], $result->get('errors'));

        $questInstance = $user->quests()->create([
            'model_type' => $user::class,
            'quest_id' => $quest->id,
            'current_quest_stage_id' => $stage->id,
            'progress' => [],
        ]);

        // cannot craft, user has not finished the quest
        $result = $service->canCraft($user, $blueprint);
        $this->assertHasErrors($result);
        $this->assertEquals(["Quest $quest->name has not been completed"], $result->get('errors'));;

        $questInstance->completed_at = now();
        $questInstance->save();

        // can craft, user has finished the quest
        $this->assertHasNoErrors($service->canCraft($user, $blueprint));

        // let's add an invalid crafting component to force an exception
        $blueprint->components()->create([
            'model_type' => $user::class,
            'model_id' => $user->id,
        ]);

        $blueprint->refresh();

        $this->assertThrows(function() use ($service, $user, $blueprint) {
            $service->canCraft($user, $blueprint);
        }, HandlerDoesNotExist::class);

        // adding an invalid condition handler to force an exception
        $service->conditions[$user::class] = $user::class;

        $this->assertThrows(function() use ($service, $user, $blueprint) {
            $service->canCraft($user, $blueprint);
        }, InvalidHandler::class);

        $blueprint->components()->delete();
        $blueprint->refresh();

        $this->assertThrows(function() use ($service, $user, $blueprint) {
            $service->canCraft($user, $blueprint);
        }, HasNoComponents::class);
    }

    public function testCraft(): void
    {
        $user = UserFactory::new()->createOne();

        $dough = Item::create(['name' => 'Bread dough']);
        $blueprint = Blueprint::create([
            'name' => 'Recipe: Bread dough',
            'model_type' => $dough::class,
            'model_id' => $dough->id],
        );

        $flour = Item::create(['name' => 'Pot of flour']);
        $blueprint->components()->create([
            'model_type' => $flour::class,
            'model_id' => $flour->id,
        ]);

        $water = Item::create(['name' => 'Bucket of water']);
        $blueprint->components()->create([
            'model_type' => $water::class,
            'model_id' => $water->id,
        ]);

        $service = app(CraftingService::class);

        $this->assertHasErrors($service->craft($user, $blueprint));

        $user->items()->create([
            'model_type' => $user::class,
            'item_id' => $flour->id
        ]);

        $user->items()->create([
            'model_type' => $user::class,
            'item_id' => $water->id
        ]);

        // can craft, has the required items
        $this->assertHasNoErrors($service->craft($user, $blueprint));

        $this->assertCount(1, $user->items);
        $itemInstance = $user->items->first();
        $this->assertNotNull($itemInstance);
        $this->assertEquals($dough->id, $itemInstance->model_id);

        // cannot craft, no longer has the flour and water
        $this->assertHasErrors($service->craft($user, $blueprint));
    }
}
