<?php

namespace EwertonDaniel\Bitfinex\Entities;

use EwertonDaniel\Bitfinex\Helpers\GetThis;

/**
 * Class Wallet
 *
 * Represents a wallet on the Bitfinex platform, encapsulating details such as:
 * - Wallet type (e.g., exchange, margin, funding).
 * - Currency and balance information.
 * - Details about unsettled interest and the available balance.
 * - Metadata about the last ledger entry for the wallet.
 *
 * Key Features:
 * - Tracks balances and wallet-specific details for various wallet types.
 * - Handles optional metadata about the last ledger changes.
 *
 * @author Ewert
 *
 * @contact contact@ewertondaniel.work
 */
class Wallet
{
    /** Wallet type (e.g., exchange, margin, funding). */
    public readonly string $type;

    /** Currency of the wallet (e.g., USD, BTC, ETH). */
    public readonly string $currency;

    /** Current balance of the wallet. */
    public readonly float $balance;

    /** Unsettled interest in the wallet. */
    public readonly float $unsettledInterest;

    /** Available balance for orders, withdrawal, or transfer. */
    public readonly float $availableBalance;

    /** Description of the last ledger entry. */
    public readonly ?string $lastChange;

    /** Optional object with details of the last change. */
    public readonly ?array $lastChangeMetadata;

    /**
     * Constructs a Wallet entity using provided data.
     *
     * @param  array  $data  Array containing wallet details:
     *                       - [0]: Wallet type (string).
     *                       - [1]: Currency (string).
     *                       - [2]: Balance (float).
     *                       - [3]: Unsettled interest (float).
     *                       - [4]: Available balance (float).
     *                       - [5]: Last change description (string, optional).
     *                       - [6]: Last change metadata (array, optional).
     */
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
