<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Entities;

use Illuminate\Support\Arr;

/**
 * Class ForeignExchangeRate
 *
 * Represents the foreign exchange rate between two currencies on the Bitfinex platform.
 * Provides information about:
 * - The base currency (e.g., 'USD').
 * - The quote currency (e.g., 'EUR').
 * - The current exchange rate between the currencies (if available).
 *
 * This entity is useful for converting amounts between currencies or analyzing market trends.
 *
 * @author Ewerton Daniel
 *
 * @contact contact@ewertondaniel.work
 */
class ForeignExchangeRate
{
    /** The base currency (e.g., 'USD'). */
    public readonly string $baseCurrency;

    /** The quote currency (e.g., 'EUR'). */
    public readonly string $quoteCurrency;

    /** The current exchange rate between the base and quote currencies. */
    public readonly ?float $currentRate;

    /**
     * Constructs a ForeignExchangeRate entity with provided data.
     *
     * @param  string  $baseCurrency  The base currency.
     * @param  string  $quoteCurrency  The quote currency.
     * @param  array  $data  An array containing:
     *                       - [0]: Current exchange rate (nullable float).
     */
    public function __construct(string $baseCurrency, string $quoteCurrency, array $data)
    {
        $this->baseCurrency = $baseCurrency;
        $this->quoteCurrency = $quoteCurrency;
        $this->currentRate = Arr::get($data, 0);
    }
}
