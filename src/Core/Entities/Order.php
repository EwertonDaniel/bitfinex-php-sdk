<?php

namespace EwertonDaniel\Bitfinex\Core\Entities;

use EwertonDaniel\Bitfinex\Helpers\GetThis;
use Illuminate\Support\Carbon;

class Order
{
    /** @note Order ID */
    public readonly int $id;

    /** @note Group Order ID */
    public readonly ?int $gid;

    /** @note Client Order ID */
    public readonly int $cid;

    /** @note Trading pair (e.g., tBTCUSD, tLTCBTC) */
    public readonly string $symbol;

    /** @note Millisecond epoch timestamp of creation */
    public readonly Carbon $mtsCreate;

    /** @note Millisecond epoch timestamp of last update */
    public readonly Carbon $mtsUpdate;

    /** @note Positive means buy, negative means sell */
    public readonly float $amount;

    /** @note Original amount (before any update) */
    public readonly float $amountOrig;

    /** @note The order's type */
    public readonly string $orderType;

    /** @note Previous order type (before the last update) */
    public readonly ?string $typePrev;

    /** @note Millisecond epoch timestamp for TIF (Time-In-Force) */
    public readonly ?Carbon $mtsTif;

    /** @note Sum of all active flags for the order */
    public readonly ?int $flags;

    /** @note Order status */
    public readonly string $status;

    /** @note Price */
    public readonly float $price;

    /** @note Average price */
    public readonly float $priceAvg;

    /** @note The trailing price */
    public readonly ?float $priceTrailing;

    /** @note Auxiliary Limit price (for STOP LIMIT orders) */
    public readonly ?float $priceAuxLimit;

    /** @note 1 if operations on order must trigger a notification, 0 otherwise */
    public readonly bool $notify;

    /** @note 1 if order must be hidden, 0 otherwise */
    public readonly bool $hidden;

    /** @note If another order caused this order to be placed (OCO), this will be that other order's ID */
    public readonly ?int $placedId;

    /** @note Indicates origin of action (e.g., BFX, API>BFX) */
    public readonly ?string $routing;

    /** @note Additional meta information about the order */
    public readonly ?array $meta;

    public function __construct(array $data)
    {
        $this->id = GetThis::ifTrueOrFallback(isset($data[0]), fn () => $data[0]);
        $this->gid = GetThis::ifTrueOrFallback(isset($data[1]), fn () => $data[1]);
        $this->cid = GetThis::ifTrueOrFallback(isset($data[2]), fn () => $data[2]);
        $this->symbol = GetThis::ifTrueOrFallback(isset($data[3]), fn () => $data[3]);
        $this->mtsCreate = GetThis::ifTrueOrFallback(isset($data[4]), fn () => Carbon::createFromTimestampMs($data[4]));
        $this->mtsUpdate = GetThis::ifTrueOrFallback(isset($data[5]), fn () => Carbon::createFromTimestampMs($data[5]));
        $this->amount = GetThis::ifTrueOrFallback(isset($data[6]), fn () => $data[6], 0.0);
        $this->amountOrig = GetThis::ifTrueOrFallback(isset($data[7]), fn () => $data[7], 0.0);
        $this->orderType = GetThis::ifTrueOrFallback(isset($data[8]), fn () => $data[8]);
        $this->typePrev = GetThis::ifTrueOrFallback(isset($data[9]), fn () => $data[9]);
        $this->mtsTif = GetThis::ifTrueOrFallback(isset($data[10]), fn () => Carbon::createFromTimestampMs($data[10]), null);
        $this->flags = GetThis::ifTrueOrFallback(isset($data[12]), fn () => $data[12]);
        $this->status = GetThis::ifTrueOrFallback(isset($data[13]), fn () => $data[13]);
        $this->price = GetThis::ifTrueOrFallback(isset($data[16]), fn () => $data[16], 0.0);
        $this->priceAvg = GetThis::ifTrueOrFallback(isset($data[17]), fn () => $data[17], 0.0);
        $this->priceTrailing = GetThis::ifTrueOrFallback(isset($data[18]), fn () => $data[18]);
        $this->priceAuxLimit = GetThis::ifTrueOrFallback(isset($data[19]), fn () => $data[19]);
        $this->notify = GetThis::ifTrueOrFallback(isset($data[23]), fn () => $data[23] === 1, false);
        $this->hidden = GetThis::ifTrueOrFallback(isset($data[24]), fn () => $data[24] === 1, false);
        $this->placedId = GetThis::ifTrueOrFallback(isset($data[25]), fn () => $data[25]);
        $this->routing = GetThis::ifTrueOrFallback(isset($data[28]), fn () => $data[28]);
        $this->meta = GetThis::ifTrueOrFallback(isset($data[31]) && is_array($data[31]), fn () => $data[31], []);
    }
}
