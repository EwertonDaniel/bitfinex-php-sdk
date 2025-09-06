<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Entities;

/**
 * Represents a single leaderboard (rankings) entry.
 * Schema varies by key/timeframe; raw row preserved in $data.
 */
class LeaderboardEntry
{
    /** @var array<int, mixed> */
    public readonly array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }
}

