<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Helpers;

use Illuminate\Container\Container;

/**
 * Class BitfinexConfig
 *
 * Resolves SDK settings from the published Laravel configuration, falling back to
 * environment variables and finally to the value shipped with the package.
 *
 * Reading the environment directly keeps the SDK usable outside Laravel, where no
 * application is bootstrapped to serve the configuration repository.
 *
 * @author  Ewerton Daniel
 *
 * @contact contact@ewertondaniel.work
 */
class BitfinexConfig
{
    /**
     * Resolves a setting, preferring the Laravel configuration over the environment.
     *
     * @param  string  $key  Configuration key relative to the `bitfinex` namespace (e.g. 'urls.public').
     * @param  string  $envKey  Environment variable checked when the configuration is absent.
     * @param  mixed  $default  Value used when neither source provides one.
     */
    public static function get(string $key, string $envKey, mixed $default = null): mixed
    {
        $container = Container::getInstance();

        if ($container->bound('config')) {
            $value = $container->make('config')->get("bitfinex.$key");

            if (! is_null($value) && $value !== '') {
                return $value;
            }
        }

        $value = $_ENV[$envKey] ?? $_SERVER[$envKey] ?? getenv($envKey);

        if (is_string($value) && $value !== '') {
            return $value;
        }

        return $default;
    }

    /**
     * Resolves a setting as a string, or null when it is not defined anywhere.
     */
    public static function string(string $key, string $envKey, ?string $default = null): ?string
    {
        $value = self::get($key, $envKey, $default);

        return GetThis::ifTrueOrFallback(
            boolean: ! is_null($value),
            callback: fn () => (string) $value,
            fallback: null
        );
    }

    /**
     * Resolves a setting as a float, falling back when the value is not numeric.
     *
     * Casting blindly turned a non-numeric setting into `0.0`, and Guzzle reads a
     * timeout of `0` as "no timeout at all", so a typo in the environment made
     * the caller hang on a stalled connection instead of failing.
     */
    public static function float(string $key, string $envKey, float $default): float
    {
        $value = self::get($key, $envKey, $default);

        return GetThis::ifTrueOrFallback(
            boolean: is_numeric($value),
            callback: fn () => (float) $value,
            fallback: $default
        );
    }

    /**
     * Resolves a setting as an integer, falling back when the value is not numeric.
     *
     * Same hazard as `float()`: a non-numeric token TTL used to become `0`.
     */
    public static function int(string $key, string $envKey, int $default): int
    {
        $value = self::get($key, $envKey, $default);

        return GetThis::ifTrueOrFallback(
            boolean: is_numeric($value),
            callback: fn () => (int) $value,
            fallback: $default
        );
    }

    /**
     * Resolves a setting as a boolean, accepting the usual textual environment values.
     */
    public static function bool(string $key, string $envKey, bool $default): bool
    {
        $value = self::get($key, $envKey, $default);

        if (is_bool($value)) {
            return $value;
        }

        return filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? $default;
    }
}
