<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Entities;

use Illuminate\Support\Arr;

class ForeignExchangeRate
{
    /** @note base currency */
    public readonly string $baseCurrency;

    /** @note quote currency */
    public readonly string $quoteCurrency;

    /** @note Exchange rate */
    public readonly ?float $currentRate;

    public function __construct(string $baseCurrency, string $quoteCurrency, array $data)
    {
        $this->baseCurrency = $baseCurrency;
        $this->quoteCurrency = $quoteCurrency;
        $this->currentRate = Arr::get($data, 0);
    }
}
