<?php

namespace EwertonDaniel\Bitfinex\Entities;

/**
 * Class TakerFeeInfo
 *
 * Represents taker fee information for a Bitfinex account.
 * This entity provides detailed data about the taker fee rates for various trade types, including:
 * - Crypto to crypto trades.
 * - Crypto to stablecoin trades.
 * - Crypto to fiat trades.
 * - Derivative trades.
 *
 * Key Features:
 * - Simplifies access to taker fee rates for different trading scenarios.
 * - Provides structured data for fee analysis and optimization.
 *
 * @author Ewerton Daniel
 *
 * @contact contact@ewertondaniel.work
 */
class TakerFeeInfo
{
    /** Taker fee rate for crypto to crypto trades. */
    public readonly float $takerFeeToCrypto;

    /** Taker fee rate for crypto to stablecoin trades. */
    public readonly float $takerFeeToStable;

    /** Taker fee rate for crypto to fiat trades. */
    public readonly float $takerFeeToFiat;

    /** Taker fee rate for derivative trades. */
    public readonly float $derivTakerFee;

    /**
     * Constructs a TakerFeeInfo entity using provided data.
     *
     * @param  array  $data  Array containing:
     *                       - [0]: Taker fee rate for crypto to crypto trades.
     *                       - [1]: Taker fee rate for crypto to stablecoin trades.
     *                       - [2]: Taker fee rate for crypto to fiat trades.
     *                       - [5]: Taker fee rate for derivative trades.
     */
    public function __construct(array $data)
    {
        $this->takerFeeToCrypto = $data[0];
        $this->takerFeeToStable = $data[1];
        $this->takerFeeToFiat = $data[2];
        $this->derivTakerFee = $data[5];
    }
}
