<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Services;

use EwertonDaniel\Bitfinex\Builders\RequestBuilder;
use EwertonDaniel\Bitfinex\Builders\UrlBuilder;
use EwertonDaniel\Bitfinex\Exceptions\BitfinexPathNotFoundException;
use EwertonDaniel\Bitfinex\Exceptions\BitfinexUrlNotFoundException;
use EwertonDaniel\Bitfinex\Helpers\GetThis;
use EwertonDaniel\Bitfinex\Services\Authenticated\BitfinexAuthenticatedAccountAction;
use EwertonDaniel\Bitfinex\Services\Authenticated\BitfinexAuthenticatedOrder;
use EwertonDaniel\Bitfinex\Services\Authenticated\BitfinexAuthenticatedWallet;
use EwertonDaniel\Bitfinex\ValueObjects\BitfinexCredentials;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Class BitfinexAuthenticated
 *
 * Handles authentication and interaction with Bitfinex's private API endpoints.
 * Provides methods for managing wallets, orders, and account actions securely.
 *
 * @author  Ewerton Daniel
 *
 * @contact contact@ewertondaniel.work
 */
class BitfinexAuthenticated
{
    /**
     * @var BitfinexCredentials API credentials for authenticated requests.
     */
    protected readonly BitfinexCredentials $credentials;

    /**
     * @var RequestBuilder Builder for constructing HTTP requests.
     */
    protected readonly RequestBuilder $request;

    /**
     * @var Client HTTP client for making API requests.
     */
    private Client $client;

    /**
     * Constructor for initializing the authenticated Bitfinex service.
     *
     * @param  UrlBuilder  $url  URL builder for constructing API paths.
     * @param  BitfinexCredentials|null  $credentials  Optional API credentials.
     *
     * @throws Exception If credentials cannot be initialized.
     */
    public function __construct(private readonly UrlBuilder $url, ?BitfinexCredentials $credentials = null)
    {
        $this->credentials = GetThis::ifTrueOrFallback(
            boolean: (bool) $credentials,
            callback: fn () => $credentials,
            fallback: fn () => new BitfinexCredentials
        );

        $this->request = (new RequestBuilder)->setMethod('POST');
        $this->url->setBaseUrl('private');

        $this->client = new Client([
            'base_uri' => $this->url->getBaseUrl(),
            'timeout' => 3.0,
        ]);
    }

    /**
     * Generates an authentication token for private API endpoints.
     *
     * @param  string  $scope  Token scope (e.g., 'api') (default: 'api').
     * @param  int  $ttl  Time-to-live for the token in seconds (default: 120).
     * @param  bool  $writePermission  Whether the token allows write operations (default: false).
     * @param  array|null  $caps  Additional capabilities for the token (optional).
     * @return $this Authenticated Bitfinex service instance with the token set.
     *
     * @throws BitfinexPathNotFoundException If the API path for token generation is invalid.
     * @throws GuzzleException If the HTTP request fails.
     * @throws BitfinexUrlNotFoundException If the URL for token generation is invalid.
     */
    final public function generateToken(string $scope = 'api', int $ttl = 120, bool $writePermission = false, ?array $caps = null): static
    {
        $response = (new Authenticate($this->credentials, $scope, $ttl, $writePermission, $caps))->authenticate();

        $this->credentials->setToken($response->content['token']);

        return $this;
    }

    /**
     * Retrieves the current authentication token.
     *
     * @return string|null The authentication token, or null if not set.
     */
    final public function getToken(): ?string
    {
        return $this->credentials->getToken();
    }

    /**
     * Access account actions related to Bitfinex.
     *
     * @return BitfinexAuthenticatedAccountAction Instance for account actions.
     */
    final public function accountAction(): BitfinexAuthenticatedAccountAction
    {
        return new BitfinexAuthenticatedAccountAction($this->url, $this->credentials, $this->request, $this->client);
    }

    /**
     * Access wallet-related actions in Bitfinex.
     *
     * @return BitfinexAuthenticatedWallet Instance for wallet actions.
     */
    final public function wallets(): BitfinexAuthenticatedWallet
    {
        return new BitfinexAuthenticatedWallet($this->url, $this->credentials, $this->request, $this->client);
    }

    /**
     * Access order-related actions in Bitfinex.
     *
     * @return BitfinexAuthenticatedOrder Instance for order actions.
     */
    final public function orders(): BitfinexAuthenticatedOrder
    {
        return new BitfinexAuthenticatedOrder($this->url, $this->credentials, $this->request, $this->client);
    }
}
