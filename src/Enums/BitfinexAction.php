<?php

namespace EwertonDaniel\Bitfinex\Enums;

use EwertonDaniel\Bitfinex\Exceptions\BitfinexException;
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
     * Zero has no direction, and neither does a non-finite value. Both used to
     * fall through to `SELL`, which answers a question the amount does not
     * actually answer, so they are rejected instead.
     *
     * @param  float  $amount  Trade amount (positive for buy, negative for sell).
     * @return BitfinexAction The corresponding action.
     *
     * @throws BitfinexException When the amount does not carry a direction.
     */
    public static function fromAmount(float $amount): self
    {
        if (! is_finite($amount) || $amount === 0.0) {
            throw new BitfinexException(
                'Cannot derive a trade direction from the amount '.var_export($amount, true).
                '. Pass a non-zero, finite amount, or state the action explicitly.'
            );
        }

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
     * @return int 1 for buy, -1 for sell.
     *
     * @link https://docs.bitfinex.com/reference/rest-auth-calc-order-avail
     */
    final public function dir(): int
    {
        return GetThis::ifTrueOrFallback(boolean: $this->isBuy(), callback: 1, fallback: -1);
    }

    /**
     * Determines which side of the order book a row sits on, from its amount.
     *
     * In a Bitfinex book a positive amount is a bid and a negative amount is an
     * ask. This is the opposite of the order semantics above, where a positive
     * amount means buy: the book states what the resting order offers, so bids
     * carry the positive side. Deriving the side from the order semantics
     * labelled every row backwards.
     *
     * @param  float  $amount  Book row amount (positive on the bid side, negative on the ask side).
     * @return string 'bid' for a positive amount, 'ask' for a negative one.
     */
    final public static function bidOrAskByAmount(float $amount): string
    {
        return GetThis::ifTrueOrFallback(boolean: $amount > 0, callback: 'bid', fallback: 'ask');
    }
}
