<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Core\Entities;

class TickerHistory
{
    /** @note Pair of the requested ticker history data */
    public readonly string $pair;

    /** @note Symbol of the requested ticker history data */
    public readonly string $symbol;

    public readonly float $bid;

    /** @note Price of last lowest ask */
    public readonly float $ask;

    /** @note Millisecond epoch timestamp */
    public readonly int $mts;

    public function __construct(array $data)
    {
        $this->pair = substr($data[0], 1);
        $this->symbol = $data[0];
        $this->bid = $data[1];
        $this->ask = $data[3];
        $this->mts = $data[12];
    }
}
