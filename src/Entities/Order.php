<?php

namespace EwertonDaniel\Bitfinex\Entities;

use EwertonDaniel\Bitfinex\Helpers\GetThis;
use Illuminate\Support\Carbon;

/**
 * Class Order
 *
 * Represents an order on the Bitfinex platform. This entity provides structured
 * details about the order, including its identifiers, trading pair, timestamps,
 * amounts, prices, and metadata.
 *
 * Key Features:
 * - Distinguishes between buy and sell orders using the `amount` field.
 * - Includes auxiliary fields like flags, notification settings, and order routing information.
 * - Parses timestamps and additional meta information for better usability.
 *
 * @author Ewerton Daniel
 * @contact contact@ewertondaniel.work
 */
class Order
{
    /** Order ID. */
    public readonly int $id;

    /** Group Order ID. */
    public readonly ?int $gid;

    /** Client Order ID. */
    public readonly int $cid;

    /** Trading pair (e.g., tBTCUSD, tLTCBTC). */
    public readonly string $symbol;

    /** Millisecond epoch timestamp of creation. */
    public readonly Carbon $createdAt;

    /** Millisecond epoch timestamp of last update. */
    public readonly Carbon $updatedAt;

    /** Positive means buy, negative means sell. */
    public readonly float $amount;

    /** Original amount (before any updates). */
    public readonly float $amountOrig;

    /** The order's type. */
    public readonly string $orderType;

    /** Previous order type (before the last update). */
    public readonly ?string $typePrev;

    /** Datetime for TIF (Time-In-Force). */
    public readonly ?Carbon $tifDatetime;

    /** Sum of all active flags for the order. */
    public readonly ?int $flags;

    /** Order status. */
    public readonly string $status;

    /** Price. */
    public readonly float $price;

    /** Average price. */
    public readonly float $priceAvg;

    /** The trailing price. */
    public readonly ?float $priceTrailing;

    /** Auxiliary Limit price (for STOP LIMIT orders). */
    public readonly ?float $priceAuxLimit;

    /** 1 if operations on order must trigger a notification, 0 otherwise. */
    public readonly bool $notify;

    /** 1 if the order must be hidden, 0 otherwise. */
    public readonly bool $hidden;

    /** If another order caused this order to be placed (OCO), this will be that other order's ID. */
    public readonly ?int $placedId;

    /** Indicates origin of action (e.g., BFX, API>BFX). */
    public readonly ?string $routing;

    /** Additional meta information about the order. */
    public readonly ?array $meta;

    /** Pair (e.g., BTCUSD, LTCBTC). */
    public string $pair;

    /**
     * Constructs an Order entity using data retrieved from the Bitfinex API.
     *
     * @param array $data Array containing order details:
     *                       - [0]: Order ID.
     *                       - [1]: Group Order ID (optional).
     *                       - [2]: Client Order ID.
     *                       - [3]: Trading pair symbol.
     *                       - [4]: Creation timestamp (milliseconds).
     *                       - [5]: Last update timestamp (milliseconds).
     *                       - [6]: Amount.
     *                       - [7]: Original amount.
     *                       - [8]: Order type.
     *                       - [9]: Previous order type (optional).
     *                       - [10]: TIF timestamp (milliseconds, optional).
     *                       - [12]: Flags (optional).
     *                       - [13]: Status.
     *                       - [16]: Price.
     *                       - [17]: Average price.
     *                       - [18]: Trailing price (optional).
     *                       - [19]: Auxiliary limit price (optional).
     *                       - [23]: Notify flag (1 or 0).
     *                       - [24]: Hidden flag (1 or 0).
     *                       - [25]: Placed Order ID (optional).
     *                       - [28]: Routing information (optional).
     *                       - [31]: Metadata (optional, array).
     */
    public function __construct(array $data)
    {
        $this->id = GetThis::ifTrueOrFallback(isset($data[0]), fn() => (int)$data[0]);
        $this->gid = GetThis::ifTrueOrFallback(isset($data[1]), fn() => (int)$data[1]);
        $this->cid = GetThis::ifTrueOrFallback(isset($data[2]), fn() => (int)$data[2]);
        $this->symbol = GetThis::ifTrueOrFallback(isset($data[3]), fn() => $data[3]);
        $this->pair = GetThis::ifTrueOrFallback(isset($data[3]), fn() => substr($data[3], 1));
        $this->createdAt = GetThis::ifTrueOrFallback(isset($data[4]), fn() => Carbon::createFromTimestampMs($data[4]));
        $this->updatedAt = GetThis::ifTrueOrFallback(isset($data[5]), fn() => Carbon::createFromTimestampMs($data[5]));
        $this->amount = GetThis::ifTrueOrFallback(isset($data[6]), fn() => $data[6], 0.0);
        $this->amountOrig = GetThis::ifTrueOrFallback(isset($data[7]), fn() => $data[7], 0.0);
        $this->orderType = GetThis::ifTrueOrFallback(isset($data[8]), fn() => $data[8]);
        $this->typePrev = GetThis::ifTrueOrFallback(isset($data[9]), fn() => $data[9]);
        $this->tifDatetime = GetThis::ifTrueOrFallback(isset($data[10]), fn() => Carbon::createFromTimestampMs($data[10]), null);
        $this->flags = GetThis::ifTrueOrFallback(isset($data[12]), fn() => $data[12]);
        $this->status = GetThis::ifTrueOrFallback(isset($data[13]), fn() => $data[13]);
        $this->price = GetThis::ifTrueOrFallback(isset($data[16]), fn() => $data[16], 0.0);
        $this->priceAvg = GetThis::ifTrueOrFallback(isset($data[17]), fn() => $data[17], 0.0);
        $this->priceTrailing = GetThis::ifTrueOrFallback(isset($data[18]), fn() => $data[18]);
        $this->priceAuxLimit = GetThis::ifTrueOrFallback(isset($data[19]), fn() => $data[19]);
        $this->notify = GetThis::ifTrueOrFallback(isset($data[23]), fn() => $data[23] === 1, false);
        $this->hidden = GetThis::ifTrueOrFallback(isset($data[24]), fn() => $data[24] === 1, false);
        $this->placedId = GetThis::ifTrueOrFallback(isset($data[25]), fn() => $data[25]);
        $this->routing = GetThis::ifTrueOrFallback(isset($data[28]), fn() => $data[28]);
        $this->meta = GetThis::ifTrueOrFallback(isset($data[31]) && is_array($data[31]), fn() => $data[31], []);
    }
}
