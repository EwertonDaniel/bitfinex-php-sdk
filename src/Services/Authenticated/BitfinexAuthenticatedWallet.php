<?php

namespace EwertonDaniel\Bitfinex\Services\Authenticated;

use EwertonDaniel\Bitfinex\Builders\RequestBuilder;
use EwertonDaniel\Bitfinex\Builders\UrlBuilder;
use EwertonDaniel\Bitfinex\Exceptions\BitfinexPathNotFoundException;
use EwertonDaniel\Bitfinex\Http\Requests\BitfinexRequest;
use EwertonDaniel\Bitfinex\Http\Responses\AuthenticatedBitfinexResponse;
use EwertonDaniel\Bitfinex\ValueObjects\BitfinexCredentials;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Class BitfinexAuthenticatedWallet
 *
 * Provides access to wallet-related operations for authenticated users in the Bitfinex API.
 * This class handles retrieving wallet information under the `private.wallets` endpoint.
 *
 * @author  Ewerton Daniel
 * @contact contact@ewertondaniel.work
 */
class BitfinexAuthenticatedWallet
{
    /**
     * Base path for wallet-related API endpoints.
     */
    private readonly string $basePath;

    /**
     * Constructor for initializing dependencies required for wallet operations.
     *
     * @param  UrlBuilder  $url  The URL builder for constructing API paths.
     * @param  BitfinexCredentials  $credentials  The API credentials for authentication.
     * @param  RequestBuilder  $request  The request builder for configuring HTTP requests.
     * @param  Client  $client  The HTTP client for sending requests.
     */
    public function __construct(
        private readonly UrlBuilder $url,
        private readonly BitfinexCredentials $credentials,
        private readonly RequestBuilder $request,
        private readonly Client $client
    ) {
        $this->basePath = 'private';
    }

    /**
     * Retrieves wallet information for the authenticated user.
     *
     * This method sends a request to the `private.wallets` endpoint to fetch
     * details about the user's wallets, including balances and currency information.
     *
     * @link https://docs.bitfinex.com/reference/rest-auth-wallets
     *
     * @return AuthenticatedBitfinexResponse The response containing wallet information.
     *
     * @throws GuzzleException If an HTTP request error occurs.
     * @throws BitfinexPathNotFoundException If the API path cannot be resolved.
     */
    final public function get(): AuthenticatedBitfinexResponse
    {
        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);

        $apiPath = $this->url->setPath("$this->basePath.wallets")->getPath();

        $response = $request->execute(apiPath: $apiPath);

        return $response->wallets();
    }
}
