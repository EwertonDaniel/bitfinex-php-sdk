<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Entities;

use Carbon\Carbon;
use EwertonDaniel\Bitfinex\Helpers\GetThis;

/**
 * Class LedgerEntry
 *
 * A single movement in the account ledger: the amount that changed, the balance
 * that resulted, and the free-form description the exchange wrote for it.
 *
 * Layout confirmed against the reference and against a live
 * `v2/auth/r/ledgers/USD/hist` response on 2026-08-03: 9 fields, with
 * placeholders at [4] and [7].
 *
 * @author Ewerton Daniel
 *
 * @contact contact@ewertondaniel.work
 *
 * @link https://docs.bitfinex.com/reference/rest-auth-ledgers
 */
class LedgerEntry
{
    /** Ledger identifier. */
    public readonly int $id;

    /** Currency the movement is denominated in (e.g. USD, BTC). */
    public readonly string $currency;

    /** Wallet the movement hit: exchange, margin, funding or contribution. */
    public readonly ?string $wallet;

    /** When the movement was recorded. */
    public readonly ?Carbon $mts;

    /** Signed amount: negative took funds out of the wallet. */
    public readonly float $amount;

    /** Wallet balance after the movement. */
    public readonly float $balance;

    /**
     * What the exchange says happened, e.g. "Exchange 0.9 XMR for USD" or
     * "Settlement @ 185.79 on wallet margin".
     *
     * Free-form and subject to change: read it, never branch on it.
     */
    public readonly ?string $description;

    /**
     * @param  array  $data  Ledger row:
     *                       - [0]: Ledger ID.
     *                       - [1]: Currency.
     *                       - [2]: Wallet.
     *                       - [3]: Timestamp (milliseconds).
     *                       - [4]: PLACEHOLDER.
     *                       - [5]: Amount.
     *                       - [6]: Balance after the movement.
     *                       - [7]: PLACEHOLDER.
     *                       - [8]: Description.
     */
    public function __construct(array $data)
    {
        $this->id = (int) ($data[0] ?? 0);
        $this->currency = (string) ($data[1] ?? '');
        $this->wallet = GetThis::ifTrueOrFallback(isset($data[2]), fn () => (string) $data[2]);
        $this->mts = GetThis::ifTrueOrFallback(isset($data[3]), fn () => Carbon::createFromTimestampMs((int) $data[3]));
        $this->amount = (float) ($data[5] ?? 0);
        $this->balance = (float) ($data[6] ?? 0);
        $this->description = GetThis::ifTrueOrFallback(isset($data[8]), fn () => (string) $data[8]);
    }
}
