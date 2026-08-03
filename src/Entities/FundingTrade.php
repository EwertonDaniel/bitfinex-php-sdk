<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Entities;

use Carbon\Carbon;
use EwertonDaniel\Bitfinex\Helpers\GetThis;

/**
 * Class FundingTrade
 *
 * One execution against a funding offer.
 *
 * Layout taken from the reference on 2026-08-03: 8 fields, with a placeholder at
 * [7]. There was no live sample to check it against, since the account used for
 * verification has no funding history.
 *
 * @author Ewerton Daniel
 *
 * @contact contact@ewertondaniel.work
 *
 * @link https://docs.bitfinex.com/reference/rest-auth-funding-trades-hist
 */
class FundingTrade
{
    /** Trade identifier. */
    public readonly int $id;

    /** Funding symbol, prefixed (e.g. fUSD). */
    public readonly string $symbol;

    /** Currency without the funding prefix (e.g. USD). */
    public readonly string $currency;

    /** When the trade executed. */
    public readonly ?Carbon $createdAt;

    /** Offer this execution came from. */
    public readonly ?int $offerId;

    /** Signed amount: negative means funds were lent out. */
    public readonly float $amount;

    /** Rate per period the trade executed at. */
    public readonly ?float $rate;

    /** Period in days. */
    public readonly ?int $period;

    /**
     * @param  array  $data  Funding trade row:
     *                       - [0]: Trade ID.
     *                       - [1]: Currency symbol.
     *                       - [2]: Creation timestamp (milliseconds).
     *                       - [3]: Offer ID.
     *                       - [4]: Amount.
     *                       - [5]: Rate.
     *                       - [6]: Period, in days.
     *                       - [7]: PLACEHOLDER.
     */
    public function __construct(array $data)
    {
        $this->id = (int) ($data[0] ?? 0);
        $this->symbol = (string) ($data[1] ?? '');
        $this->currency = ltrim($this->symbol, 'f');
        $this->createdAt = GetThis::ifTrueOrFallback(isset($data[2]), fn () => Carbon::createFromTimestampMs((int) $data[2]));
        $this->offerId = GetThis::ifTrueOrFallback(is_numeric($data[3] ?? null), fn () => (int) $data[3]);
        $this->amount = (float) ($data[4] ?? 0);
        $this->rate = GetThis::ifTrueOrFallback(is_numeric($data[5] ?? null), fn () => (float) $data[5]);
        $this->period = GetThis::ifTrueOrFallback(is_numeric($data[6] ?? null), fn () => (int) $data[6]);
    }
}
