<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex;

use EwertonDaniel\Bitfinex\Core\Builders\UrlBuilder;
use EwertonDaniel\Bitfinex\Core\Services\BitfinexAuthenticated;
use EwertonDaniel\Bitfinex\Core\Services\BitfinexPublic;
use EwertonDaniel\Bitfinex\Core\ValueObjects\BitfinexCredentials;
use EwertonDaniel\Bitfinex\Helpers\GetThis;
use Exception;

/**
 * Class Bitfinex
 *
 * This class provides methods to interact with the Bitfinex API, enabling functionalities such as
 * retrieving ticker data, public trades, and platform status.
 *
 * @author Ewerton Daniel
 *
 * @email contact@ewertondaniel.work
 *
 * @version 0.1.1
 *
 * @since 2024-10-22
 *
 * @license MIT License
 *
 * @see https://docs.bitfinex.com/ for API documentation.
 */
class Bitfinex
{
    private UrlBuilder $urlBuilder;

    public function __construct()
    {
        $this->urlBuilder = new UrlBuilder;
    }

    /**
     * Returns an instance of BitfinexPublic to interact with public endpoints.
     *
     * @throws Exception
     */
    final public function public(): BitfinexPublic
    {
        return new BitfinexPublic($this->urlBuilder->setBaseUrl('public'));
    }

    /**
     * Returns an instance of BitfinexAuthenticated to interact with private endpoints.
     *
     * @throws Exception
     */
    final public function authenticated(?BitfinexCredentials $credentials = null): BitfinexAuthenticated
    {
        $credentials = GetThis::ifTrueOrFallback(is_null($credentials), fn () => new BitfinexCredentials, $credentials);

        return new BitfinexAuthenticated(url: $this->urlBuilder->setBaseUrl('private'), credentials: $credentials);
    }
}
