<?php

namespace EwertonDaniel\Bitfinex\Core\Entities;

use EwertonDaniel\Bitfinex\Helpers\GetThis;

class Wallet
{
    /** @note Wallet type (e.g., exchange, margin, funding) */
    public readonly string $type;

    /** @note Currency of the wallet (e.g., USD, BTC, ETH) */
    public readonly string $currency;

    /** @note Current balance of the wallet */
    public readonly float $balance;

    /** @note Unsettled interest in the wallet */
    public readonly float $unsettledInterest;

    /** @note Available balance for orders, withdrawal, or transfer */
    public readonly float $availableBalance;

    /** @note Description of the last ledger entry */
    public readonly ?string $lastChange;

    /** @note Optional object with details of the last change */
    public readonly ?array $lastChangeMetadata;

    public function __construct(array $data)
    {
        $this->type = GetThis::ifTrueOrFallback(isset($data[0]), fn () => $data[0]);
        $this->currency = GetThis::ifTrueOrFallback(isset($data[1]), fn () => $data[1]);
        $this->balance = GetThis::ifTrueOrFallback(isset($data[2]), fn () => $data[2], 0.0);
        $this->unsettledInterest = GetThis::ifTrueOrFallback(isset($data[3]), fn () => $data[3], 0.0);
        $this->availableBalance = GetThis::ifTrueOrFallback(isset($data[4]), fn () => $data[4], 0.0);
        $this->lastChange = GetThis::ifTrueOrFallback(isset($data[5]), fn () => $data[5]);
        $this->lastChangeMetadata = GetThis::ifTrueOrFallback(isset($data[6]), fn () => $data[6], []);
    }
}
