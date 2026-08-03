<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Entities;

use Carbon\Carbon;
use EwertonDaniel\Bitfinex\Helpers\GetThis;

/**
 * Class FundingLoan
 *
 * Funds taken from the funding book.
 *
 * The layout is byte-for-byte the one in {@see FundingCredit} over [0]–[20]; a
 * credit adds [21] POSITION_PAIR and a loan has no such field. That is the only
 * structural difference, and `tests/Unit/Entities/FundingLayoutTest.php` fails if
 * the two mappings ever drift apart.
 *
 * The mapping is kept flat rather than shared through a base class so the whole
 * positional contract stays readable in one file.
 *
 * Layout taken from the reference on 2026-08-03: 21 fields, with placeholders at
 * [9], [10], [17] and [19]. There was no live sample to check it against, since
 * the account used for verification has no funding history.
 *
 * @author Ewerton Daniel
 *
 * @contact contact@ewertondaniel.work
 *
 * @link https://docs.bitfinex.com/reference/rest-auth-funding-loans
 */
class FundingLoan
{
    /** Loan identifier. */
    public readonly int $id;

    /** Funding symbol, prefixed (e.g. fUSD). */
    public readonly string $symbol;

    /** Currency without the funding prefix (e.g. USD). */
    public readonly string $currency;

    /** 1 for lender, -1 for borrower, 0 when both. */
    public readonly ?int $side;

    /** When the loan was opened. */
    public readonly ?Carbon $createdAt;

    /** When the loan last changed. */
    public readonly ?Carbon $updatedAt;

    /** Amount of funds. */
    public readonly float $amount;

    /** Sum of the active flags. Non-numeric values are dropped rather than coerced. */
    public readonly ?int $flags;

    /** Loan status, e.g. ACTIVE. */
    public readonly ?string $status;

    /** Rate type: FIXED, or VAR for the Flash Return Rate. */
    public readonly ?string $rateType;

    /** Rate per period. */
    public readonly ?float $rate;

    /** Period in days. */
    public readonly ?int $period;

    /** When the loan started earning. */
    public readonly ?Carbon $openedAt;

    /** When the last payout happened. */
    public readonly ?Carbon $lastPayoutAt;

    /** Whether operations on the loan trigger a notification. */
    public readonly bool $notify;

    /** Whether the loan is hidden. */
    public readonly bool $hidden;

    /** Whether the loan renews itself once it closes. */
    public readonly bool $renew;

    /** Whether the funds are kept out of an automatic close. */
    public readonly bool $noClose;

    /**
     * @param  array  $data  Funding loan row:
     *                       - [0]: Loan ID.
     *                       - [1]: Symbol.
     *                       - [2]: Side.
     *                       - [3]: Creation timestamp (milliseconds).
     *                       - [4]: Last update timestamp (milliseconds).
     *                       - [5]: Amount.
     *                       - [6]: Flags.
     *                       - [7]: Status.
     *                       - [8]: Rate type.
     *                       - [9]: PLACEHOLDER.
     *                       - [10]: PLACEHOLDER.
     *                       - [11]: Rate.
     *                       - [12]: Period, in days.
     *                       - [13]: Opening timestamp (milliseconds).
     *                       - [14]: Last payout timestamp (milliseconds).
     *                       - [15]: Notify flag.
     *                       - [16]: Hidden flag.
     *                       - [17]: PLACEHOLDER.
     *                       - [18]: Renew flag.
     *                       - [19]: PLACEHOLDER.
     *                       - [20]: No close flag.
     */
    public function __construct(array $data)
    {
        $this->id = (int) ($data[0] ?? 0);
        $this->symbol = (string) ($data[1] ?? '');
        $this->currency = ltrim($this->symbol, 'f');
        $this->side = GetThis::ifTrueOrFallback(is_numeric($data[2] ?? null), fn () => (int) $data[2]);
        $this->createdAt = GetThis::ifTrueOrFallback(isset($data[3]), fn () => Carbon::createFromTimestampMs((int) $data[3]));
        $this->updatedAt = GetThis::ifTrueOrFallback(isset($data[4]), fn () => Carbon::createFromTimestampMs((int) $data[4]));
        $this->amount = (float) ($data[5] ?? 0);
        $this->flags = GetThis::ifTrueOrFallback(is_numeric($data[6] ?? null), fn () => (int) $data[6]);
        $this->status = GetThis::ifTrueOrFallback(isset($data[7]), fn () => (string) $data[7]);
        $this->rateType = GetThis::ifTrueOrFallback(isset($data[8]), fn () => (string) $data[8]);
        $this->rate = GetThis::ifTrueOrFallback(is_numeric($data[11] ?? null), fn () => (float) $data[11]);
        $this->period = GetThis::ifTrueOrFallback(is_numeric($data[12] ?? null), fn () => (int) $data[12]);
        $this->openedAt = GetThis::ifTrueOrFallback(isset($data[13]), fn () => Carbon::createFromTimestampMs((int) $data[13]));
        $this->lastPayoutAt = GetThis::ifTrueOrFallback(isset($data[14]), fn () => Carbon::createFromTimestampMs((int) $data[14]));
        $this->notify = (bool) ($data[15] ?? false);
        $this->hidden = (bool) ($data[16] ?? false);
        $this->renew = (bool) ($data[18] ?? false);
        $this->noClose = (bool) ($data[20] ?? false);
    }
}
