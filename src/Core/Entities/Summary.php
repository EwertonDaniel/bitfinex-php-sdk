<?php

namespace EwertonDaniel\Bitfinex\Core\Entities;

class Summary
{
    /** @note Array with info on your current fee rates */
    public readonly FeeInfo $feeInfo;

    /** @note Array with data on your trading volume and fees paid */
    public readonly TradingVolumeAndFee $tradingVolAndFee;

    /** @note Array with data on your funding earnings */
    public readonly FundingEarnings $fundingEarnings;

    /** @note Object with info on your LEO level and holdings */
    public readonly LeoInfo $leoInfo;

    public function __construct(array $data)
    {
        $this->feeInfo = new FeeInfo($data[4]);
        $this->tradingVolAndFee = new TradingVolumeAndFee($data[5]);
        $this->fundingEarnings = new FundingEarnings($data[6]);
        $this->leoInfo = new LeoInfo($data[9]);
    }
}
