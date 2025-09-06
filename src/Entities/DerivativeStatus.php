<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Entities;

use EwertonDaniel\Bitfinex\Helpers\GetThis;

/**
 * Class DerivativeStatus
 *
 * Maps selected fields from the derivatives status endpoint.
 * Only known indexes are extracted; missing ones remain null.
 *
 * Index map:
 * [0] KEY (string)
 * [1] MTS (int)
 * [3] DERIV_PRICE (float)
 * [4] SPOT_PRICE (float)
 * [6] INSURANCE_FUND_BALANCE (float)
 * [8] NEXT_FUNDING_EVT_MTS (int)
 * [9] NEXT_FUNDING_ACCRUED (float)
 * [10] NEXT_FUNDING_STEP (int)
 * [12] CURRENT_FUNDING (float)
 * [15] MARK_PRICE (float)
 * [18] OPEN_INTEREST (float)
 * [22] CLAMP_MIN (float)
 * [23] CLAMP_MAX (float)
 */
class DerivativeStatus
{
    public readonly ?string $key;
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
        $this->key = GetThis::ifTrueOrFallback(isset($data[0]), fn () => (string) $data[0]);
        $this->mts = GetThis::ifTrueOrFallback(isset($data[1]), fn () => (int) $data[1]);
        $this->derivPrice = GetThis::ifTrueOrFallback(isset($data[3]), fn () => (float) $data[3]);
        $this->spotPrice = GetThis::ifTrueOrFallback(isset($data[4]), fn () => (float) $data[4]);
        $this->insuranceFundBalance = GetThis::ifTrueOrFallback(isset($data[6]), fn () => (float) $data[6]);
        $this->nextFundingEvtMts = GetThis::ifTrueOrFallback(isset($data[8]), fn () => (int) $data[8]);
        $this->nextFundingAccrued = GetThis::ifTrueOrFallback(isset($data[9]), fn () => (float) $data[9]);
        $this->nextFundingStep = GetThis::ifTrueOrFallback(isset($data[10]), fn () => (int) $data[10]);
        $this->currentFunding = GetThis::ifTrueOrFallback(isset($data[12]), fn () => (float) $data[12]);
        $this->markPrice = GetThis::ifTrueOrFallback(isset($data[15]), fn () => (float) $data[15]);
        $this->openInterest = GetThis::ifTrueOrFallback(isset($data[18]), fn () => (float) $data[18]);
        $this->clampMin = GetThis::ifTrueOrFallback(isset($data[22]), fn () => (float) $data[22]);
        $this->clampMax = GetThis::ifTrueOrFallback(isset($data[23]), fn () => (float) $data[23]);
    }
}

