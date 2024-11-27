<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Entities;

use EwertonDaniel\Bitfinex\Helpers\GetThis;
use GuzzleHttp\Utils;
use Illuminate\Support\Arr;

/**
 * Class TradingPair
 *
 * Represents data for a trading pair on the Bitfinex platform, encapsulating key metrics such as bid, ask, daily changes, and volumes.
 *
 * Key Features:
 * - Tracks the symbol and pair information (e.g., tBTCUSD -> BTCUSD).
 * - Provides daily statistics including price changes, high, low, and volume.
 * - Handles bid and ask prices and their respective sizes.
 * - Converts the entity to JSON or an array for easy integration.
 *
 * @author Ewerton Daniel
 *
 * @contact contact@ewertondaniel.work
 */
class TradingPair
{
    /** Symbol, e.g., tEURUSD, tBTCUSD, tETHUSD. */
    public readonly string $symbol;

    /** Pair, e.g., EURUSD, BTCUSD, ETHUSD. */
    public readonly string $pair;

    /** Price of the last highest bid. */
    public readonly ?float $bid;

    /** Sum of the 25 highest bid sizes. */
    public readonly ?float $bidSize;

    /** Price of the last lowest ask. */
    public readonly ?float $ask;

    /** Sum of the 25 lowest ask sizes. */
    public readonly ?float $askSize;

    /** Amount that the last price has changed since yesterday. */
    public readonly ?float $dailyChange;

    /** Relative price change since yesterday (*100 for percentage change). */
    public readonly ?float $dailyChangePercentage;

    /** Price of the last trade. */
    public readonly ?float $lastPrice;

    /** Daily trading volume. */
    public readonly ?float $volume;

    /** Daily high price. */
    public readonly ?float $high;

    /** Daily low price. */
    public readonly ?float $low;

    /**
     * Constructs a TradingPair entity using provided data.
     *
     * @param  string  $symbol  The trading pair symbol (e.g., tBTCUSD).
     * @param  array  $data  Array containing trading pair details:
     *                       - [0]: Bid price (float).
     *                       - [1]: Sum of the 25 highest bid sizes (float).
     *                       - [2]: Ask price (float).
     *                       - [3]: Sum of the 25 lowest ask sizes (float).
     *                       - [4]: Daily change amount (float).
     *                       - [5]: Daily change percentage (float).
     *                       - [6]: Last trade price (float).
     *                       - [7]: Daily volume (float).
     *                       - [8]: Daily high price (float).
     *                       - [9]: Daily low price (float).
     */
    public function __construct(string $symbol, array $data)
    {
        $this->pair = GetThis::ifTrueOrFallback(
            boolean: str_starts_with($symbol, 't'),
            callback: fn () => substr($symbol, 1),
            fallback: $symbol
        );
        $this->symbol = GetThis::ifTrueOrFallback(
            boolean: str_starts_with($symbol, 't'),
            callback: $symbol,
            fallback: fn () => "t$symbol"
        );
        $this->bid = Arr::get($data, 0);
        $this->bidSize = Arr::get($data, 1);
        $this->ask = Arr::get($data, 2);
        $this->askSize = Arr::get($data, 3);
        $this->dailyChange = Arr::get($data, 4);
        $this->dailyChangePercentage = Arr::get($data, 5);
        $this->lastPrice = Arr::get($data, 6);
        $this->volume = Arr::get($data, 7);
        $this->high = Arr::get($data, 8);
        $this->low = Arr::get($data, 9);
    }

    /**
     * Converts the TradingPair entity to a JSON string.
     *
     * @return string JSON representation of the entity.
     */
    public function __toString(): string
    {
        return Utils::jsonEncode($this->toArray());
    }

    /**
     * Converts the TradingPair entity to an associative array.
     *
     * @return array Associative array representation of the entity.
     */
    final public function toArray(): array
    {
        return get_object_vars($this);
    }
}
