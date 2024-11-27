<?php

namespace EwertonDaniel\Bitfinex\Entities;

/**
 * Class Summary
 *
 * Represents a comprehensive summary of a user's account on the Bitfinex platform.
 * This entity encapsulates data related to:
 * - Current fee rates.
 * - Trading volume and fees paid.
 * - Funding earnings.
 * - LEO level and holdings.
 *
 * Key Features:
 * - Combines multiple entities (`FeeInfo`, `TradingVolumeAndFee`, `FundingEarnings`, and `LeoInfo`) for a full account overview.
 * - Simplifies access to detailed account statistics and metrics.
 *
 * @author Ewerton Daniel
 *
 * @contact contact@ewertondaniel.work
 */
class Summary
{
    /** Current fee rates. */
    public readonly FeeInfo $feeInfo;

    /** Trading volume and fees paid. */
    public readonly TradingVolumeAndFee $tradingVolAndFee;

    /** Funding earnings data. */
    public readonly FundingEarnings $fundingEarnings;

    /** LEO level and holdings information. */
    public readonly LeoInfo $leoInfo;

    /**
     * Constructs a Summary entity using data from the Bitfinex API.
     *
     * @param  array  $data  Array containing summary details:
     *                       - [4]: Fee information.
     *                       - [5]: Trading volume and fees paid.
     *                       - [6]: Funding earnings.
     *                       - [9]: LEO level and holdings information.
     */
    public function __construct(array $data)
    {
        $this->feeInfo = new FeeInfo($data[4]);
        $this->tradingVolAndFee = new TradingVolumeAndFee($data[5]);
        $this->fundingEarnings = new FundingEarnings($data[6]);
        $this->leoInfo = new LeoInfo($data[9]);
    }
}
