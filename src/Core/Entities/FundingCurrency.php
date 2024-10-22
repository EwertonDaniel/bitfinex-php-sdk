<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Core\Entities;

use EwertonDaniel\Bitfinex\Helpers\GetThis;
use GuzzleHttp\Utils;
use Illuminate\Support\Arr;

class FundingCurrency
{
    /** @note Currency ex.:USD,EUR,BTC,ETH */
    public readonly string $currency;
    /** @note Currency platform symbol ex.:fUSD,fEUR,fBTC,fETH */
    public readonly string $symbol;
    /** @note Flash Return Rate - average of all fixed rate funding over the last hour */
    public readonly float|null $frr;
    /** @note Price of last highest bid */
    public readonly float|null $bid;
    /** @note Bid period covered in days */
    public readonly float|null $bidPeriod;
    /** @note Sum of the 25 highest bid sizes */
    public readonly float|null $bidSize;
    /** @note Price of last lowest ask */
    public readonly float|null $ask;
    /** @note Ask period covered in days */
    public readonly float|null $askPeriod;
    /** @note Sum of the 25 lowest ask sizes */
    public readonly float|null $askSize;
    /** @note Amount that the last price has changed since yesterday */
    public readonly float|null $dailyChange;
    /** @note Relative price change since yesterday (*100 for percentage change) */
    public readonly float|null $dailyChangePercentage;
    /** @note Price of the last trade */
    public readonly float|null $lastPrice;
    /** @note Daily volume */
    public readonly float|null $volume;
    /** @note Daily high */
    public readonly float|null $high;
    /** @note Daily low */
    public readonly float|null $low;

    /**@note The amount of funding that is available at the Flash Return Rate */
    private float $freeAmount;

    public function __construct(string $symbol, array $data)
    {
        $this->currency = GetThis::ifTrueOrFallback(
            boolean: str_starts_with($symbol, 'f'),
            callback: fn() => substr($symbol, 1),
            fallback: $symbol
        );
        $this->symbol = GetThis::ifTrueOrFallback(
            boolean: str_starts_with($symbol, 'f'),
            callback: $symbol,
            fallback: fn() => "f$symbol"
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

    public function __toString(): string
    {
        return Utils::jsonEncode($this->toArray());
    }

    final public function toArray(): array
    {
        return get_object_vars($this);
    }
}
