<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Entities;

use EwertonDaniel\Bitfinex\Helpers\GetThis;

/**
 * Class Withdrawal
 *
 * The DATA field of the notification the withdraw endpoint answers with.
 *
 * Layout taken from the reference on 2026-08-05: 9 fields, with placeholders at
 * [1], [6] and [7]. Not the same record as {@see Movement}, which is what the
 * movements history returns for the same withdrawal later on.
 *
 * @author Ewerton Daniel
 *
 * @contact contact@ewertondaniel.work
 *
 * @link https://docs.bitfinex.com/reference/rest-auth-withdraw
 */
class Withdrawal
{
    /** Withdrawal identifier. */
    public readonly int $id;

    /** Method used, e.g. ethereum, bitcoin. */
    public readonly ?string $method;

    /** Payment id / tag / memo the withdrawal was created with, when the method takes one. */
    public readonly ?string $paymentId;

    /** Wallet the funds left, e.g. exchange, margin, funding. */
    public readonly ?string $wallet;

    /** Amount withdrawn. */
    public readonly float $amount;

    /** Fee charged for the withdrawal. */
    public readonly ?float $fee;

    /**
     * @param  array  $data  Withdrawal row carried in the notification DATA:
     *                       - [0]: Withdrawal ID.
     *                       - [1]: PLACEHOLDER.
     *                       - [2]: Method.
     *                       - [3]: Payment ID.
     *                       - [4]: Wallet.
     *                       - [5]: Amount.
     *                       - [6]: PLACEHOLDER.
     *                       - [7]: PLACEHOLDER.
     *                       - [8]: Withdrawal fee.
     */
    public function __construct(array $data)
    {
        $this->id = (int) ($data[0] ?? 0);
        $this->method = GetThis::ifTrueOrFallback(isset($data[2]), fn () => (string) $data[2]);
        $this->paymentId = GetThis::ifTrueOrFallback(isset($data[3]), fn () => (string) $data[3]);
        $this->wallet = GetThis::ifTrueOrFallback(isset($data[4]), fn () => (string) $data[4]);
        $this->amount = (float) ($data[5] ?? 0);
        $this->fee = GetThis::ifTrueOrFallback(is_numeric($data[8] ?? null), fn () => (float) $data[8]);
    }
}
