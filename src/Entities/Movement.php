<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Entities;

use Carbon\Carbon;
use EwertonDaniel\Bitfinex\Helpers\GetThis;

class Movement
{
    /** @note Movement identifier */
    public readonly int $id;

    /** @note The symbol of the currency (e.g., "BTC") */
    public readonly string $currency;

    /** @note The extended name of the currency or "WIRE" for fiat */
    public readonly string $currencyMethod;

    /** @note Remarks related to the movement */
    public readonly ?string $remark;

    /** @note Movement started at (timestamp) */
    public readonly Carbon $mtsStarted;

    /** @note Movement last updated at (timestamp) */
    public readonly Carbon $mtsUpdated;

    /** @note Current status */
    public readonly string $status;

    /** @note Amount of funds moved (positive for deposits, negative for withdrawals) */
    public readonly float $amount;

    /** @note Tx Fees applied */
    public readonly float $fees;

    /** @note Destination address */
    public readonly ?string $destinationAddress;

    /** @note Memo/Tag related to the transaction */
    public readonly ?string $memo;

    /** @note Transaction identifier */
    public readonly ?string $transactionId;

    /** @note Optional personal transaction note */
    public readonly ?string $movementNote;

    /** @note Wire bank fees */
    public readonly ?float $bankFees;

    /** @note Identifier of bank router */
    public readonly ?int $bankRouterId;

    /** @note External provider movement ID */
    public readonly ?string $externalBankMovId;

    /** @note External provider movement status */
    public readonly ?string $externalBankMovStatus;

    /** @note External provider movement info */
    public readonly ?string $externalBankMovDescription;

    /** @note External provider bank account information for user */
    public readonly ?array $externalBankAccInfo;

    public function __construct(array $data)
    {
        $this->id = (int) $data[0];
        $this->currency = $data[1];
        $this->currencyMethod = $data[2];
        $this->mtsStarted = Carbon::createFromTimestampMs($data[5]);
        $this->mtsUpdated = Carbon::createFromTimestampMs($data[6]);
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
