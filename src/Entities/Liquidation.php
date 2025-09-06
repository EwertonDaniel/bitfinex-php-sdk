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
 * Common fields (best-effort based on docs):
 * [0] ID (int)
 * [1] MTS (int)
 * [2] SYMBOL (string)
 * [3] AMOUNT (float)
 * [4] BASE_PRICE (float)
 */
class Liquidation
{
    public readonly ?int $id;
    public readonly ?int $mts;
    public readonly ?string $symbol;
    public readonly ?float $amount;
    public readonly ?float $basePrice;

    public function __construct(array $data)
    {
        $this->id = GetThis::ifTrueOrFallback(isset($data[0]), fn () => (int) $data[0]);
        $this->mts = GetThis::ifTrueOrFallback(isset($data[1]), fn () => (int) $data[1]);
        $this->symbol = GetThis::ifTrueOrFallback(isset($data[2]), fn () => (string) $data[2]);
        $this->amount = GetThis::ifTrueOrFallback(isset($data[3]), fn () => (float) $data[3]);
        $this->basePrice = GetThis::ifTrueOrFallback(isset($data[4]), fn () => (float) $data[4]);
    }
}

