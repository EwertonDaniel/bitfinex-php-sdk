<?php

namespace EwertonDaniel\Bitfinex\Entities;

class FundingEarnings
{
    /** @note Shows the amount earned on your provided funding per currency over the past 30 days */
    public readonly array $fundingEarningsPerCurrency;

    /** @note Shows the USD equivalent of the total earnings on your provided funding over the past 30 days */
    public readonly float $fundingEarningsTotal;

    public function __construct(array $data)
    {
        $this->fundingEarningsPerCurrency = $data[1];
        $this->fundingEarningsTotal = $data[2];
    }
}
