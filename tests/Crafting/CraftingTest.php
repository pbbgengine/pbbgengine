<?php

declare(strict_types=1);

namespace PbbgEngine\Tests\Crafting;

use PbbgEngine\Crafting\Models\Blueprint;
use PbbgEngine\Item\Models\Item;
use PbbgEngine\Tests\TestCase;

class CraftingTest extends TestCase
{
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
}
