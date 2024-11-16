<?php

declare(strict_types=1);

namespace PbbgEngine\Attribute;

use InvalidArgumentException;

class AttributeManager
{
    /**
     * The services that handle each attribute type.
     *
     * @var array<string, AttributeService>
     */
    public array $types = [];

    /**
     * Add a new attribute type.
     */
    public function add(string $type): void
    {
        if (isset($this->types[$type])) {
            throw new InvalidArgumentException("Attribute type $type already exists.");
        }

        $this->types[$type] = new AttributeService();
    }
}
