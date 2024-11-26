<?php

namespace EwertonDaniel\Bitfinex\Http\Requests;

use EwertonDaniel\Bitfinex\Builders\RequestBuilder;
use EwertonDaniel\Bitfinex\Http\Responses\AuthenticatedBitfinexResponse;
use EwertonDaniel\Bitfinex\Http\Responses\BitfinexResponse;
use EwertonDaniel\Bitfinex\ValueObjects\BitfinexCredentials;
use EwertonDaniel\Bitfinex\ValueObjects\BitfinexSignature;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Class BitfinexRequest
 *
 * Handles authenticated HTTP requests to the Bitfinex API.
 *
 * @author  Ewerton Daniel
 * @contact contact@ewertondaniel.work
 */
class BitfinexRequest
{
    /**
     * Constructor initializes the request builder, credentials, and HTTP client.
     *
     * @param  RequestBuilder  $requestBuilder  Instance of the request builder to manage request settings.
     * @param  BitfinexCredentials  $credentials  Holds API credentials (key, secret, and optional token).
     * @param  Client  $client  Instance of GuzzleHttp client for executing HTTP requests.
     */
    public function __construct(
        private readonly RequestBuilder $requestBuilder,
        private readonly BitfinexCredentials $credentials,
        private readonly Client $client
    ) {}

    /**
     * Executes a POST request to the specified API path.
     * If a token is present in the credentials, it is used for authentication; otherwise, a signature is generated.
     *
     * @param  string  $apiPath  The API endpoint to which the request is sent.
     * @return BitfinexResponse The response returned by the Bitfinex API.
     *
     * @throws GuzzleException Thrown if an error occurs during the HTTP request.
     */
    final public function execute(string $apiPath): BitfinexResponse
    {
        if ($this->credentials->hasToken()) {
            $this->requestBuilder->setHeaders(['bfx-token' => $this->credentials->getToken()]);
        } else {
            $this->setCredentials($apiPath);
        }

        $request = $this->client->post($apiPath, $this->requestBuilder->getOptions());

        return new AuthenticatedBitfinexResponse($request);
    }

    /**
     * Sets the necessary credentials for the API request by generating a signature.
     *
     * @param  string  $apiPath  The API endpoint for which the signature is generated.
     */
    private function setCredentials(string $apiPath): void
    {
        $this->requestBuilder->setCredentials(
            credentials: $this->credentials,
            signature: new BitfinexSignature(
                apiPath: $apiPath,
                body: $this->requestBuilder->body->__toString(),
                apiSecret: $this->credentials->getApiSecret()
            )
        );
    }
}
