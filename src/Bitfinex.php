<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex;

use EwertonDaniel\Bitfinex\Builders\UrlBuilder;
use EwertonDaniel\Bitfinex\Helpers\GetThis;
use EwertonDaniel\Bitfinex\Services\BitfinexAuthenticated;
use EwertonDaniel\Bitfinex\Services\BitfinexPublic;
use EwertonDaniel\Bitfinex\ValueObjects\BitfinexCredentials;
use Exception;

/**
 * Class Bitfinex
 *
 * Provides a unified interface for interacting with the Bitfinex API.
 * Offers access to public and authenticated endpoints for operations such as
 * retrieving market data, managing orders, wallets, and more.
 *
 * @author  Ewerton Daniel
 *
 * @contact contact@ewertondaniel.work
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
     * Creates an instance to interact with public endpoints of the Bitfinex API.
     *
     * Public endpoints provide access to general market data and platform information.
     *
     * @return BitfinexPublic Instance for interacting with public endpoints.
     *
     * @throws Exception If URL initialization fails.
     */
    final public function public(): BitfinexPublic
    {
        return new BitfinexPublic(
            (new UrlBuilder)->setBaseUrl('public')
        );
    }

    /**
     * Creates an instance to interact with authenticated endpoints of the Bitfinex API.
     *
     * Authenticated endpoints require valid API credentials to access user-specific
     * operations, such as managing orders, viewing account details, and wallets.
     *
     * If no credentials are provided, a default instance is created.
     *
     * @param  BitfinexCredentials|null  $credentials  Optional API credentials.
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
            url: (new UrlBuilder)->setBaseUrl('private'),
            credentials: $credentials
        );
    }
}
