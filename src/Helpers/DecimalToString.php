<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Helpers;

/**
 * Class DecimalToString
 *
 * Converts monetary values into the plain decimal strings expected by the Bitfinex API.
 *
 * Casting a float directly to string renders small values in scientific notation
 * (e.g. `1.0E-7`), which the API does not accept. Strings are passed through
 * untouched so callers can keep full precision by formatting the value themselves.
 *
 * @author  Ewerton Daniel
 *
 * @contact contact@ewertondaniel.work
 */
class DecimalToString
{
    /** @note Bitfinex accepts up to 8 decimal places for amounts and rates. */
    private const SCALE = 8;

    /**
     * Converts a numeric value into a plain decimal string.
     *
     * @param  float|int|string|null  $value  The value to convert.
     * @return string|null The decimal representation, or null when no value is given.
     */
    public static function convert(float|int|string|null $value): ?string
    {
        if (is_null($value)) {
            return null;
        }

        if (is_string($value)) {
            return $value;
        }

        $formatted = number_format((float) $value, self::SCALE, '.', '');

        return rtrim(rtrim($formatted, '0'), '.') ?: '0';
    }
}
