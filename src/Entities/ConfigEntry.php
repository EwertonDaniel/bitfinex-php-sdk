<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Entities;

/**
 * Represents a single configuration entry returned by the conf endpoint.
 * Only pairs with existing values are materialized.
 */
class ConfigEntry
{
    public readonly string $key;
    public readonly mixed $value;

    public function __construct(string $key, mixed $value)
    {
        $this->key = $key;
        $this->value = $value;
    }
}

