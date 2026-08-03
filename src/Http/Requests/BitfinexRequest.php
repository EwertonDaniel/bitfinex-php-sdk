<?php

namespace EwertonDaniel\Bitfinex\Http\Requests;

use EwertonDaniel\Bitfinex\Builders\RequestBuilder;
use EwertonDaniel\Bitfinex\Exceptions\BitfinexApiException;
use EwertonDaniel\Bitfinex\Http\Responses\AuthenticatedBitfinexResponse;
use EwertonDaniel\Bitfinex\ValueObjects\BitfinexCredentials;
use EwertonDaniel\Bitfinex\ValueObjects\BitfinexSignature;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;

/**
 * Class BitfinexRequest
 *
 * Handles authenticated HTTP requests to the Bitfinex API.
 *
 * @author  Ewerton Daniel
 *
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
     * The return type names the concrete class rather than the base one. Every
     * caller is an authenticated service that immediately calls a mapper defined
     * on the subclass, so declaring the base type made 73 of the 84 static
     * analysis errors at level 2 and forced the tool down to level 1.
     *
     * @param  string  $apiPath  The API endpoint to which the request is sent.
     * @return AuthenticatedBitfinexResponse The response returned by the Bitfinex API.
     *
     * @throws GuzzleException Thrown if an error occurs during the HTTP request.
     */
    final public function execute(string $apiPath): AuthenticatedBitfinexResponse
    {
        try {
            if ($this->credentials->hasToken()) {
                // The token replaces the key and the signature, not the nonce:
                // the official client sends bfx-nonce on every authenticated
                // request regardless of which credential it carries.
                $this->requestBuilder->setHeaders([
                    'bfx-nonce' => BitfinexSignature::nonce(),
                    'bfx-token' => $this->credentials->getToken(),
                ]);
            } else {
                $this->setCredentials($apiPath);
            }

            $request = $this->client->post($apiPath, $this->requestBuilder->getOptions());
        } catch (RequestException $e) {
            // The API reports failure in the body while returning HTTP 500, so
            // Guzzle's exception carries only the status. Re-raise it with the
            // code and text the exchange actually gave.
            throw BitfinexApiException::fromResponse($e->getResponse(), $e);
        } finally {
            // The builder is shared across sub-services, so a request that fails
            // before getOptions() would otherwise leave its body and auth headers
            // behind for the next call.
            $this->requestBuilder->reset();
        }

        $response = new AuthenticatedBitfinexResponse($request);

        // An error envelope can also arrive with a 2xx status.
        $error = BitfinexApiException::fromBody($response->content, $response->statusCode);

        if (! is_null($error)) {
            throw $error;
        }

        return $response;
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
