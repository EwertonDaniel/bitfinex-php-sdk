<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Helpers;

use Closure;

class GetThis
{
    public static function ifTrueOrFallback(mixed $boolean, mixed $callback, mixed $fallback = null): mixed
    {
        return (! is_null($boolean) && $boolean !== false) ?
            self::resolveValue($callback) :
            self::resolveValue($fallback);
    }

    private static function resolveValue(mixed $value): mixed
    {
        return is_a($value, Closure::class) ? $value() : $value;
    }
}
