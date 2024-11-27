<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Entities;

use Carbon\Carbon;
use EwertonDaniel\Bitfinex\Enums\BitfinexAction;
use EwertonDaniel\Bitfinex\Helpers\GetThis;

/**
 * Class Trade
 *
 * Represents a trade on the Bitfinex platform, encapsulating details about executed orders,
 * including timestamps, prices, amounts, fees, and trade types.
 *
 * Key Features:
 * - Tracks both execution and order-level details.
 * - Derives the trade type (buy or sell) based on the execution amount.
 * - Handles historical and current trade data with optional order types.
 *
 * @author Ewerton Daniel
 *
 * @contact contact@ewertondaniel.work
 */
class Trade
{
    /** Trade database ID. */
    public readonly int $id;

    /** Symbol (e.g., BTCUSD). */
    public readonly string $symbol;

    /** Execution timestamp. */
    public readonly Carbon $mts;

    /** Order ID. */
    public readonly int $orderId;

    /** Positive means buy, negative means sell. */
    public readonly float $execAmount;

    /** Trade type (buy or sell). */
    public readonly BitfinexAction $tradeType;

    /** Execution price. */
    public readonly float $execPrice;

    /** Order type (null for trades older than March 2020). */
    public readonly ?string $orderType;

    /** Order price. */
    public readonly float $orderPrice;

    /** Indicates whether the trade was made as a maker. */
    public readonly bool $maker;

    /** Trade fee. */
    public readonly float $fee;

    /** Fee currency. */
    public readonly string $feeCurrency;

    /** Client Order ID. */
    public readonly int $cid;

    /**
     * Constructs a Trade entity using data from the Bitfinex API.
     *
     * @param  array  $data  Array containing trade details:
     *                       - [0]: Trade database ID (int).
     *                       - [1]: Symbol (string).
     *                       - [2]: Execution timestamp in milliseconds (int).
     *                       - [3]: Order ID (int).
     *                       - [4]: Execution amount (float, positive for buy, negative for sell).
     *                       - [5]: Execution price (float).
     *                       - [6]: Order type (string, optional for trades older than March 2020).
     *                       - [7]: Order price (float).
     *                       - [8]: Maker flag (int, > 0 if maker, else taker).
     *                       - [9]: Fee (float).
     *                       - [10]: Fee currency (string).
     *                       - [11]: Client Order ID (int).
     */
    public function __construct(array $data)
    {
        $this->id = (int) $data[0];
        $this->symbol = $data[1];
        $this->mts = Carbon::createFromTimestampMs($data[2]);
        $this->orderId = (int) $data[3];
        $this->execAmount = (float) $data[4];
        $this->tradeType = BitfinexAction::fromAmount($this->execAmount);
        $this->execPrice = (float) $data[5];
        $this->orderPrice = (float) $data[7];
        $this->maker = $data[8] > 0;
        $this->fee = (float) $data[9];
        $this->feeCurrency = $data[10];
        $this->cid = (int) $data[11];
        $this->orderType = GetThis::ifTrueOrFallback(isset($data[6]), fn () => $data[6]);
    }
}
