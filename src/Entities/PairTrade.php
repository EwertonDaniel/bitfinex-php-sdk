<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Entities;

use Illuminate\Support\Carbon;

/**
 * Class PairTrade
 *
 * Represents a trade executed on a specific trading pair on the Bitfinex platform.
 * Provides structured information about the trade, including:
 * - The trading pair (e.g., BTCUSD).
 * - Trade ID, timestamp, amount, and price.
 *
 * This entity is useful for analyzing trade activity and tracking executed trades.
 *
 * @author Ewerton Daniel
 *
 * @contact contact@ewertondaniel.work
 */
class PairTrade
{
    /** Trading pair (e.g., BTCUSD). */
    public readonly string $pair;

    /** Unique identifier of the trade. */
    public readonly int $id;

    /** Millisecond epoch timestamp of the trade. */
    public readonly Carbon $datetime;

    /** Amount traded (positive for buy, negative for sell). */
    public readonly float $amount;

    /** Price at which the trade was executed. */
    public readonly float $price;

    /**
     * Constructs a PairTrade entity using data retrieved from the Bitfinex API.
     *
     * @param  string  $symbol  The trading symbol (e.g., tBTCUSD).
     * @param  array  $data  Array containing trade details:
     *                       - [0]: Trade ID (int).
     *                       - [1]: Timestamp of the trade in milliseconds (int).
     *                       - [2]: Amount traded (float, positive for buy, negative for sell).
     *                       - [3]: Execution price (float).
     */
    public function __construct(string $symbol, array $data)
    {
        $this->pair = substr($symbol, 1);
        $this->id = $data[0];
        $this->datetime = Carbon::createFromTimestampMs($data[1]);
        $this->amount = $data[2];
        $this->price = $data[3];
    }
}
