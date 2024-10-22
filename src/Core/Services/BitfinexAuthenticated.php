<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Core\Services;

use EwertonDaniel\Bitfinex\Core\Builders\UrlBuilder;
use EwertonDaniel\Bitfinex\Core\ValueObjects\BitfinexCredentials;
use Exception;

class BitfinexAuthenticated
{
    private readonly BitfinexCredentials $credentials;

    /** @throws Exception */
    public function __construct(private readonly UrlBuilder $url, ?BitfinexCredentials $credentials = null)
    {
        $this->credentials = $credentials ?? new BitfinexCredentials;
        $this->url->setBaseUrl('private');
    }
}
