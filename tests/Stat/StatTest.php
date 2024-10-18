<?php

declare(strict_types=1);

namespace PbbgEngine\Tests\Stat;

use Illuminate\Support\Collection;
use PbbgEngine\Stat\Models\Stat;
use PbbgEngine\Stat\Models\StatInstance;
use PbbgEngine\Stat\StatService;
use PbbgEngine\Stat\ValidatedCollection;
use PbbgEngine\Tests\TestCase;
use Workbench\App\Models\User;
use Workbench\Database\Factories\UserFactory;
use Workbench\App\Game\Stat\Validators\Health;

class StatTest extends TestCase
{
    public function testCanGetStats(): void
    {
        $user = UserFactory::new()->create();
        $this->assertInstanceOf(User::class, $user);

        $this->assertFalse(StatInstance::query()->exists());

        $this->assertInstanceOf(Collection::class, $user->stats);

        $this->assertTrue(StatInstance::query()->exists());

        $this->assertEquals([], $user->stats->toArray());
        $user->stats->put('test', 123);
        $this->assertEquals(['test' => 123], $user->stats->toArray());

        $user->save();

        $statInstance = StatInstance::query()
            ->where('model_type', $user::class)
            ->where('model_id', $user->id)
            ->first();

        $this->assertNotNull($statInstance);
        $this->assertEquals($statInstance->data->toArray(), $user->stats->toArray());

        $statInstance->data = $statInstance->data->map(fn ($item) => $item + 1);
        $this->assertEquals(['test' => 124], $statInstance->data->toArray());
        $statInstance->save();

        $user->refresh();

        $this->assertInstanceOf(Collection::class, $user->stats);
        $this->assertEquals($statInstance->data->toArray(), $user->stats->toArray());

        $instance = $user->whereHas('stats', function($query) {
            $query->where('data->test', '<', 135);
        })->first();
        $this->assertNotNull($instance);

        $instance = $user->whereHas('stats', function($query) {
            $query->where('data->test', '>', 125);
        })->first();
        $this->assertNull($instance);
    }

    public function testDefaultStatsCreated(): void
    {
        $user = UserFactory::new()->create();
        $this->assertInstanceOf(User::class, $user);

        $health = Stat::create([
            'name' => 'health',
            'model_type' => $user::class,
            'class' => Health::class,
        ]);
        $this->assertInstanceOf(Stat::class, $health);

        $points = Stat::create([
            'name' => 'points',
            'model_type' => $user::class,
        ]);
        $this->assertInstanceOf(Stat::class, $points);

        $this->assertFalse(StatInstance::query()->exists());

        $this->assertInstanceOf(Collection::class, $user->stats);

        $this->assertTrue(StatInstance::query()->exists());

        // only health, the points stat has no class handler, therefore no default value
        $this->assertEquals(['health' => 100], $user->stats->toArray());

        $user->stats->put('points', 5);
        $this->assertEquals(['health' => 100, 'points' => 5], $user->stats->toArray());

        $user->stats->put('health', 105);
        // $user->save();
        $this->assertEquals(100, $user->stats['health']);

        $user->stats->put('health', -50);
        $user->save();
        $this->assertEquals(0, $user->stats['health']);
    }

    public function testStatServicePopulated(): void
    {
        $statService = new StatService();
        $this->assertCount(0, $statService->stats);

        $user = UserFactory::new()->create();
        $this->assertInstanceOf(User::class, $user);
        $this->assertInstanceOf(ValidatedCollection::class, $user->stats);
        $this->assertEquals([], $user->stats->toArray());

        Stat::create([
            'name' => 'health',
            'model_type' => $user::class,
            'class' => Health::class,
        ]);

        $statService = new StatService();
        $this->assertCount(1, $statService->stats);
        $this->assertNotNull($this);

        $user->save();

        $this->assertEquals(['health' => 100], $user->stats->toArray());
    }
}
