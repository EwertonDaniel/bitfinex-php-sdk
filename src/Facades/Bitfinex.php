<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Facades;

use EwertonDaniel\Bitfinex\Core\Builders\UrlBuilder;
use EwertonDaniel\Bitfinex\Core\Services\BitfinexAuthenticated;
use EwertonDaniel\Bitfinex\Core\Services\BitfinexPublic;
use EwertonDaniel\Bitfinex\Core\ValueObjects\BitfinexCredentials;

class Bitfinex
{
    final public static function public(): BitfinexPublic
    {
        return new BitfinexPublic(url: new UrlBuilder());
    }

    final public static function authenticated(BitfinexCredentials|null $credentials = null): BitfinexAuthenticated
    {
        $credentials = $credentials ?? new BitfinexCredentials();
        return new BitfinexAuthenticated(url: new UrlBuilder(), credentials: $credentials);
    }
}
