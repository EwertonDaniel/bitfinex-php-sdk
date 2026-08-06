<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Entities;

use EwertonDaniel\Bitfinex\Helpers\GetThis;

/**
 * Class DerivativeStatusHistory
 *
 * Represents one historical derivative status row.
 *
 * The history endpoint carries the symbol in the path, so its rows have no
 * leading `KEY` field: every index sits one position lower than in the snapshot
 * layout described by `DerivativeStatus`. Reading a history row with the
 * snapshot entity would take `MTS` from `DERIV_PRICE`, and so on down the row.
 *
 * Documented layout (23 elements):
 * [0] MTS (int)
 * [2] DERIV_PRICE (float)
 * [3] SPOT_PRICE (float)
 * [5] INSURANCE_FUND_BALANCE (float)
 * [7] NEXT_FUNDING_EVT_MTS (int)
 * [8] NEXT_FUNDING_ACCRUED (float)
 * [9] NEXT_FUNDING_STEP (int)
 * [11] CURRENT_FUNDING (float)
 * [14] MARK_PRICE (float)
 * [17] OPEN_INTEREST (float)
 * [21] CLAMP_MIN (float)
 * [22] CLAMP_MAX (float)
 *
 * @author  Ewerton Daniel
 *
 * @contact contact@ewertondaniel.work
 *
 * @link https://docs.bitfinex.com/reference/rest-public-derivatives-status-history
 */
class DerivativeStatusHistory
{
    public readonly ?int $mts;

    public readonly ?float $derivPrice;

    public readonly ?float $spotPrice;

    public readonly ?float $insuranceFundBalance;

    public readonly ?int $nextFundingEvtMts;

    public readonly ?float $nextFundingAccrued;

    public readonly ?int $nextFundingStep;

    public readonly ?float $currentFunding;

    public readonly ?float $markPrice;

    public readonly ?float $openInterest;

    public readonly ?float $clampMin;

    public readonly ?float $clampMax;

    public function __construct(array $data)
    {
        $this->mts = GetThis::ifTrueOrFallback(isset($data[0]), fn () => (int) $data[0]);
        $this->derivPrice = GetThis::ifTrueOrFallback(isset($data[2]), fn () => (float) $data[2]);
        $this->spotPrice = GetThis::ifTrueOrFallback(isset($data[3]), fn () => (float) $data[3]);
        $this->insuranceFundBalance = GetThis::ifTrueOrFallback(isset($data[5]), fn () => (float) $data[5]);
        $this->nextFundingEvtMts = GetThis::ifTrueOrFallback(isset($data[7]), fn () => (int) $data[7]);
        $this->nextFundingAccrued = GetThis::ifTrueOrFallback(isset($data[8]), fn () => (float) $data[8]);
        $this->nextFundingStep = GetThis::ifTrueOrFallback(isset($data[9]), fn () => (int) $data[9]);
        $this->currentFunding = GetThis::ifTrueOrFallback(isset($data[11]), fn () => (float) $data[11]);
        $this->markPrice = GetThis::ifTrueOrFallback(isset($data[14]), fn () => (float) $data[14]);
        $this->openInterest = GetThis::ifTrueOrFallback(isset($data[17]), fn () => (float) $data[17]);
        $this->clampMin = GetThis::ifTrueOrFallback(isset($data[21]), fn () => (float) $data[21]);
        $this->clampMax = GetThis::ifTrueOrFallback(isset($data[22]), fn () => (float) $data[22]);
    }
}
