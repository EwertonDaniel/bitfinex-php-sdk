<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Services;

use EwertonDaniel\Bitfinex\Builders\RequestBuilder;
use EwertonDaniel\Bitfinex\Builders\UrlBuilder;
use EwertonDaniel\Bitfinex\Exceptions\BitfinexApiException;
use EwertonDaniel\Bitfinex\Exceptions\BitfinexPathNotFoundException;
use EwertonDaniel\Bitfinex\Exceptions\BitfinexUrlNotFoundException;
use EwertonDaniel\Bitfinex\Helpers\GetThis;
use EwertonDaniel\Bitfinex\Http\Responses\AuthenticatedBitfinexResponse;
use EwertonDaniel\Bitfinex\ValueObjects\BitfinexCredentials;
use EwertonDaniel\Bitfinex\ValueObjects\BitfinexSignature;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;

/**
 * Class Authenticate
 *
 * Handles the authentication process for the Bitfinex API.
 * This class is responsible for generating authentication tokens using
 * the private API endpoint `/private/account_actions.generate_token`.
 *
 * @author  Ewerton Daniel
 *
 * @contact contact@ewertondaniel.work
 */
class Authenticate
{
    private Client $client;

    protected readonly RequestBuilder $request;

    private readonly string $apiPath;

    /**
     * Constructs the Authenticate class and initializes required components.
     *
     * @param  BitfinexCredentials  $credentials  API credentials required for authentication.
     * @param  string  $scope  Defines the scope of the authentication token (default: 'api').
     * @param  int  $ttl  Time-to-live for the token in seconds (default: 120).
     * @param  bool  $writePermission  Indicates whether the token has write permissions (default: false).
     * @param  array|null  $caps  Additional capabilities or restrictions for the token.
     * @param  Client|null  $client  Optional HTTP client; the SDK builds its own when omitted.
     *
     * @throws BitfinexPathNotFoundException If the API path could not be built.
     * @throws BitfinexUrlNotFoundException If the base URL could not be resolved.
     */
    public function __construct(
        private readonly BitfinexCredentials $credentials,
        private readonly string $scope = 'api',
        private readonly int $ttl = 120,
        private readonly bool $writePermission = false,
        private readonly ?array $caps = null,
        ?Client $client = null
    ) {
        $url = (new UrlBuilder)->setBaseUrl('private');
        $this->client = $client ?? new Client(config: ['base_uri' => $url->getBaseUrl()]);
        $this->apiPath = $url->setPath('private.account_actions.generate_token')->getPath();
        $this->request = (new RequestBuilder)->setMethod('POST');
    }

    /**
     * Authenticates with the Bitfinex API and generates an authentication token.
     *
     * This method constructs a request payload with the provided credentials,
     * scope, and additional parameters. It also signs the request using the
     * API secret key before sending it to the API endpoint.
     *
     * @return AuthenticatedBitfinexResponse A response object containing the generated token.
     *
     * @throws BitfinexApiException If the API rejects the request.
     * @throws GuzzleException If the HTTP request fails or encounters an error.
     */
    public function authenticate(): AuthenticatedBitfinexResponse
    {
        $this->request->setBody([
            'scope' => $this->scope,
            'ttl' => $this->ttl,
            'writePermission' => $this->writePermission,
            '_cust_ip' => GetThis::userIp(),
        ]);

        $this->request->addBody('caps', $this->caps, true);

        $this->request->setCredentials(
            credentials: $this->credentials,
            signature: $this->getSignature()
        );

        try {
            $apiResponse = $this->client->post($this->apiPath, $this->request->getOptions());
        } catch (RequestException $e) {
            // This call bypasses BitfinexRequest, so it needs the same treatment:
            // the reason the exchange refused sits in the body of an HTTP 500.
            throw BitfinexApiException::fromResponse($e->getResponse(), $e);
        } finally {
            $this->request->reset();
        }

        $response = new AuthenticatedBitfinexResponse($apiResponse);

        $error = BitfinexApiException::fromBody($response->content, $response->statusCode);

        if (! is_null($error)) {
            throw $error;
        }

        return $response->generateToken();
    }

    /**
     * Generates a cryptographic signature for the API request.
     *
     * This method creates a new instance of `BitfinexSignature` by using the API path,
     * the request body, and the API secret key. The signature is used to securely authenticate
     * the request with the Bitfinex API.
     *
     * @return BitfinexSignature A signature object that contains the cryptographic signature for the request.
     */
    private function getSignature(): BitfinexSignature
    {
        return new BitfinexSignature(
            apiPath: $this->apiPath,
            body: $this->request->body->__toString(),
            apiSecret: $this->credentials->getApiSecret()
        );
    }
}
