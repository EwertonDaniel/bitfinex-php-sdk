<?php

namespace EwertonDaniel\Bitfinex\Entities;

class FeeInfo
{
    /** @note Array with info on your current maker fee rates */
    public readonly MakerFeeInfo $makerFeeInfo;

    /** @note Array with info on your current taker fee rates */
    public readonly TakerFeeInfo $takerFeeInfo;

    public function __construct(array $data)
    {
        $this->makerFeeInfo = new MakerFeeInfo($data[0]);
        $this->takerFeeInfo = new TakerFeeInfo($data[1]);
    }
}
