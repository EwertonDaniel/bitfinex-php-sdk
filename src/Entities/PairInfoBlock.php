<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Entities;

use EwertonDaniel\Bitfinex\Helpers\GetThis;

class PairInfoBlock
{
    public readonly ?float $minOrderSize;
    public readonly ?float $maxOrderSize;
    public readonly ?float $initialMargin;
    public readonly ?float $minMargin;

    /**
     * Expects an array with indexes:
     * [3] MIN_ORDER_SIZE, [4] MAX_ORDER_SIZE, [8] INITIAL_MARGIN, [9] MIN_MARGIN
     */
    public function __construct(array $data)
    {
        $this->minOrderSize = GetThis::ifTrueOrFallback(isset($data[3]), fn () => (float) $data[3]);
        $this->maxOrderSize = GetThis::ifTrueOrFallback(isset($data[4]), fn () => (float) $data[4]);
        $this->initialMargin = GetThis::ifTrueOrFallback(isset($data[8]), fn () => (float) $data[8]);
        $this->minMargin = GetThis::ifTrueOrFallback(isset($data[9]), fn () => (float) $data[9]);
    }
}

