<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex;

use EwertonDaniel\Bitfinex\Core\Builders\UrlBuilder;
use EwertonDaniel\Bitfinex\Core\Services\BitfinexAuthenticated;
use EwertonDaniel\Bitfinex\Core\Services\BitfinexPublic;
use EwertonDaniel\Bitfinex\Core\ValueObjects\BitfinexCredentials;
use Exception;

class Bitfinex
{
    private UrlBuilder $urlBuilder;

    public function __construct()
    {
        $this->urlBuilder = new UrlBuilder;
    }

    /** @throws Exception */
    final public function public(): BitfinexPublic
    {
        return new BitfinexPublic($this->urlBuilder->setBaseUrl('public'));
    }

    /** @throws Exception */
    final public function authenticated(?BitfinexCredentials $credentials = null): BitfinexAuthenticated
    {
        $credentials = $credentials ?? new BitfinexCredentials;

        return new BitfinexAuthenticated(url: $this->urlBuilder->setBaseUrl('private'), credentials: $credentials);
    }
}
