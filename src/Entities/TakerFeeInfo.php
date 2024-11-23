<?php

namespace EwertonDaniel\Bitfinex\Entities;

class TakerFeeInfo
{
    /** @note Shows the taker fee rate for crypto to crypto trades on the account */
    public readonly float $takerFeeToCrypto;

    /** @note Shows the taker fee rate for crypto to stable coin trades on the account */
    public readonly float $takerFeeToStable;

    /** @note Shows the taker fee rate for crypto to fiat trades on the account */
    public readonly float $takerFeeToFiat;

    /** @note Shows the taker fee rate for derivative trades on the account */
    public readonly float $derivTakerFee;

    public function __construct(array $data)
    {
        $this->takerFeeToCrypto = $data[0];
        $this->takerFeeToStable = $data[1];
        $this->takerFeeToFiat = $data[2];
        $this->derivTakerFee = $data[5];
    }
}
