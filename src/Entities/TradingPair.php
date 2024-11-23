<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Entities;

use EwertonDaniel\Bitfinex\Helpers\GetThis;
use GuzzleHttp\Utils;
use Illuminate\Support\Arr;

class TradingPair
{
    /**@note Pair, ex.: EURUSD, BTCUSD, ETHUSD */
    public readonly string $pair;

    /**@note Symbol, ex.: tEURUSD, tBTCUSD, tETHUSD */
    public readonly string $symbol;

    /**@note Price of last highest bid */
    public readonly ?float $bid;

    /**@note Sum of the 25 highest bid sizes */
    public readonly ?float $bidSize;

    /**@note Price of last lowest ask */
    public readonly ?float $ask;

    /**@note Sum of the 25 lowest ask sizes */
    public readonly ?float $askSize;

    /**@note Amount that the last price has changed since yesterday */
    public readonly ?float $dailyChange;

    /**@note Relative price change since yesterday (*100 for percentage change) */
    public readonly ?float $dailyChangePercentage;

    /**@note Price of the last trade */
    public readonly ?float $lastPrice;

    /**@note Daily volume */
    public readonly ?float $volume;

    /**@note Daily high */
    public readonly ?float $high;

    /**@note Daily low */
    public readonly ?float $low;

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

    public function __toString(): string
    {
        return Utils::jsonEncode($this->toArray());
    }

    final public function toArray(): array
    {
        return get_object_vars($this);
    }
}
