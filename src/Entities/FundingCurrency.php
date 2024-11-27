<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Entities;

use EwertonDaniel\Bitfinex\Helpers\GetThis;
use GuzzleHttp\Utils;
use Illuminate\Support\Arr;

/**
 * Class FundingCurrency
 *
 * Represents funding currency data on the Bitfinex platform, providing details about
 * currency metrics such as bids, asks, daily changes, and trading volumes.
 *
 * Key Features:
 * - Handles flash return rate (FRR) data.
 * - Tracks bid and ask sizes, prices, and periods.
 * - Provides daily trading statistics such as volume, high, low, and price changes.
 * - Includes functionality to convert the entity to an array or JSON string.
 *
 * @author Ewerton Daniel
 *
 * @contact contact@ewertondaniel.work
 */
class FundingCurrency
{
    /** Currency platform symbol (e.g., fUSD, fEUR, fBTC, fETH). */
    public readonly string $symbol;

    /** Currency code (e.g., USD, EUR, BTC, ETH). */
    public readonly string $currency;

    /** Flash Return Rate - average of all fixed-rate funding over the last hour. */
    public readonly ?float $frr;

    /** Price of the last highest bid. */
    public readonly ?float $bid;

    /** Bid period covered in days. */
    public readonly ?float $bidPeriod;

    /** Sum of the 25 highest bid sizes. */
    public readonly ?float $bidSize;

    /** Price of the last lowest ask. */
    public readonly ?float $ask;

    /** Ask period covered in days. */
    public readonly ?float $askPeriod;

    /** Sum of the 25 lowest ask sizes. */
    public readonly ?float $askSize;

    /** Amount the last price has changed since yesterday. */
    public readonly ?float $dailyChange;

    /** Relative price change since yesterday (multiplied by 100 for percentage). */
    public readonly ?float $dailyChangePercentage;

    /** Price of the last trade. */
    public readonly ?float $lastPrice;

    /** Daily trading volume. */
    public readonly ?float $volume;

    /** Daily high price. */
    public readonly ?float $high;

    /** Daily low price. */
    public readonly ?float $low;

    /** Amount of funding available at the Flash Return Rate. */
    private float $freeAmount;

    /**
     * Constructs a FundingCurrency entity using the provided symbol and data.
     *
     * @param  string  $symbol  The platform symbol (e.g., fUSD).
     * @param  array  $data  Array containing funding currency details:
     *                       - [0]: Flash Return Rate (float).
     *                       - [1]: Bid price (float).
     *                       - [2]: Bid period in days (float).
     *                       - [3]: Sum of 25 highest bid sizes (float).
     *                       - [4]: Ask price (float).
     *                       - [5]: Ask period in days (float).
     *                       - [6]: Sum of 25 lowest ask sizes (float).
     *                       - [7]: Daily change (float).
     *                       - [8]: Daily change percentage (float).
     *                       - [9]: Last trade price (float).
     *                       - [10]: Daily volume (float).
     *                       - [11]: Daily high price (float).
     *                       - [12]: Daily low price (float).
     *                       - [15]: Free amount available at FRR (float).
     */
    public function __construct(string $symbol, array $data)
    {
        $this->currency = GetThis::ifTrueOrFallback(
            boolean: str_starts_with($symbol, 'f'),
            callback: fn () => substr($symbol, 1),
            fallback: $symbol
        );
        $this->symbol = GetThis::ifTrueOrFallback(
            boolean: str_starts_with($symbol, 'f'),
            callback: $symbol,
            fallback: fn () => "f$symbol"
        );
        $this->frr = Arr::get($data, 0);
        $this->bid = Arr::get($data, 1);
        $this->bidPeriod = Arr::get($data, 2);
        $this->bidSize = Arr::get($data, 3);
        $this->ask = Arr::get($data, 4);
        $this->askPeriod = Arr::get($data, 5);
        $this->askSize = Arr::get($data, 6);
        $this->dailyChange = Arr::get($data, 7);
        $this->dailyChangePercentage = Arr::get($data, 8);
        $this->lastPrice = Arr::get($data, 9);
        $this->volume = Arr::get($data, 10);
        $this->high = Arr::get($data, 11);
        $this->low = Arr::get($data, 12);
        $this->freeAmount = Arr::get($data, 15);
    }

    /**
     * Converts the FundingCurrency entity to a JSON string.
     *
     * @return string JSON representation of the entity.
     */
    public function __toString(): string
    {
        return Utils::jsonEncode($this->toArray());
    }

    /**
     * Converts the FundingCurrency entity to an associative array.
     *
     * @return array Associative array representation of the entity.
     */
    final public function toArray(): array
    {
        return get_object_vars($this);
    }
}
