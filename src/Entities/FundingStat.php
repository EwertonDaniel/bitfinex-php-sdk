<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Entities;

use Carbon\Carbon;
use EwertonDaniel\Bitfinex\Helpers\GetThis;

/**
 * Class FundingStat
 *
 * One snapshot of the funding book for a currency.
 *
 * Layout confirmed against the reference and against a live
 * `v2/funding/stats/fUSD/hist` response on 2026-08-03: 12 fields, with
 * placeholders at [1], [2], [5], [6], [9] and [10].
 *
 * @author Ewerton Daniel
 *
 * @contact contact@ewertondaniel.work
 *
 * @link https://docs.bitfinex.com/reference/rest-public-funding-stats
 */
class FundingStat
{
    /** When the snapshot was taken. */
    public readonly ?Carbon $mts;

    /**
     * Flash Return Rate, as the API sends it: **one 365th** of the daily rate.
     *
     * The raw value is tiny (around 9.3e-7) and is almost never what a caller
     * wants to display. Use `dailyRate()` or `annualRate()` instead of scaling
     * it by hand.
     */
    public readonly ?float $frr;

    /** Average period, in days, of the funding currently provided. */
    public readonly ?float $averagePeriod;

    /** Total funding provided in the book. */
    public readonly ?float $fundingAmount;

    /** Portion of the provided funding that is used in positions. */
    public readonly ?float $fundingAmountUsed;

    /** Sum of the open funding offers priced below 0.75%. */
    public readonly ?float $fundingBelowThreshold;

    /**
     * @param  array  $data  Funding stats row:
     *                       - [0]: Timestamp (milliseconds).
     *                       - [1]: PLACEHOLDER.
     *                       - [2]: PLACEHOLDER.
     *                       - [3]: FRR, per 365th of a day.
     *                       - [4]: Average period, in days.
     *                       - [5]: PLACEHOLDER.
     *                       - [6]: PLACEHOLDER.
     *                       - [7]: Total funding provided.
     *                       - [8]: Funding provided that is used in positions.
     *                       - [9]: PLACEHOLDER.
     *                       - [10]: PLACEHOLDER.
     *                       - [11]: Sum of open funding offers below 0.75%.
     */
    public function __construct(array $data)
    {
        $this->mts = GetThis::ifTrueOrFallback(isset($data[0]), fn () => Carbon::createFromTimestampMs((int) $data[0]));
        $this->frr = GetThis::ifTrueOrFallback(is_numeric($data[3] ?? null), fn () => (float) $data[3]);
        $this->averagePeriod = GetThis::ifTrueOrFallback(is_numeric($data[4] ?? null), fn () => (float) $data[4]);
        $this->fundingAmount = GetThis::ifTrueOrFallback(is_numeric($data[7] ?? null), fn () => (float) $data[7]);
        $this->fundingAmountUsed = GetThis::ifTrueOrFallback(is_numeric($data[8] ?? null), fn () => (float) $data[8]);
        $this->fundingBelowThreshold = GetThis::ifTrueOrFallback(is_numeric($data[11] ?? null), fn () => (float) $data[11]);
    }

    /**
     * The Flash Return Rate as a daily rate.
     *
     * The API sends FRR divided by 365, so a raw `9.3e-7` is a daily
     * `3.3945e-4`, which is 0.0339% a day.
     */
    final public function dailyRate(): ?float
    {
        return GetThis::ifTrueOrFallback(! is_null($this->frr), fn () => $this->frr * 365);
    }

    /**
     * The Flash Return Rate compounded out to a year, ignoring compounding.
     *
     * Daily rate times 365. Quoted as a fraction, not a percentage.
     */
    final public function annualRate(): ?float
    {
        return GetThis::ifTrueOrFallback(! is_null($this->frr), fn () => $this->frr * 365 * 365);
    }
}
