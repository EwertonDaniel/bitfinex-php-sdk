<?php

namespace EwertonDaniel\Bitfinex\Entities;

/**
 * Class MakerFeeInfo
 *
 * Represents maker fee rates and rebates for an account on the Bitfinex platform.
 * Provides structured data for:
 * - Maker fee rates for crypto, stable coins, and fiat.
 * - Maker rebate for derivative trades.
 *
 * @author Ewerton Daniel
 *
 * @contact contact@ewertondaniel.work
 */
class MakerFeeInfo
{
    /** Maker fee rate for crypto trades. */
    public readonly float $makerFeeToCrypto;

    /** Maker fee rate for stablecoin trades. */
    public readonly float $makerFeeToStable;

    /** Maker fee rate for fiat trades. */
    public readonly float $makerFeeToFiat;

    /** Maker rebate for derivative trades. */
    public readonly float $derivRebate;

    /**
     * Constructs a MakerFeeInfo entity using provided data.
     *
     * @param  array  $data  Array containing:
     *                       - [0]: Maker fee rate for crypto.
     *                       - [1]: Maker fee rate for stablecoins.
     *                       - [2]: Maker fee rate for fiat.
     *                       - [5]: Maker rebate for derivative trades.
     */
    public function __construct(array $data)
    {
        $this->makerFeeToCrypto = $data[0];
        $this->makerFeeToStable = $data[1];
        $this->makerFeeToFiat = $data[2];
        $this->derivRebate = $data[5];
    }
}
