<?php

namespace EwertonDaniel\Bitfinex\Entities;

class LeoInfo
{
    /** @note Current LEO level */
    public readonly float $leoLevel;

    /** @note Average LEO amount held in the past 30 days */
    public readonly float $leoAmountAvg;

    public function __construct(array $data)
    {
        $this->leoLevel = $data['leo_lev'];
        $this->leoAmountAvg = $data['leo_amount_avg'];
    }
}
