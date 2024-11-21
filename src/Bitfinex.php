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
 * Provides a streamlined interface to interact with the Bitfinex API, facilitating operations.
 *
 * @author  Ewerton Daniel
 *
 * @email   contact@ewertondaniel.work
 *
 * @since   2024-10-22
 *
 * @license MIT License
 *
 * @see     https://docs.bitfinex.com/ for API documentation.
 */
class Bitfinex
{
    /**
     * The builder for constructing API endpoint URLs.
     */
    private UrlBuilder $urlBuilder;

    /**
     * Initializes the URL builder for interacting with the Bitfinex API.
     */
    public function __construct()
    {
        $this->urlBuilder = new UrlBuilder;
    }

    /**
     * Provides access to public endpoints of the Bitfinex API.
     *
     * This method initializes the `BitfinexPublic` service, which allows interaction with
     * public endpoints, such as retrieving market data and platform status.
     *
     * @return BitfinexPublic Instance for interacting with public endpoints.
     *
     * @throws Exception If URL initialization fails.
     */
    final public function public(): BitfinexPublic
    {
        return new BitfinexPublic($this->urlBuilder->setBaseUrl('public'));
    }

    /**
     * Provides access to private (authenticated) endpoints of the Bitfinex API.
     *
     * This method initializes the `BitfinexAuthenticated` service, which requires
     * API credentials to interact with private endpoints, such as account details and
     * user-specific operations.
     *
     * If no credentials are provided, a default instance is created.
     *
     * @param  BitfinexCredentials|null  $credentials  Optional API credentials for authentication.
     * @return BitfinexAuthenticated Instance for interacting with private endpoints.
     *
     * @throws Exception If URL initialization or credentials setup fails.
     */
    final public function authenticated(?BitfinexCredentials $credentials = null): BitfinexAuthenticated
    {
        $credentials = GetThis::ifTrueOrFallback(
            boolean: is_null($credentials),
            callback: fn () => new BitfinexCredentials,
            fallback: $credentials
        );

        return new BitfinexAuthenticated(
            url: $this->urlBuilder->setBaseUrl('private'),
            credentials: $credentials
        );
    }
}
