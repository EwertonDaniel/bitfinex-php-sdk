<?php

namespace EwertonDaniel\Bitfinex\Core\Entities;

class MakerFeeInfo
{
    /** @note Shows the maker fee rate for the account */
    public readonly float $makerFeeToCrypto;

    /** @note Shows the maker fee rate for the account */
    public readonly float $makerFeeToStable;

    /** @note Shows the maker fee rate for the account */
    public readonly float $makerFeeToFiat;

    /** @note Shows the maker rebate for derivative trades on the account */
    public readonly float $derivRebate;

    public function __construct(array $data)
    {
        $this->makerFeeToCrypto = $data[0];
        $this->makerFeeToStable = $data[1];
        $this->makerFeeToFiat = $data[2];
        $this->derivRebate = $data[5];
    }
}
