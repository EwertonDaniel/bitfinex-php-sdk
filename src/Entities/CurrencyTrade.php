<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Entities;

use Carbon\Carbon;

/**
 * Class CurrencyTrade
 *
 * Represents a trade transaction for a specific currency pair on the Bitfinex platform.
 * Provides structured information about the trade, including the pair, trade ID, timestamp,
 * trade amount, rate, and period.
 *
 * Key Details:
 * - The `pair` identifies the trading pair (e.g., 'BTCUSD').
 * - The `amount` indicates the trade volume (positive for buy, negative for sell).
 * - The `rate` and `period` are specific to funding transactions.
 *
 * This entity is designed to encapsulate trade data for further analysis or processing.
 *
 * @author Ewerton Daniel
 *
 * @contact contact@ewertondaniel.work
 */
class CurrencyTrade
{
    /** Trading pair (e.g., 'BTCUSD'). */
    public readonly string $pair;

    /** Unique identifier of the trade. */
    public readonly int $id;

    /** Datetime of the trade. */
    public readonly Carbon $datetime;

    /** Amount traded (positive for buy, negative for sell). */
    public readonly float $amount;

    /** Rate at which the funding transaction occurred. */
    public readonly float $rate;

    /** Duration of the funding transaction (in days). */
    public readonly int $period;

    /**
     * Constructs a CurrencyTrade entity with the provided data.
     *
     * @param  string  $symbol  The trading symbol (e.g., 'tBTCUSD').
     * @param  array  $data  Array containing:
     *                       - [0] => id (int): Trade ID.
     *                       - [1] => mts (int): Millisecond epoch timestamp.
     *                       - [2] => amount (float): Trade amount.
     *                       - [3] => rate (float): Funding rate.
     *                       - [4] => period (int): Funding period.
     */
    public function __construct(string $symbol, array $data)
    {
        $this->pair = substr($symbol, 1);
        $this->id = $data[0];
        $this->datetime = Carbon::createFromTimestampMs($data[1]);
        $this->amount = $data[2];
        $this->rate = $data[3];
        $this->period = $data[4];
    }
}
