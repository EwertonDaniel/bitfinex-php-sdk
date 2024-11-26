<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Entities;

/**
 * Class TickerHistory
 *
 * Represents historical data for a ticker on the Bitfinex platform.
 * Provides details about:
 * - The trading symbol and pair.
 * - Bid and ask prices.
 * - A timestamp indicating when the data was recorded.
 *
 * Key Features:
 * - Encapsulates key metrics for analyzing historical market data.
 * - Simplifies data handling for ticker-related operations.
 *
 * @author Ewerton Daniel
 * @contact contact@ewertondaniel.work
 */
class TickerHistory
{
    /** Symbol of the requested ticker history data (e.g., "tBTCUSD"). */
    public readonly string $symbol;

    /** Trading pair (e.g., "BTCUSD"). */
    public readonly string $pair;

    /** Bid price. */
    public readonly float $bid;

    /** Price of the last lowest ask. */
    public readonly float $ask;

    /** Millisecond epoch timestamp of the data. */
    public readonly int $mts;

    /**
     * Constructs a TickerHistory entity using provided data.
     *
     * @param  array  $data  Array containing ticker history details:
     *                       - [0]: Symbol (string).
     *                       - [1]: Bid price (float).
     *                       - [3]: Ask price (float).
     *                       - [12]: Millisecond epoch timestamp (int).
     */
    public function __construct(array $data)
    {
        $this->pair = substr($data[0], 1);
        $this->symbol = $data[0];
        $this->bid = $data[1];
        $this->ask = $data[3];
        $this->mts = $data[12];
    }
}
