<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Core\Services;

use EwertonDaniel\Bitfinex\Core\Builders\RequestBuilder;
use EwertonDaniel\Bitfinex\Core\Builders\UrlBuilder;
use EwertonDaniel\Bitfinex\Core\ValueObjects\BitfinexCredentials;
use EwertonDaniel\Bitfinex\Core\ValueObjects\BitfinexSignature;
use EwertonDaniel\Bitfinex\Exceptions\BitfinexPathNotFoundException;
use EwertonDaniel\Bitfinex\Helpers\GetThis;
use EwertonDaniel\Bitfinex\Http\Responses\AuthenticatedBitfinexResponse;
use EwertonDaniel\Bitfinex\Http\Responses\BitfinexResponse;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Class BitfinexAuthenticated
 *
 * Handles authentication for accessing private endpoints in the Bitfinex API.
 * This class is responsible for generating tokens and ensuring secure interactions
 * with private endpoints through appropriate request builders and credentials.
 *
 * @author  Ewerton Daniel
 *
 * @contact contact@ewertondaniel.work
 */
class BitfinexAuthenticated
{
    /**
     * Holds the Bitfinex API credentials.
     */
    private readonly BitfinexCredentials $credentials;

    /**
     * Builder for constructing HTTP requests.
     */
    private readonly RequestBuilder $request;

    /**
     * HTTP client for sending requests to the Bitfinex API.
     */
    private Client $client;

    /**
     * Constructor initializes the URL builder and ensures credentials are set.
     * If no credentials are provided, a default instance is created.
     *
     * @param  UrlBuilder  $url  Builder for constructing API endpoint URLs.
     * @param  BitfinexCredentials|null  $credentials  Optional API credentials.
     *
     * @throws Exception If an error occurs during credential fallback or setup.
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

        $this->client = new Client(config: ['base_uri' => $this->url->getBaseUrl(), 'timeout' => 3.0]);
    }

    /**
     * Generates a token for authenticated requests to private endpoints.
     *
     * This method constructs the request body, sets the required credentials, and sends
     * the request to the Bitfinex API. The token is generated for a specific scope,
     * with an optional time-to-live (TTL) and write permissions.
     *
     * @param  string  $scope  The scope of the token (default: 'api').
     * @param  int  $ttl  The token's time-to-live in seconds (default: 120).
     * @param  bool  $writePermission  Whether the token has write permissions (default: false).
     * @return BitfinexResponse The generated token.
     *
     * @throws BitfinexPathNotFoundException If the API path for token generation is not found.
     * @throws GuzzleException If the HTTP request to the API fails.
     */
    final public function generateToken(string $scope = 'api', int $ttl = 120, bool $writePermission = false, ?array $caps = null): BitfinexResponse
    {
        $apiPath = $this->url->setPath('private.account_actions.generate_token')->getPath();

        $this->request->setBody([
            'scope' => $scope,
            'ttl' => $ttl,
            'writePermission' => $writePermission,
            'caps' => $caps,
            '_cust_ip' => GetThis::userIp(),
        ]);

        $this->request->setCredentials(
            credentials: $this->credentials,
            signature: new BitfinexSignature(
                apiPath: $apiPath,
                body: $this->request->body->__toString(),
                apiSecret: $this->credentials->apiSecret
            )
        );

        $response = new AuthenticatedBitfinexResponse($this->client->post($apiPath, $this->request->getOptions()));

        $response->generateToken();

        $this->credentials->setToken($response->content['token']);

        return $response;
    }

    /**
     * @throws GuzzleException
     * @throws BitfinexPathNotFoundException
     */
    final public function userInfo(): BitfinexResponse
    {
        $apiPath = $this->url->setPath('private.account_actions.user_info')->getPath();

        $request = $this->request($apiPath);

        $response = new AuthenticatedBitfinexResponse($request);

        return $response->userInfo();
    }

    /**
     * @throws GuzzleException
     * @throws BitfinexPathNotFoundException
     */
    final public function summary(): BitfinexResponse
    {
        $request = $this->request($this->url->setPath('private.account_actions.summary')->getPath());

        $response = new AuthenticatedBitfinexResponse($request);

        return $response->summary();
    }

    /**
     * @throws GuzzleException
     * @throws BitfinexPathNotFoundException
     */
    final public function loginHistory(): BitfinexResponse
    {
        $request = $this->request($this->url->setPath('private.account_actions.login_history')->getPath());

        $response = new AuthenticatedBitfinexResponse($request);

        return $response->loginHistory();
    }

    /**
     * @throws GuzzleException
     * @throws BitfinexPathNotFoundException
     */
    final public function wallets(): BitfinexResponse
    {
        $request = $this->request($this->url->setPath('private.wallets')->getPath());

        $response = new AuthenticatedBitfinexResponse($request);

        return $response->wallets();
    }

    private function setEmptyBodyCredentials(string $apiPath): void
    {
        $this->request->setCredentials(
            credentials: $this->credentials,
            signature: new BitfinexSignature(
                apiPath: $apiPath,
                body: $this->request->body->__toString(),
                apiSecret: $this->credentials->apiSecret
            )
        );
    }

    /**
     * @throws GuzzleException
     */
    private function request(string $apiPath): \Psr\Http\Message\ResponseInterface
    {
        if (isset($this->credentials->token)) {
            $this->request->setHeaders(['bfx-token' => $this->credentials->token]);
            $request = $this->client->post($apiPath, ['headers' => $this->request->headers->get()]);
        } else {
            $this->setEmptyBodyCredentials($apiPath);
            $request = $this->client->post($apiPath, $this->request->getOptions());
        }

        return $request;
    }
}
