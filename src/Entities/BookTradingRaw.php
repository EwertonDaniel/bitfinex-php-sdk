<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Entities;

use EwertonDaniel\Bitfinex\Enums\BitfinexAction;

/**
 * Class BookTradingRaw
 *
 * Represents a single order of a trading pair raw book (precision `R0`).
 *
 * Raw books are not aggregated: each row is one order rather than a price level,
 * so the row carries an order id and has no order count. The aggregated layout
 * lives in `BookTrading`.
 *
 * @author  Ewerton Daniel
 *
 * @contact contact@ewertondaniel.work
 *
 * @link https://docs.bitfinex.com/reference/rest-public-book
 */
class BookTradingRaw
{
    /** Trading pair (e.g., 'BTCUSD'). */
    public readonly string $pair;

    /** Identifier of the order. */
    public readonly int $orderId;

    /** Price of the order. */
    public readonly float $price;

    /** Amount of the order. */
    public readonly float $amount;

    /** Action type derived from amount (bid or ask). */
    public readonly string $type;

    /**
     * Initializes a trading pair raw book entry with data from the Bitfinex API.
     *
     * @param  string  $symbol  The trading symbol (e.g., 'tBTCUSD').
     * @param  array  $data  Array containing the raw book entry details:
     *                       - [0] => order_id (int): Identifier of the order.
     *                       - [1] => price (float): Price of the order.
     *                       - [2] => amount (float): Amount of the order.
     */
    public function __construct(string $symbol, array $data)
    {
        $this->pair = substr($symbol, 1);
        $this->orderId = (int) $data[0];
        $this->price = (float) $data[1];
        $this->amount = (float) $data[2];
        $this->type = BitfinexAction::bidOrAskByAmount($this->amount);
    }
}
