<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Entities;

use EwertonDaniel\Bitfinex\Helpers\GetThis;

class TxStatus
{
    public readonly string $method;
    public readonly ?int $depositStatus;
    public readonly ?int $withdrawStatus;
    public readonly ?int $paymentIdDeposit;
    public readonly ?int $paymentIdWithdraw;
    public readonly ?int $depositConfirmationsRequired;

    /**
     * Structure indexes:
     * [0] METHOD, [1] DEP_STATUS, [2] WD_STATUS, [7] PAYMENT_ID_DEP, [8] PAYMENT_ID_WD, [11] DEPOSIT_CONFIRMATIONS_REQUIRED
     */
    public function __construct(array $data)
    {
        $this->method = (string) $data[0];
        $this->depositStatus = GetThis::ifTrueOrFallback(isset($data[1]), fn () => (int) $data[1]);
        $this->withdrawStatus = GetThis::ifTrueOrFallback(isset($data[2]), fn () => (int) $data[2]);
        $this->paymentIdDeposit = GetThis::ifTrueOrFallback(isset($data[7]), fn () => (int) $data[7]);
        $this->paymentIdWithdraw = GetThis::ifTrueOrFallback(isset($data[8]), fn () => (int) $data[8]);
        $this->depositConfirmationsRequired = GetThis::ifTrueOrFallback(isset($data[11]), fn () => (int) $data[11]);
    }
}

