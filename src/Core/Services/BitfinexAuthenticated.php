<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Core\Services;

use EwertonDaniel\Bitfinex\Core\Builders\UrlBuilder;
use EwertonDaniel\Bitfinex\Core\ValueObjects\BitfinexCredentials;

class BitfinexAuthenticated
{


    private readonly BitfinexCredentials $credentials;

    public function __construct(private readonly UrlBuilder $url, BitfinexCredentials|null $credentials = null)
    {
        $this->credentials = $credentials ?? new BitfinexCredentials();
        $this->url->setBaseUrl('private');
    }
}
