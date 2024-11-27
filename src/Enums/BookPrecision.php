<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Enums;

/**
 * Enum BookPrecision
 *
 * Represents the precision levels for Bitfinex order books.
 * These levels control the granularity of price aggregation in the book data:
 * - `P0`: Precision level 0 (most precise, smallest aggregation).
 * - `P1`: Precision level 1.
 * - `P2`: Precision level 2.
 * - `P3`: Precision level 3.
 * - `P4`: Precision level 4 (least precise, largest aggregation).
 * - `R0`: Raw books with no aggregation (full order-level details).
 *
 * Key Features:
 * - Provides human-readable descriptions for each precision level.
 * - Defines constants for each precision level used in the Bitfinex API.
 *
 * @author Ewerton Daniel
 *
 * @contact contact@ewertondaniel.work
 *
 * @link https://docs.bitfinex.com/reference/rest-public-book
 */
enum BookPrecision: string
{
    case P0 = 'p0';
    case P1 = 'p1';
    case P2 = 'p2';
    case P3 = 'p3';
    case P4 = 'p4';
    case R0 = 'r0';

    /**
     * Get the description of the precision level.
     *
     * @return string Description of the precision level.
     */
    final public function description(): string
    {
        return match ($this) {
            self::P0 => 'Most precise, smallest aggregation',
            self::P1 => 'Precision level 1',
            self::P2 => 'Precision level 2',
            self::P3 => 'Precision level 3',
            self::P4 => 'Least precise, largest aggregation',
            self::R0 => 'Raw order book with no aggregation',
        };
    }
}
