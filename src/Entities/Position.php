<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Entities;

use EwertonDaniel\Bitfinex\Helpers\GetThis;

/**
 * Position entity for authenticated position endpoints.
 * Common indexes:
 * [0] SYMBOL, [1] STATUS, [2] AMOUNT, [3] BASE_PRICE, [6] PL, [7] PL_PERC, [8] PRICE_LIQ, [9] LEVERAGE
 */
class Position
{
    public readonly ?string $symbol;
    public readonly ?string $status;
    public readonly ?float $amount;
    public readonly ?float $basePrice;
    public readonly ?float $pl;
    public readonly ?float $plPerc;
    public readonly ?float $priceLiq;
    public readonly ?float $leverage;

    public function __construct(array $data)
    {
        $this->symbol = GetThis::ifTrueOrFallback(isset($data[0]), fn () => (string) $data[0]);
        $this->status = GetThis::ifTrueOrFallback(isset($data[1]), fn () => (string) $data[1]);
        $this->amount = GetThis::ifTrueOrFallback(isset($data[2]), fn () => (float) $data[2]);
        $this->basePrice = GetThis::ifTrueOrFallback(isset($data[3]), fn () => (float) $data[3]);
        $this->pl = GetThis::ifTrueOrFallback(isset($data[6]), fn () => (float) $data[6]);
        $this->plPerc = GetThis::ifTrueOrFallback(isset($data[7]), fn () => (float) $data[7]);
        $this->priceLiq = GetThis::ifTrueOrFallback(isset($data[8]), fn () => (float) $data[8]);
        $this->leverage = GetThis::ifTrueOrFallback(isset($data[9]), fn () => (float) $data[9]);
    }
}

