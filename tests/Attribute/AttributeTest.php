<?php

declare(strict_types=1);

namespace PbbgEngine\Tests\Attribute;

use Illuminate\Support\Collection;
use PbbgEngine\Attribute\AttributeManager;
use PbbgEngine\Attribute\AttributeServiceProvider;
use PbbgEngine\Attribute\Exceptions\InvalidAttributeHandler;
use PbbgEngine\Attribute\Models\Attributes;
use PbbgEngine\Attribute\Support\ValidatedAttributes;
use PbbgEngine\Tests\TestCase;
use Workbench\App\Game\Stat\Validators\Health;
use Workbench\App\Models\User;
use Workbench\Database\Factories\UserFactory;

class AttributeTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [AttributeServiceProvider::class];
    }

    public function setUp(): void
    {
        parent::setUp();

        $manager = app(AttributeManager::class);
        $manager->add('stats');
        $manager->add('resources');
    }

    public function testCanGetStats(): void
    {
        $user = UserFactory::new()->create();
        $this->assertInstanceOf(User::class, $user);

        $this->assertFalse(Attributes::query()->exists());

        $this->assertInstanceOf(Collection::class, $user->stats);

        $this->assertTrue(Attributes::query()->exists());

        $this->assertEquals([], $user->stats->toArray());
        $user->stats->put('test', 123);
        $this->assertEquals(['test' => 123], $user->stats->toArray());

        $user->save();

        $statInstance = Attributes::query()
            ->where('name', 'stats')
            ->where('model_type', $user::class)
            ->where('model_id', $user->id)
            ->first();

        $this->assertNotNull($statInstance);
        $this->assertEquals($statInstance->attribute->toArray(), $user->stats->toArray());

        $statInstance->attribute = $statInstance->attribute->map(fn ($item) => $item + 1);
        $this->assertEquals(['test' => 124], $statInstance->attribute->toArray());
        $statInstance->save();

        $user->refresh();

        $this->assertInstanceOf(Collection::class, $user->stats);
        $this->assertEquals($statInstance->attribute->toArray(), $user->stats->toArray());

        $instance = $user->whereHas('stats', function($query) {
            $query->where('attribute->test', '<', 135);
        })->first();
        $this->assertNotNull($instance);

        $instance = $user->whereHas('stats', function($query) {
            $query->where('attribute->test', '>', 125);
        })->first();
        $this->assertNull($instance);
    }

    public function testDefaultStatsCreated(): void
    {
        $user = UserFactory::new()->create();
        $this->assertInstanceOf(User::class, $user);

        $service = app(AttributeManager::class)->types['stats'];
        $service->handlers[$user::class] = ['health' => Health::class];

        $this->assertFalse(Attributes::query()->exists());

        $this->assertInstanceOf(ValidatedAttributes::class, $user->stats);

        $this->assertTrue(Attributes::query()->exists());

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
        $service = app(AttributeManager::class)->types['stats'];
        $this->assertCount(0, $service->handlers);

        $user = UserFactory::new()->create();
        $this->assertInstanceOf(User::class, $user);
        $this->assertInstanceOf(ValidatedAttributes::class, $user->stats);
        $this->assertEquals([], $user->stats->toArray());

        $service->handlers[$user::class] = ['health' => Health::class];
        $this->assertCount(1, $service->handlers[$user::class]);
        $this->assertNotNull($this);

        $user->save();

        $this->assertEquals(['health' => 100], $user->stats->toArray());
    }

    public function testObserverGetsBooted(): void
    {
        $service = app(AttributeManager::class)->types['stats'];

        $this->assertCount(0, $service->booted);

        $user = UserFactory::new()->createOne();
        $this->assertInstanceOf(ValidatedAttributes::class, $user->stats);

        $this->assertCount(1, $service->booted);
    }

    public function testInvalidAttributeHandler(): void
    {
        $user = UserFactory::new()->create();
        $this->assertInstanceOf(User::class, $user);


        $this->assertThrows(function() use ($user) {
            $service = app(AttributeManager::class)->types['stats'];
            // @phpstan-ignore-next-line
            $service->handlers[$user::class] = ['energy' => 'invalid'];
            // @phpstan-ignore-next-line
            $user->stats; // triggers the observer
        }, InvalidAttributeHandler::class);
    }

    public function testCanGetStatsAndResources(): void
    {
        $user = UserFactory::new()->create();
        $this->assertInstanceOf(User::class, $user);

        $this->assertFalse($user->stats()->exists());
        $this->assertFalse($user->resources()->exists());

        $this->assertInstanceOf(ValidatedAttributes::class, $user->stats);
        $this->assertInstanceOf(ValidatedAttributes::class, $user->resources);

        $this->assertTrue($user->stats()->exists());
        $this->assertTrue($user->resources()->exists());

        $this->assertEquals([], $user->stats->toArray());
        $this->assertEquals([], $user->resources->toArray());

        $user->stats->put('energy', 10);
        $user->resources->put('money', 1000);

        $this->assertEquals(['energy' => 10], $user->stats->toArray());
        $this->assertEquals(['money' => 1000], $user->resources->toArray());

        $user->save();
        $user->refresh();

        $this->assertInstanceOf(ValidatedAttributes::class, $user->stats);
        $this->assertInstanceOf(ValidatedAttributes::class, $user->resources);

        $this->assertEquals(['energy' => 10], $user->stats->toArray());
        $this->assertEquals(['money' => 1000], $user->resources->toArray());
    }
}
