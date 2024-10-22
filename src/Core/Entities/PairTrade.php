<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Core\Entities;

use EwertonDaniel\Bitfinex\Helpers\GetThis;

class PairTrade
{
    /** @note Pair of the trade */
    public readonly string $pair;
    /** @note Symbol of the trade */
    public readonly string $symbol;
    /** @note ID of the trade */
    public readonly int $id;
    /** @note Millisecond epoch timestamp */
    public readonly int $mts;
    /** @note How much was bought (positive) or sold (negative) */
    public readonly float $amount;
    /** @note Price at which the trade was executed */
    public readonly float $price;

    public function __construct(string $symbol, array $data)
    {
        $this->pair = GetThis::ifTrueOrFallback(
            boolean: str_starts_with($symbol, 't'),
            callback: fn() => substr($symbol, 1),
            fallback: $symbol
        );
        $this->symbol = GetThis::ifTrueOrFallback(
            boolean: str_starts_with($symbol, 't'),
            callback: $symbol,
            fallback: fn() => "t$symbol"
        );
        $this->id = $data[0];
        $this->mts = $data[1];
        $this->amount = $data[2];
        $this->price = $data[3];
    }
}
