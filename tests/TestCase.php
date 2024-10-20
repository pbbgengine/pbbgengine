<?php

declare(strict_types=1);

namespace PbbgEngine\Tests;

use Illuminate\Support\MessageBag;
use Orchestra\Testbench\TestCase as Orchestra;
use function Orchestra\Testbench\workbench_path;

class TestCase extends Orchestra
{
    public function setUp(): void
    {
        parent::setUp();
    }

    public function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadMigrationsFrom(workbench_path('database/migrations'));
    }

    public function assertHasErrors(MessageBag $messages): void
    {
        $this->assertTrue($messages->has('errors'));
    }

    public function assertHasNoErrors(MessageBag $messages): void
    {
        $this->assertFalse($messages->has('errors'));
    }
}
