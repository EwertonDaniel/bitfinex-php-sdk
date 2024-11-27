<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Entities;

use EwertonDaniel\Bitfinex\Enums\BitfinexType;

/**
 * Class Alert
 *
 * Represents an alert in the Bitfinex platform. This entity encapsulates
 * details about a price alert, including the symbol, pair, price, and type.
 * The alert is associated with a specific Bitfinex type (e.g., 'trading' or 'funding')
 * and includes additional metadata such as countdown timers and alert type information.
 *
 * The `Alert` class is constructed using an array of alert details retrieved
 * from the Bitfinex API, ensuring proper parsing and structuring of the data.
 *
 * Example Use Case:
 * - Managing price alerts for trading pairs like BTCUSD.
 *
 * @author  Ewerton Daniel
 *
 * @contact contact@ewertondaniel.work
 */
class Alert
{
    /** Alert information (e.g., 'type:pair:price'). */
    public readonly string $info;

    /** Alert type (e.g., 'price'). */
    public readonly string $type;

    /** Type of Bitfinex symbol (e.g., 'trading' or 'funding'). */
    public readonly BitfinexType $bitfinexType;

    /** Symbol referencing the Bitfinex type (e.g., 'tBTCUSD'). */
    public readonly string $symbol;

    /** Pair on which the alert is active (e.g., 'BTCUSD'). */
    public readonly string $pair;

    /** Alert price. */
    public readonly float $price;

    /** Countdown for the alert. */
    public readonly int $countdown;

    /**
     * Initializes an Alert instance with data provided by the Bitfinex API.
     *
     * @param  array  $data  Alert details in the following format:
     *                       - [0] `info` (string): Alert information.
     *                       - [1] `type` (string): Alert type (e.g., 'price').
     *                       - [2] `symbol` (string): Bitfinex symbol (e.g., 'tBTCUSD').
     *                       - [3] `price` (float): Price value triggering the alert.
     *                       - [4] `countdown` (int): Countdown value for the alert.
     */
    public function __construct(array $data)
    {
        $this->info = $data[0];
        $this->type = $data[1];
        $this->bitfinexType = BitfinexType::bySymbol($data[2]);
        $this->symbol = $data[2];
        $this->pair = mb_substr($data[2], 1);
        $this->price = (float) $data[3];
        $this->countdown = (int) $data[4];
    }
}
