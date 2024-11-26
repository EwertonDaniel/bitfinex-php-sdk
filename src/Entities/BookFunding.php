<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Entities;

/**
 * Class BookFunding
 *
 * Represents a funding currency book entry in the Bitfinex platform, providing details
 * about the currency, rate, period, order count, and total amount available at a specific
 * rate level. This entity is commonly used to analyze funding rates and availability.
 *
 * @author  Ewerton Daniel
 * @contact contact@ewertondaniel.work
 */
class BookFunding
{
    /** Currency symbol (e.g., 'USD'). */
    public readonly string $currency;

    /** Rate level. */
    public readonly float $rate;

    /** Period level. */
    public readonly int $period;

    /** Number of orders at this rate level. */
    public readonly int $count;

    /** Total amount available at this rate level. */
    public readonly float $amount;

    /**
     * Initializes a funding currency book entry with details retrieved from the Bitfinex API.
     *
     * @param  string  $symbol  The funding symbol (e.g., 'fUSD').
     * @param  array  $data  Array containing the book entry details:
     *                       - [0] => rate (float): Rate level.
     *                       - [1] => period (int): Period level.
     *                       - [2] => count (int): Number of orders at this rate level.
     *                       - [3] => amount (float): Total amount available at this rate level.
     */
    public function __construct(string $symbol, array $data)
    {
        $this->currency = substr($symbol, 1);
        $this->rate = (float) $data[0];
        $this->period = (int) $data[1];
        $this->count = (int) $data[2];
        $this->amount = (float) $data[3];
    }
}
