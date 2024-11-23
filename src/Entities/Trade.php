<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Entities;

use Carbon\Carbon;
use EwertonDaniel\Bitfinex\Enums\BitfinexAction;
use EwertonDaniel\Bitfinex\Helpers\GetThis;

class Trade
{
    /** @note Trade database id */
    public readonly int $id;

    /** @note Symbol (e.g., BTCUSD) */
    public readonly string $symbol;

    /** @note Execution timestamp */
    public readonly Carbon $mts;

    /** @note Order id */
    public readonly int $orderId;

    /** @note Positive means buy, negative means sell */
    public readonly float $execAmount;

    public BitfinexAction $tradeType;

    /** @note Execution price */
    public readonly float $execPrice;

    /** @note Order type (null for trades older than March 2020) */
    public readonly ?string $orderType;

    /** @note Order price */
    public readonly float $orderPrice;

    /** @note maker */
    public readonly bool $maker;

    /** @note Fee */
    public readonly float $fee;

    /** @note Fee currency */
    public readonly string $feeCurrency;

    /** @note Client Order ID */
    public readonly int $cid;

    public function __construct(array $data)
    {
        $this->id = (int) $data[0];
        $this->symbol = $data[1];
        $this->mts = Carbon::createFromTimestampMs($data[2]);
        $this->orderId = (int) $data[3];
        $this->execAmount = (float) $data[4];
        $this->tradeType = BitfinexAction::fromValue($this->execAmount);
        $this->execPrice = (float) $data[5];
        $this->orderPrice = (float) $data[7];
        $this->maker = $data[8] > 0;
        $this->fee = (float) $data[9];
        $this->feeCurrency = $data[10];
        $this->cid = (int) $data[11];
        $this->orderType = GetThis::ifTrueOrFallback(isset($data[6]), fn () => $data[6]);
    }
}
