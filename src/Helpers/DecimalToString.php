<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Helpers;

use EwertonDaniel\Bitfinex\Exceptions\BitfinexException;

/**
 * Class DecimalToString
 *
 * Converts monetary values into the plain decimal strings expected by the Bitfinex API.
 *
 * Casting a float directly to string renders small values in scientific notation
 * (e.g. `1.0E-7`), which the API does not accept.
 *
 * Floats are rendered through their shortest round-tripping decimal form rather
 * than through `number_format()`. `number_format()` rounds internally, and PHP 8.4
 * removed the pre-rounding step `round()` relied on, so the same float produced
 * different strings on different supported PHP versions: on 8.4, `37204349.7`
 * serialized as `37204349.70000001`. Sending a value the caller did not write is
 * not acceptable on an exchange, so the conversion is now version-stable.
 *
 * Strings are validated and normalized instead of passed through, since the
 * previous passthrough let scientific notation and non-numeric text reach the
 * wire unchanged.
 *
 * @author  Ewerton Daniel
 *
 * @contact contact@ewertondaniel.work
 */
class DecimalToString
{
    /** @note Bitfinex accepts up to 8 decimal places for amounts and rates. */
    private const SCALE = 8;

    /** Widest precision a 64-bit double can need to round-trip. */
    private const MAX_SIGNIFICANT_DIGITS = 17;

    /**
     * Converts a numeric value into a plain decimal string.
     *
     * @param  float|int|string|null  $value  The value to convert.
     * @return string|null The decimal representation, or null when no value is given.
     *
     * @throws BitfinexException When the value is not a finite number, or when a
     *                           non-zero value would be sent as zero at the API scale.
     */
    public static function convert(float|int|string|null $value): ?string
    {
        if (is_null($value)) {
            return null;
        }

        $plain = self::toPlainDecimal($value);
        $scaled = self::applyScale($plain);

        if (self::isZero($scaled) && ! self::isZero($plain)) {
            throw new BitfinexException(
                "Value \"$plain\" is smaller than the ".self::SCALE.' decimal places the Bitfinex API accepts, '.
                'and would be sent as zero. Round it to a representable amount before passing it in.'
            );
        }

        return $scaled;
    }

    /**
     * Renders a value as plain decimal text, free of exponent notation.
     *
     * @throws BitfinexException
     */
    private static function toPlainDecimal(float|int|string $value): string
    {
        if (is_int($value)) {
            return (string) $value;
        }

        if (is_string($value)) {
            $trimmed = trim($value);

            if (! is_numeric($trimmed)) {
                throw new BitfinexException("Value \"$value\" is not a valid decimal.");
            }

            return self::expandExponent($trimmed);
        }

        if (! is_finite($value)) {
            throw new BitfinexException('Value must be a finite number, got '.var_export($value, true).'.');
        }

        return self::expandExponent(self::shortestRoundTrip($value));
    }

    /**
     * Finds the shortest decimal form that still converts back to the same double.
     *
     * `%G` is used rather than `%F` so the search covers magnitudes at both ends;
     * the exponent it may produce is expanded afterwards.
     */
    private static function shortestRoundTrip(float $value): string
    {
        for ($precision = 1; $precision < self::MAX_SIGNIFICANT_DIGITS; $precision++) {
            $candidate = sprintf('%.'.$precision.'G', $value);

            if ((float) $candidate === $value) {
                return $candidate;
            }
        }

        return sprintf('%.'.self::MAX_SIGNIFICANT_DIGITS.'G', $value);
    }

    /**
     * Rewrites scientific notation as plain decimal text, without float arithmetic.
     */
    private static function expandExponent(string $number): string
    {
        if (! preg_match('/^([+-]?)(\d+)(?:\.(\d*))?[eE]([+-]?\d+)$/', $number, $matches)) {
            return self::stripPlus($number);
        }

        [, $sign, $integer, $fraction, $exponent] = $matches;

        $digits = $integer.$fraction;
        $pointAt = strlen($integer) + (int) $exponent;

        $plain = match (true) {
            $pointAt <= 0 => '0.'.str_repeat('0', -$pointAt).$digits,
            $pointAt >= strlen($digits) => $digits.str_repeat('0', $pointAt - strlen($digits)),
            default => substr($digits, 0, $pointAt).'.'.substr($digits, $pointAt),
        };

        return self::stripPlus($sign.$plain);
    }

    /**
     * Rounds the decimal text to the API scale and drops redundant trailing zeros.
     *
     * Rounding is done on the digits themselves so a long input cannot pick up
     * float error on the way.
     */
    private static function applyScale(string $plain): string
    {
        $negative = str_starts_with($plain, '-');
        [$integer, $fraction] = array_pad(explode('.', ltrim($plain, '-'), 2), 2, '');

        if (strlen($fraction) > self::SCALE) {
            $kept = substr($fraction, 0, self::SCALE);

            if ((int) $fraction[self::SCALE] >= 5) {
                $carried = self::increment($integer.$kept);
                $integer = substr($carried, 0, strlen($carried) - self::SCALE);
                $kept = substr($carried, strlen($carried) - self::SCALE);
            }

            $fraction = $kept;
        }

        $fraction = rtrim($fraction, '0');
        $integer = ltrim($integer, '0');

        $decimal = GetThis::ifTrueOrFallback(
            boolean: $fraction === '',
            callback: fn () => self::orZero($integer),
            fallback: fn () => self::orZero($integer).'.'.$fraction
        );

        return GetThis::ifTrueOrFallback(
            boolean: $negative && ! self::isZero($decimal),
            callback: fn () => "-$decimal",
            fallback: $decimal
        );
    }

    /**
     * Adds one to a string of digits, propagating the carry.
     */
    private static function increment(string $digits): string
    {
        for ($position = strlen($digits) - 1; $position >= 0; $position--) {
            if ($digits[$position] !== '9') {
                $digits[$position] = (string) ((int) $digits[$position] + 1);

                return $digits;
            }

            $digits[$position] = '0';
        }

        return '1'.$digits;
    }

    private static function stripPlus(string $number): string
    {
        return ltrim($number, '+');
    }

    private static function orZero(string $integer): string
    {
        return GetThis::ifTrueOrFallback(boolean: $integer === '', callback: '0', fallback: $integer);
    }

    private static function isZero(string $decimal): bool
    {
        return rtrim(rtrim(ltrim($decimal, '-0'), '0'), '.') === '';
    }
}
