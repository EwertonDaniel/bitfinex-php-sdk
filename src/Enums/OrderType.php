<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Enums;

/**
 * Enum OrderType
 *
 * Represents the types of orders available in the Bitfinex platform.
 *
 * @author  Ewerton Daniel
 *
 * @contact contact@ewertondaniel.work
 */
enum OrderType: string
{
    case LIMIT = 'LIMIT';
    case EXCHANGE_LIMIT = 'EXCHANGE LIMIT';
    case MARKET = 'MARKET';
    case EXCHANGE_MARKET = 'EXCHANGE MARKET';
    case STOP = 'STOP';
    case EXCHANGE_STOP = 'EXCHANGE STOP';
    case STOP_LIMIT = 'STOP LIMIT';
    case EXCHANGE_STOP_LIMIT = 'EXCHANGE STOP LIMIT';
    case TRAILING_STOP = 'TRAILING STOP';
    case EXCHANGE_TRAILING_STOP = 'EXCHANGE TRAILING STOP';
    case FOK = 'FOK';
    case EXCHANGE_FOK = 'EXCHANGE FOK';
    case IOC = 'IOC';
    case EXCHANGE_IOC = 'EXCHANGE IOC';

    /**
     * Returns a human-readable description of the order type.
     */
    final public function description(): string
    {
        return match ($this) {
            self::LIMIT => 'A limit order is an order to buy or sell at a specific price or better.',
            self::EXCHANGE_LIMIT => 'A limit order executed directly on the exchange, bypassing margin funding.',
            self::MARKET => 'A market order is executed immediately at the current market price.',
            self::EXCHANGE_MARKET => 'A market order executed directly on the exchange, bypassing margin funding.',
            self::STOP => 'A stop order is an instruction to buy or sell once the market reaches a specified price.',
            self::EXCHANGE_STOP => 'A stop order executed directly on the exchange, bypassing margin funding.',
            self::STOP_LIMIT => 'A stop-limit order combines the features of a stop order and a limit order. The stop order triggers the submission of a limit order.',
            self::EXCHANGE_STOP_LIMIT => 'A stop-limit order executed directly on the exchange, bypassing margin funding.',
            self::TRAILING_STOP => 'A trailing stop order sets a dynamic stop price that adjusts with the market price.',
            self::EXCHANGE_TRAILING_STOP => 'A trailing stop order executed directly on the exchange, bypassing margin funding.',
            self::FOK => 'A Fill or Kill (FOK) order must be executed immediately in its entirety or canceled.',
            self::EXCHANGE_FOK => 'A Fill or Kill (FOK) order executed directly on the exchange, bypassing margin funding.',
            self::IOC => 'An Immediate or Cancel (IOC) order must be executed immediately, fully or partially, or canceled.',
            self::EXCHANGE_IOC => 'An Immediate or Cancel (IOC) order executed directly on the exchange, bypassing margin funding.',
        };
    }

    final public function title(): string
    {
        return strtolower($this->value);
    }
}
