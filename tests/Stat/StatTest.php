<?php

declare(strict_types=1);

namespace PbbgEngine\Tests\Stat;

use Illuminate\Support\Collection;
use PbbgEngine\Attribute\AttributeServiceProvider;
use PbbgEngine\Attribute\Exceptions\InvalidAttributeHandler;
use PbbgEngine\Stat\Models\Stats;
use PbbgEngine\Stat\StatService;
use PbbgEngine\Stat\StatServiceProvider;
use PbbgEngine\Stat\Support\ValidatedCollection;
use PbbgEngine\Tests\TestCase;
use Workbench\App\Game\Stat\Validators\Health;
use Workbench\App\Models\User;
use Workbench\Database\Factories\UserFactory;

class StatTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            AttributeServiceProvider::class,
            StatServiceProvider::class,
        ];
    }

    public function testCanGetStats(): void
    {
        $user = UserFactory::new()->create();
        $this->assertInstanceOf(User::class, $user);

        $this->assertFalse(Stats::query()->exists());

        $this->assertInstanceOf(Collection::class, $user->stats);

        $this->assertTrue(Stats::query()->exists());

        $this->assertEquals([], $user->stats->toArray());
        $user->stats->put('test', 123);
        $this->assertEquals(['test' => 123], $user->stats->toArray());

        $user->save();

        $statInstance = Stats::query()
            ->where('model_type', $user::class)
            ->where('model_id', $user->id)
            ->first();

        $this->assertNotNull($statInstance);
        $this->assertEquals($statInstance->stats->toArray(), $user->stats->toArray());

        $statInstance->stats = $statInstance->stats->map(fn ($item) => $item + 1);
        $this->assertEquals(['test' => 124], $statInstance->stats->toArray());
        $statInstance->save();

        $user->refresh();

        $this->assertInstanceOf(Collection::class, $user->stats);
        $this->assertEquals($statInstance->stats->toArray(), $user->stats->toArray());

        $instance = $user->whereHas('stats', function($query) {
            $query->where('stats->test', '<', 135);
        })->first();
        $this->assertNotNull($instance);

        $instance = $user->whereHas('stats', function($query) {
            $query->where('stats->test', '>', 125);
        })->first();
        $this->assertNull($instance);
    }

    public function testDefaultStatsCreated(): void
    {
        $user = UserFactory::new()->create();
        $this->assertInstanceOf(User::class, $user);

        $service = app(StatService::class);
        $service->handlers[$user::class] = ['health' => Health::class];

        $this->assertFalse(Stats::query()->exists());

        $this->assertInstanceOf(ValidatedCollection::class, $user->stats);

        $this->assertTrue(Stats::query()->exists());

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
        $statService = app(StatService::class);
        $this->assertCount(0, $statService->handlers);

        $user = UserFactory::new()->create();
        $this->assertInstanceOf(User::class, $user);
        $this->assertInstanceOf(ValidatedCollection::class, $user->stats);
        $this->assertEquals([], $user->stats->toArray());

        $statService->handlers[$user::class] = ['health' => Health::class];
        $this->assertCount(1, $statService->handlers[$user::class]);
        $this->assertNotNull($this);

        $user->save();

        $this->assertEquals(['health' => 100], $user->stats->toArray());
    }

    public function testObserverGetsBooted(): void
    {
        /** @var StatService $service */
        $service = app(StatService::class);

        $this->assertCount(0, $service->booted);

        $user = UserFactory::new()->createOne();
        $this->assertInstanceOf(ValidatedCollection::class, $user->stats);

        $this->assertCount(1, $service->booted);
    }

    public function testInvalidAttributeHandler(): void
    {
        $user = UserFactory::new()->create();
        $this->assertInstanceOf(User::class, $user);


        $this->assertThrows(function() use ($user) {
            $service = app(StatService::class);
            // @phpstan-ignore-next-line
            $service->handlers[$user::class] = ['energy' => 'invalid'];
            // @phpstan-ignore-next-line
            $user->stats; // triggers the observer
        }, InvalidAttributeHandler::class);
    }
}
