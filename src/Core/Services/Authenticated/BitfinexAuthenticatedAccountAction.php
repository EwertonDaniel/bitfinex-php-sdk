<?php

namespace EwertonDaniel\Bitfinex\Core\Services\Authenticated;

use EwertonDaniel\Bitfinex\Core\Builders\RequestBuilder;
use EwertonDaniel\Bitfinex\Core\Builders\UrlBuilder;
use EwertonDaniel\Bitfinex\Core\ValueObjects\BitfinexCredentials;
use EwertonDaniel\Bitfinex\Exceptions\BitfinexPathNotFoundException;
use EwertonDaniel\Bitfinex\Http\Requests\BitfinexRequest;
use EwertonDaniel\Bitfinex\Http\Responses\BitfinexResponse;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class BitfinexAuthenticatedAccountAction
{
    private readonly string $basePath;

    public function __construct(
        private readonly UrlBuilder $url,
        private readonly BitfinexCredentials $credentials,
        private readonly RequestBuilder $request,
        private readonly Client $client

    ) {
        $this->basePath = 'private.account_actions';
    }

    /**
     * @throws GuzzleException
     * @throws BitfinexPathNotFoundException
     */
    final public function userInfo(): BitfinexResponse
    {
        $request = (new BitfinexRequest($this->request, $this->credentials, $this->client));

        $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.user_info")->getPath());

        return $response->userInfo();
    }

        /**
     * @throws GuzzleException
     * @throws BitfinexPathNotFoundException
     */
    final public function keyPermissions(): BitfinexResponse
    {
        $request = (new BitfinexRequest($this->request, $this->credentials, $this->client));

        $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.key_permissions")->getPath());

        return $response->keyPermissions();
    }
    /**
     * @throws GuzzleException
     * @throws BitfinexPathNotFoundException
     */
    final public function loginHistory(): BitfinexResponse
    {
        $request = (new BitfinexRequest($this->request, $this->credentials, $this->client));

        $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.login_history")->getPath());

        return $response->loginHistory();
    }

    /**
     * @throws GuzzleException
     * @throws BitfinexPathNotFoundException
     */
    final public function summary(): BitfinexResponse
    {
        $request = (new BitfinexRequest($this->request, $this->credentials, $this->client));

        $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.summary")->getPath());

        return $response->summary();
    }
}
