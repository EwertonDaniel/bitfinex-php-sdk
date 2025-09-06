<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Entities;

/**
 * Represents a single funding statistics row.
 * Schema may vary; the raw row is preserved in $data.
 */
class FundingStat
{
    /** @var array<int, mixed> */
    public readonly array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }
}

