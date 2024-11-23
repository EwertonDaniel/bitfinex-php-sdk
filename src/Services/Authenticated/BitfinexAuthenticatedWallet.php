<?php

namespace EwertonDaniel\Bitfinex\Services\Authenticated;

use EwertonDaniel\Bitfinex\Builders\RequestBuilder;
use EwertonDaniel\Bitfinex\Builders\UrlBuilder;
use EwertonDaniel\Bitfinex\Exceptions\BitfinexPathNotFoundException;
use EwertonDaniel\Bitfinex\Http\Requests\BitfinexRequest;
use EwertonDaniel\Bitfinex\Http\Responses\BitfinexResponse;
use EwertonDaniel\Bitfinex\ValueObjects\BitfinexCredentials;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class BitfinexAuthenticatedWallet
{
    private readonly string $basePath;

    public function __construct(
        private readonly UrlBuilder $url,
        private readonly BitfinexCredentials $credentials,
        private readonly RequestBuilder $request,
        private readonly Client $client

    ) {
        $this->basePath = 'private';
    }

    /**
     * @throws GuzzleException
     * @throws BitfinexPathNotFoundException
     */
    final public function get(): BitfinexResponse
    {
        $request = (new BitfinexRequest($this->request, $this->credentials, $this->client));

        $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.wallets")->getPath());

        return $response->wallets();
    }
}
