<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Helpers;

use Closure;

/**
 * Class GetThis
 *
 * A utility class designed to simplify conditional logic and value retrieval.
 * Provides helper methods to determine values based on conditions or defaults,
 * and includes specific logic for Bitfinex-related operations.
 *
 * @author  Ewerton Daniel
 *
 * @contact contact@ewertondaniel.work
 */
class GetThis
{
    /**
     * Returns a callback result or fallback value based on the condition.
     *
     * If the $boolean evaluates to true (non-null and not false), the $callback
     * value is resolved and returned. Otherwise, the $fallback value is resolved.
     *
     * @param  mixed  $boolean  The condition to evaluate.
     * @param  mixed  $callback  The value or closure to resolve if the condition is true.
     * @param  mixed  $fallback  The value or closure to resolve if the condition is false.
     * @return mixed The resolved callback or fallback value.
     */
    public static function ifTrueOrFallback(mixed $boolean, mixed $callback = null, mixed $fallback = null): mixed
    {
        return (! is_null($boolean) && $boolean !== false) ?
            self::resolveValue($callback) :
            self::resolveValue($fallback);
    }

    /**
     * Resolves the given value if it is a closure; otherwise, returns the value directly.
     *
     * @param  mixed  $value  The value or closure to resolve.
     * @return mixed The resolved value.
     */
    private static function resolveValue(mixed $value): mixed
    {
        return is_a($value, Closure::class) ? $value() : $value;
    }

    /**
     * Retrieves the user's IP address from the server's request data.
     *
     * Determines the IP address using HTTP headers or the REMOTE_ADDR variable,
     * prioritizing client-reported IPs. Returns '0.0.0.0' if no valid IP is found.
     *
     * @return string The user's validated IP address or '0.0.0.0' if unavailable.
     */
    final public static function userIp(): string
    {
        $ip = match (true) {
            ! empty($_SERVER['HTTP_CLIENT_IP']) => $_SERVER['HTTP_CLIENT_IP'],
            ! empty($_SERVER['HTTP_X_FORWARDED_FOR']) => explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0],
            ! empty($_SERVER['REMOTE_ADDR']) => $_SERVER['REMOTE_ADDR'],
            default => null,
        };

        return filter_var($ip, FILTER_VALIDATE_IP) ?: '0.0.0.0';
    }
}
