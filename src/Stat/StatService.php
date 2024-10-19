<?php

declare(strict_types=1);

namespace PbbgEngine\Stat;

use PbbgEngine\Attribute\AttributeService;
use PbbgEngine\Stat\Observers\StatsProxyObserver;
use PbbgEngine\Stat\Validators\Validator;

/**
 * @extends AttributeService<Validator, StatsProxyObserver>
 */
class StatService extends AttributeService
{
    /**
     * The stat handlers must be of type Validator.
     *
     * @var class-string<Validator>
     */
    public string $handler = Validator::class;

    /**
     * The stat observer to use.
     *
     * @var class-string<StatsProxyObserver>
     */
    public string $observer = StatsProxyObserver::class;
}
