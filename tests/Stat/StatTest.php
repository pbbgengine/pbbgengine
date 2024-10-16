<?php

declare(strict_types=1);

namespace PbbgEngine\Tests\Stat;

use Illuminate\Support\Collection;
use PbbgEngine\Stat\Models\StatInstance;
use PbbgEngine\Tests\TestCase;
use Workbench\App\Models\User;
use Workbench\Database\Factories\UserFactory;

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

        $this->assertEquals($statInstance->data->toArray(), $user->stats->toArray());
    }
}
