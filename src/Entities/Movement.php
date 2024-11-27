<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Entities;

use Carbon\Carbon;
use EwertonDaniel\Bitfinex\Helpers\GetThis;

/**
 * Class Movement
 *
 * Represents a movement transaction on the Bitfinex platform, encapsulating details about deposits and withdrawals.
 * Provides structured information about the transaction, including:
 * - Identifiers and timestamps.
 * - Currency and status.
 * - Transaction fees, amounts, and destinations.
 * - Optional information related to external providers and personal notes.
 *
 * This entity is designed for tracking and managing movement-related data in a consistent format.
 *
 * @author Ewerton Daniel
 *
 * @contact contact@ewertondaniel.work
 */
class Movement
{
    /** Movement identifier. */
    public readonly int $id;

    /** The symbol of the currency (e.g., "BTC"). */
    public readonly string $currency;

    /** The extended name of the currency or "WIRE" for fiat. */
    public readonly string $currencyMethod;

    /** Remarks related to the movement. */
    public readonly ?string $remark;

    /** Movement started at (datetime). */
    public readonly Carbon $startedAt;

    /** Movement last updated at (datetime). */
    public readonly Carbon $updatedAt;

    /** Current status. */
    public readonly string $status;

    /** Amount of funds moved (positive for deposits, negative for withdrawals). */
    public readonly float $amount;

    /** Transaction fees applied. */
    public readonly float $fees;

    /** Destination address. */
    public readonly ?string $destinationAddress;

    /** Memo/Tag related to the transaction. */
    public readonly ?string $memo;

    /** Transaction identifier. */
    public readonly ?string $transactionId;

    /** Optional personal transaction note. */
    public readonly ?string $movementNote;

    /** Wire bank fees. */
    public readonly ?float $bankFees;

    /** Identifier of bank router. */
    public readonly ?int $bankRouterId;

    /** External provider movement ID. */
    public readonly ?string $externalBankMovId;

    /** External provider movement status. */
    public readonly ?string $externalBankMovStatus;

    /** External provider movement info. */
    public readonly ?string $externalBankMovDescription;

    /** External provider bank account information for user. */
    public readonly ?array $externalBankAccInfo;

    /**
     * Constructs a Movement entity using data retrieved from the Bitfinex API.
     *
     * @param  array  $data  Array containing:
     *                       - [0]: Movement ID.
     *                       - [1]: Currency symbol (e.g., "BTC").
     *                       - [2]: Currency method or "WIRE" for fiat.
     *                       - [4]: Remark (optional).
     *                       - [5]: Movement start timestamp (milliseconds).
     *                       - [6]: Movement update timestamp (milliseconds).
     *                       - [9]: Status.
     *                       - [12]: Amount (positive for deposits, negative for withdrawals).
     *                       - [13]: Transaction fees.
     *                       - [16]: Destination address (optional).
     *                       - [17]: Memo/Tag (optional).
     *                       - [20]: Transaction ID (optional).
     *                       - [21]: Movement note (optional).
     *                       - [24]: Bank fees (optional).
     *                       - [25]: Bank router ID (optional).
     *                       - [28]: External movement ID (optional).
     *                       - [29]: External movement status (optional).
     *                       - [30]: External movement description (optional).
     *                       - [31]: External bank account info (optional).
     */
    public function __construct(array $data)
    {
        $this->id = (int) $data[0];
        $this->currency = $data[1];
        $this->currencyMethod = $data[2];
        $this->startedAt = Carbon::createFromTimestampMs($data[5]);
        $this->updatedAt = Carbon::createFromTimestampMs($data[6]);
        $this->status = $data[9];
        $this->amount = (float) $data[12];
        $this->fees = (float) $data[13];
        $this->remark = GetThis::ifTrueOrFallback(isset($data[4]), fn () => $data[4]);
        $this->destinationAddress = GetThis::ifTrueOrFallback(isset($data[16]), fn () => $data[16]);
        $this->memo = GetThis::ifTrueOrFallback(isset($data[17]), fn () => $data[17]);
        $this->transactionId = GetThis::ifTrueOrFallback(isset($data[20]), fn () => $data[20]);
        $this->movementNote = GetThis::ifTrueOrFallback(isset($data[21]), fn () => $data[21]);
        $this->bankFees = GetThis::ifTrueOrFallback(isset($data[24]), fn () => (float) $data[24]);
        $this->bankRouterId = GetThis::ifTrueOrFallback(isset($data[25]), fn () => (int) $data[25]);
        $this->externalBankMovId = GetThis::ifTrueOrFallback(isset($data[28]), fn () => $data[28]);
        $this->externalBankMovStatus = GetThis::ifTrueOrFallback(isset($data[29]), fn () => $data[29]);
        $this->externalBankMovDescription = GetThis::ifTrueOrFallback(isset($data[30]), fn () => $data[30]);
        $this->externalBankAccInfo = GetThis::ifTrueOrFallback(isset($data[31]), fn () => $data[31]);
    }
}
