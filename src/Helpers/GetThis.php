<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Helpers;

use Closure;
use EwertonDaniel\Bitfinex\Exceptions\BitfinexException;
use Symfony\Component\HttpFoundation\Response;

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

    /** @throws BitfinexException */
    public static function type(string $type): mixed
    {
        return match ($type) {
            'trading', 't' => 't',
            'funding', 'f' => 'f',
            default => throw new BitfinexException('Invalid ticker type', Response::HTTP_INTERNAL_SERVER_ERROR)
        };
    }
}
