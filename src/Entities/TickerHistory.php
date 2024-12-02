<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Entities;

use Carbon\Carbon;
use EwertonDaniel\Bitfinex\Helpers\GetThis;

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
 *
 * @contact contact@ewertondaniel.work
 */
class TickerHistory
{
    /** Symbol of the requested ticker history data (e.g., "tBTCUSD"). */
    public readonly string $symbol;

    /** Trading pair (e.g., "BTCUSD"). */
    public readonly string $pair;

    /** Price of last highest bid. */
    public readonly float $lastHighestBid;

    /** Price of the last lowest ask. */
    public readonly float $lastLowestAsk;

    public readonly ?Carbon $datetime;

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
        $this->lastHighestBid = $data[1];
        $this->lastLowestAsk = $data[3];
        $this->datetime = GetThis::ifTrueOrFallback(isset($data[12]), fn () => Carbon::createFromTimestampMs($data[12]));
    }
}
