<?php

namespace EwertonDaniel\Bitfinex\Enums;

use EwertonDaniel\Bitfinex\Helpers\GetThis;

/**
 * Enum BitfinexAction
 *
 * Represents the action type in a trade (buy or sell) on the Bitfinex platform.
 * Provides utility methods for determining the action type and its direction, as well as deriving bid or ask behavior based on the amount.
 *
 * Key Features:
 * - Defines `BUY` and `SELL` actions.
 * - Infers action type based on a positive or negative trade amount.
 * - Simplifies bid/ask determination for trading logic.
 *
 * @author Ewerton Daniel
 *
 * @contact contact@ewertondaniel.work
 */
enum BitfinexAction: string
{
    case BUY = 'buy';
    case SELL = 'sell';

    /**
     * Determines the action type based on the trade amount.
     *
     * @param  float  $amount  Trade amount (positive for buy, negative for sell).
     * @return BitfinexAction The corresponding action.
     */
    public static function fromAmount(float $amount): self
    {
        return GetThis::ifTrueOrFallback(boolean: $amount > 0, callback: self::BUY, fallback: self::SELL);
    }

    /**
     * Checks if the action is a sell.
     *
     * @return bool True if the action is `SELL`.
     */
    final public function isSell(): bool
    {
        return $this === self::SELL;
    }

    /**
     * Checks if the action is a buy.
     *
     * @return bool True if the action is `BUY`.
     */
    final public function isBuy(): bool
    {
        return $this === self::BUY;
    }

    /**
     * Returns the direction of the action for trading logic.
     *
     * @return int -1 for buy, 1 for sell.
     */
    final public function dir(): int
    {
        return GetThis::ifTrueOrFallback(boolean: $this->isBuy(), callback: -1, fallback: 1);
    }

    /**
     * Determines whether the action corresponds to a bid or an ask based on the amount.
     *
     * @param  float  $amount  Trade amount (positive for buy, negative for sell).
     * @return string 'ask' for buy, 'bid' for sell.
     */
    final public static function bidOrAskByAmount(float $amount): string
    {
        return GetThis::ifTrueOrFallback(boolean: self::fromAmount($amount)->isBuy(), callback: 'ask', fallback: 'bid');
    }
}
