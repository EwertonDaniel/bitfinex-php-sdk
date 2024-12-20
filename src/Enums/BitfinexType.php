<?php

namespace EwertonDaniel\Bitfinex\Enums;

use EwertonDaniel\Bitfinex\Helpers\GetThis;

/**
 * Enum BitfinexType
 *
 * Represents the type of entity in the Bitfinex platform (e.g., 'trading' or 'funding').
 * Provides methods to handle symbols associated with each type.
 *
 * @author Ewerton Daniel
 *
 * @contact contact@ewertondaniel.work
 */
enum BitfinexType: string
{
    /** Trading type (e.g., currency pairs such as 'BTCUSD'). */
    case TRADING = 'trading';

    /** Funding type (e.g., funding currencies such as 'USD'). */
    case FUNDING = 'funding';

    /**
     * Generates the symbol prefix based on the Bitfinex type.
     *
     * @param  string  $pairOrCurrency  The pair or currency to prefix.
     * @return string The prefixed symbol (e.g., 'tBTCUSD' for trading, 'fUSD' for funding).
     */
    final public function symbol(string $pairOrCurrency): string
    {
        return match ($this) {
            self::TRADING => GetThis::ifTrueOrFallback(
                boolean: str_starts_with($pairOrCurrency, 't'),
                callback: fn () => $pairOrCurrency,
                fallback: fn () => "t$pairOrCurrency",
            ),
            self::FUNDING => GetThis::ifTrueOrFallback(
                boolean: str_starts_with($pairOrCurrency, 'f'),
                callback: fn () => $pairOrCurrency,
                fallback: fn () => "f$pairOrCurrency",
            ),
        };
    }

    /**
     * Determines the Bitfinex type based on the provided symbol.
     *
     * @param  string  $symbol  The symbol to evaluate (e.g., 'tBTCUSD', 'fUSD').
     * @return BitfinexType The corresponding BitfinexType enum.
     *
     * @throws \InvalidArgumentException If the symbol is invalid.
     */
    final public static function bySymbol(string $symbol): self
    {
        return match (true) {
            str_starts_with($symbol, 't') => self::TRADING,
            str_starts_with($symbol, 'f') => self::FUNDING,
            default => throw new \InvalidArgumentException("Invalid symbol: $symbol"),
        };
    }

    /**
     * Checks if the type is trading.
     *
     * @return bool True if the type is `TRADING`.
     */
    final public function isTrading(): bool
    {
        return $this === self::TRADING;
    }

    /**
     * Checks if the type is funding.
     *
     * @return bool True if the type is `FUNDING`.
     */
    final public function isFunding(): bool
    {
        return $this === self::FUNDING;
    }

    final public function symbols(array $items): string
    {
        return implode(',', array_map([$this, 'symbol'], $items));
    }
}
