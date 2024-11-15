<?php

declare(strict_types=1);

namespace PbbgEngine\Attribute;

class AttributeManager
{
    /**
     * The services that handle each type of attribute.
     *
     * @var array<string, AttributeService>
     */
    public array $types = [];
}
