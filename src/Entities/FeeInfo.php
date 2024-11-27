<?php

namespace EwertonDaniel\Bitfinex\Entities;

/**
 * Class FeeInfo
 *
 * Represents fee rate information for the Bitfinex platform, including
 * current maker and taker fee rates. Provides structured access to fee
 * data for trading operations.
 *
 * - `makerFeeInfo`: Details about maker fee rates.
 * - `takerFeeInfo`: Details about taker fee rates.
 *
 * This entity encapsulates the maker and taker fee structures to simplify
 * the handling of fee-related data in the Bitfinex API.
 *
 * @author Ewerton Daniel
 *
 * @contact contact@ewertondaniel.work
 */
class FeeInfo
{
    /** Information about the maker fee rates. */
    public readonly MakerFeeInfo $makerFeeInfo;

    /** Information about the taker fee rates. */
    public readonly TakerFeeInfo $takerFeeInfo;

    /**
     * Constructs a FeeInfo entity using provided data.
     *
     * @param  array  $data  An array containing:
     *                       - [0]: Maker fee information.
     *                       - [1]: Taker fee information.
     */
    public function __construct(array $data)
    {
        $this->makerFeeInfo = new MakerFeeInfo($data[0]);
        $this->takerFeeInfo = new TakerFeeInfo($data[1]);
    }
}
