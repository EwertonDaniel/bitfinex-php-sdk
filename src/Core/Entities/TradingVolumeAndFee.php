<?php

namespace EwertonDaniel\Bitfinex\Core\Entities;

class TradingVolumeAndFee
{
    /** @note Shows objects containing trading volume per currency and Total(USD) over the past 30 days */
    public readonly array $tradeVolMonth;

    /** @note Shows trading fees paid per currency over the past 30 days */
    public readonly array $feesTradingMonth;

    /** @note Shows the USD equivalent of the total trading fees paid over the past 30 days */
    public readonly float $feesTradingTotalMonth;

    public function __construct(array $data)
    {
        $this->tradeVolMonth = $data[0];
        $this->feesTradingMonth = $data[1];
        $this->feesTradingTotalMonth = $data[2];
    }
}
