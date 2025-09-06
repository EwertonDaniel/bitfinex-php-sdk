<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Entities;

use EwertonDaniel\Bitfinex\Helpers\GetThis;

/**
 * Class Liquidation
 *
 * Represents a liquidation event entry from Bitfinex public API.
 * Only known indexes are mapped; missing values remain null.
 *
 * Per docs (rest-public-liquidations):
 * [0] ID (int)
 * [1] MTS (int)
 * [2] AMOUNT (float)
 * [3] PRICE (float)
 */
class Liquidation
{
    /** Position ID */
    public readonly ?int $posId;

    /** Millisecond epoch timestamp */
    public readonly ?int $mts;

    /** Trading pair (e.g., tBTCUSD) */
    public readonly ?string $symbol;

    /** Size of the position; >0 long, <0 short */
    public readonly ?float $amount;

    /** Entry price at which user entered the position */
    public readonly ?float $basePrice;

    /** 0: initial liquidation trigger | 1: market execution */
    public readonly ?int $isMatch;

    /** 0: acquired by system | 1: direct sell into the market */
    public readonly ?int $isMarketSold;

    /** Price at which the position has been acquired */
    public readonly ?float $priceAcquired;

    public function __construct(array $data)
    {
        $this->posId = GetThis::ifTrueOrFallback(isset($data[1]), fn () => (int) $data[1]);
        $this->mts = GetThis::ifTrueOrFallback(isset($data[2]), fn () => (int) $data[2]);
        $this->symbol = GetThis::ifTrueOrFallback(isset($data[4]), fn () => (string) $data[4]);
        $this->amount = GetThis::ifTrueOrFallback(isset($data[5]), fn () => (float) $data[5]);
        $this->basePrice = GetThis::ifTrueOrFallback(isset($data[6]), fn () => (float) $data[6]);
        $this->isMatch = GetThis::ifTrueOrFallback(isset($data[8]), fn () => (int) $data[8]);
        $this->isMarketSold = GetThis::ifTrueOrFallback(isset($data[9]), fn () => (int) $data[9]);
        $this->priceAcquired = GetThis::ifTrueOrFallback(isset($data[11]), fn () => (float) $data[11]);
    }
}
