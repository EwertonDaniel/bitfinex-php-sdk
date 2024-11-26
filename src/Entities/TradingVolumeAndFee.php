<?php

namespace EwertonDaniel\Bitfinex\Entities;

/**
 * Class TradingVolumeAndFee
 *
 * Represents trading volume and fees information for a Bitfinex account over the past 30 days.
 * This entity encapsulates:
 * - Trading volume per currency and total in USD.
 * - Trading fees paid per currency.
 * - Total trading fees paid in USD equivalent.
 *
 * Key Features:
 * - Provides a structured overview of trading activities and associated costs.
 * - Facilitates tracking and analysis of trading performance and expenses.
 *
 * @author Ewerton Daniel
 * @contact contact@ewertondaniel.work
 */
class TradingVolumeAndFee
{
    /** Objects containing trading volume per currency and Total(USD) over the past 30 days. */
    public readonly array $tradeVolMonth;

    /** Trading fees paid per currency over the past 30 days. */
    public readonly array $feesTradingMonth;

    /** USD equivalent of the total trading fees paid over the past 30 days. */
    public readonly float $feesTradingTotalMonth;

    /**
     * Constructs a TradingVolumeAndFee entity using provided data.
     *
     * @param  array  $data  Array containing:
     *                       - [0]: Trading volume per currency and total in USD.
     *                       - [1]: Trading fees paid per currency.
     *                       - [2]: Total trading fees paid in USD equivalent.
     */
    public function __construct(array $data)
    {
        $this->tradeVolMonth = $data[0];
        $this->feesTradingMonth = $data[1];
        $this->feesTradingTotalMonth = $data[2];
    }
}
