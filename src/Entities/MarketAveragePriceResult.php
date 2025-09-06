<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Entities;

/**
 * Represents the result of Market Average Price calculation.
 * Response fields depend on the payload; raw content is preserved.
 */
class MarketAveragePriceResult
{
    public readonly mixed $result;

    public function __construct(mixed $result)
    {
        $this->result = $result;
    }
}

