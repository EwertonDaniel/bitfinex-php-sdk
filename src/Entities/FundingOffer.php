<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Entities;

use Carbon\Carbon;
use EwertonDaniel\Bitfinex\Helpers\GetThis;

/**
 * Class FundingOffer
 *
 * An offer to lend funds on the funding book, active or historical.
 *
 * Layout taken from the reference on 2026-08-03: 21 fields, with placeholders at
 * [7], [8], [11], [12], [13], [18] and [20]. There was no live sample to check it
 * against, since the account used for verification has no funding history.
 *
 * @author Ewerton Daniel
 *
 * @contact contact@ewertondaniel.work
 *
 * @link https://docs.bitfinex.com/reference/rest-auth-funding-offers
 */
class FundingOffer
{
    /** Offer identifier. */
    public readonly int $id;

    /** Funding symbol, prefixed (e.g. fUSD). */
    public readonly string $symbol;

    /** Currency without the funding prefix (e.g. USD). */
    public readonly string $currency;

    /** When the offer was created. */
    public readonly ?Carbon $createdAt;

    /** When the offer last changed. */
    public readonly ?Carbon $updatedAt;

    /** Amount still on offer. */
    public readonly float $amount;

    /** Amount the offer was opened with. */
    public readonly float $amountOrig;

    /** Offer type, e.g. LIMIT or FRRDELTAVAR. */
    public readonly ?string $type;

    /**
     * Sum of the active flags.
     *
     * The reference calls this an object; both the documented example and live
     * responses carry an integer or null, so anything non-numeric is dropped
     * rather than coerced into a misleading 0.
     */
    public readonly ?int $flags;

    /** Offer status, e.g. ACTIVE, EXECUTED, PARTIALLY FILLED, CANCELED. */
    public readonly ?string $status;

    /** Rate per period. For a FRR offer this is the delta, not the absolute rate. */
    public readonly ?float $rate;

    /** Period in days the funds are offered for. */
    public readonly ?int $period;

    /** Whether operations on the offer trigger a notification. */
    public readonly bool $notify;

    /** Whether the offer is hidden from the book. */
    public readonly bool $hidden;

    /** Whether the offer renews itself once it closes. */
    public readonly bool $renew;

    /**
     * @param  array  $data  Funding offer row:
     *                       - [0]: Offer ID.
     *                       - [1]: Symbol.
     *                       - [2]: Creation timestamp (milliseconds).
     *                       - [3]: Last update timestamp (milliseconds).
     *                       - [4]: Amount.
     *                       - [5]: Original amount.
     *                       - [6]: Type.
     *                       - [7]: PLACEHOLDER.
     *                       - [8]: PLACEHOLDER.
     *                       - [9]: Flags.
     *                       - [10]: Status.
     *                       - [11]: PLACEHOLDER.
     *                       - [12]: PLACEHOLDER.
     *                       - [13]: PLACEHOLDER.
     *                       - [14]: Rate.
     *                       - [15]: Period, in days.
     *                       - [16]: Notify flag.
     *                       - [17]: Hidden flag.
     *                       - [18]: PLACEHOLDER.
     *                       - [19]: Renew flag.
     *                       - [20]: PLACEHOLDER.
     */
    public function __construct(array $data)
    {
        $this->id = (int) ($data[0] ?? 0);
        $this->symbol = (string) ($data[1] ?? '');
        $this->currency = ltrim($this->symbol, 'f');
        $this->createdAt = GetThis::ifTrueOrFallback(isset($data[2]), fn () => Carbon::createFromTimestampMs((int) $data[2]));
        $this->updatedAt = GetThis::ifTrueOrFallback(isset($data[3]), fn () => Carbon::createFromTimestampMs((int) $data[3]));
        $this->amount = (float) ($data[4] ?? 0);
        $this->amountOrig = (float) ($data[5] ?? 0);
        $this->type = GetThis::ifTrueOrFallback(isset($data[6]), fn () => (string) $data[6]);
        $this->flags = GetThis::ifTrueOrFallback(is_numeric($data[9] ?? null), fn () => (int) $data[9]);
        $this->status = GetThis::ifTrueOrFallback(isset($data[10]), fn () => (string) $data[10]);
        $this->rate = GetThis::ifTrueOrFallback(is_numeric($data[14] ?? null), fn () => (float) $data[14]);
        $this->period = GetThis::ifTrueOrFallback(is_numeric($data[15] ?? null), fn () => (int) $data[15]);
        $this->notify = (bool) ($data[16] ?? false);
        $this->hidden = (bool) ($data[17] ?? false);
        $this->renew = (bool) ($data[19] ?? false);
    }
}
