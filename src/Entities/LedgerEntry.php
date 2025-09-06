<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Entities;

/**
 * Represents a single ledger entry from authenticated ledgers endpoint.
 * Schema varies; raw data preserved.
 */
class LedgerEntry
{
    /** @var array<int, mixed> */
    public readonly array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }
}

