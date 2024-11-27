<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Entities;

use EwertonDaniel\Bitfinex\Enums\BitfinexAction;

/**
 * Class BookTrading
 *
 * Represents a trading pair book entry in the Bitfinex platform, containing details
 * about the trading pair, price level, order count, total available amount, and action type.
 * The action type (bid or ask) is determined based on the amount's sign.
 *
 * This entity is used to manage and analyze order book data for trading pairs.
 *
 * @author  Ewerton Daniel
 *
 * @contact contact@ewertondaniel.work
 */
class BookTrading
{
    /** Trading pair (e.g., 'BTCUSD'). */
    public readonly string $pair;

    /** Price level. */
    public readonly float $price;

    /** Number of orders at this price level. */
    public readonly int $count;

    /** Total amount available at this price level. */
    public readonly float $amount;

    /** Action type derived from amount (bid or ask). */
    public readonly string $type;

    /**
     * Initializes a trading pair book entry with data from the Bitfinex API.
     *
     * @param  string  $symbol  The trading symbol (e.g., 'tBTCUSD').
     * @param  array  $data  Array containing the book entry details:
     *                       - [0] => price (float): Price level.
     *                       - [1] => count (int): Number of orders at this price level.
     *                       - [2] => amount (float): Total amount available at this price level.
     */
    public function __construct(string $symbol, array $data)
    {
        $this->pair = substr($symbol, 1);
        $this->price = (float) $data[0];
        $this->count = (int) $data[1];
        $this->amount = (float) $data[2];
        $this->type = BitfinexAction::bidOrAskByAmount($this->amount);
    }
}
