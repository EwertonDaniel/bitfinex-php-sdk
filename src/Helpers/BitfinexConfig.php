<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Helpers;

/**
 * Class BitfinexConfig
 *
 * Resolves SDK settings from the published Laravel configuration, falling back to
 * environment variables and finally to the value shipped with the package.
 *
 * Reading the environment directly keeps the SDK usable outside Laravel, where the
 * `config()` helper is not available.
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
        if (function_exists('config')) {
            $value = config("bitfinex.$key");

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

        return is_null($value) ? null : (string) $value;
    }

    /**
     * Resolves a setting as a float.
     */
    public static function float(string $key, string $envKey, float $default): float
    {
        return (float) self::get($key, $envKey, $default);
    }

    /**
     * Resolves a setting as an integer.
     */
    public static function int(string $key, string $envKey, int $default): int
    {
        return (int) self::get($key, $envKey, $default);
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
