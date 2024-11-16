<?php

declare(strict_types=1);

namespace PbbgEngine\Attribute;

use InvalidArgumentException;

class AttributeRegistry
{
    /**
     * The handlers for each registered attribute type.
     *
     * @var array<string, AttributeTypeHandler>
     */
    public array $handlers = [];

    /**
     * Add a new attribute type.
     */
    public function registerType(string $type): void
    {
        if (isset($this->handlers[$type])) {
            throw new InvalidArgumentException("Attribute type $type already exists.");
        }

        $this->handlers[$type] = new AttributeTypeHandler();
    }
}
