<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Entities;

/**
 * Class BookFundingRaw
 *
 * Represents a single offer of a funding currency raw book (precision `R0`).
 *
 * Raw books are not aggregated: each row is one offer rather than a rate level,
 * so the row carries an offer id and has no order count. The aggregated layout
 * lives in `BookFunding`. Note that the raw layout orders the fields as
 * offer id, period, rate — the aggregated one starts with the rate.
 *
 * @author  Ewerton Daniel
 *
 * @contact contact@ewertondaniel.work
 *
 * @link https://docs.bitfinex.com/reference/rest-public-book
 */
class BookFundingRaw
{
    /** Currency symbol (e.g., 'USD'). */
    public readonly string $currency;

    /** Identifier of the offer. */
    public readonly int $offerId;

    /** Period of the offer, in days. */
    public readonly int $period;

    /** Rate of the offer. */
    public readonly float $rate;

    /** Amount of the offer. */
    public readonly float $amount;

    /**
     * Initializes a funding currency raw book entry with data from the Bitfinex API.
     *
     * @param  string  $symbol  The funding symbol (e.g., 'fUSD').
     * @param  array  $data  Array containing the raw book entry details:
     *                       - [0] => offer_id (int): Identifier of the offer.
     *                       - [1] => period (int): Period of the offer, in days.
     *                       - [2] => rate (float): Rate of the offer.
     *                       - [3] => amount (float): Amount of the offer.
     */
    public function __construct(string $symbol, array $data)
    {
        $this->currency = substr($symbol, 1);
        $this->offerId = (int) $data[0];
        $this->period = (int) $data[1];
        $this->rate = (float) $data[2];
        $this->amount = (float) $data[3];
    }
}
