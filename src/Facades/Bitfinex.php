<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Facades;

use EwertonDaniel\Bitfinex\Core\Builders\UrlBuilder;
use EwertonDaniel\Bitfinex\Core\Services\BitfinexAuthenticated;
use EwertonDaniel\Bitfinex\Core\Services\BitfinexPublic;
use EwertonDaniel\Bitfinex\Core\ValueObjects\BitfinexCredentials;
use Exception;

class Bitfinex
{
    /** @throws Exception */
    final public static function public(): BitfinexPublic
    {
        return new BitfinexPublic(url: (new UrlBuilder)->setBaseUrl('public'));
    }

    /** @throws Exception */
    final public static function authenticated(?BitfinexCredentials $credentials = null): BitfinexAuthenticated
    {
        $credentials = $credentials ?? new BitfinexCredentials;

        return new BitfinexAuthenticated(url: (new UrlBuilder)->setBaseUrl('private'), credentials: $credentials);
    }
}
