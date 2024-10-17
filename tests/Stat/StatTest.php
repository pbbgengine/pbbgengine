<?php

declare(strict_types=1);

namespace PbbgEngine\Tests\Stat;

use Illuminate\Support\Collection;
use PbbgEngine\Stat\Models\Stat;
use PbbgEngine\Stat\Models\StatInstance;
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

        $stat = Stat::create([
            'name' => 'health',
            'model_type' => $user::class,
            'class' => Health::class,
        ]);
        $this->assertInstanceOf(Stat::class, $stat);

        $this->assertFalse(StatInstance::query()->exists());

        $this->assertInstanceOf(Collection::class, $user->stats);

        $this->assertTrue(StatInstance::query()->exists());

        $this->assertEquals(['health' => 100], $user->stats->toArray());

    }
}
