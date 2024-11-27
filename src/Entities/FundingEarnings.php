<?php

namespace EwertonDaniel\Bitfinex\Entities;

/**
 * Class FundingEarnings
 *
 * Represents the funding earnings for an account on the Bitfinex platform over the past 30 days.
 * Provides structured data for:
 * - Earnings per currency.
 * - The total USD equivalent of all funding earnings.
 *
 * @author Ewerton Daniel
 *
 * @contact contact@ewertondaniel.work
 */
class FundingEarnings
{
    /** Funding earnings per currency. */
    public readonly array $fundingEarningsPerCurrency;

    /** Total funding earnings in USD equivalent. */
    public readonly float $fundingEarningsTotal;

    /**
     * Constructs a FundingEarnings entity using provided data.
     *
     * @param  array  $data  Array containing:
     *                       - [1]: Funding earnings per currency.
     *                       - [2]: Total funding earnings in USD.
     */
    public function __construct(array $data)
    {
        $this->fundingEarningsPerCurrency = $data[1];
        $this->fundingEarningsTotal = $data[2];
    }
}
