<?php

namespace EwertonDaniel\Bitfinex\Services\Authenticated;

use EwertonDaniel\Bitfinex\Builders\RequestBuilder;
use EwertonDaniel\Bitfinex\Builders\UrlBuilder;
use EwertonDaniel\Bitfinex\Enums\BitfinexType;
use EwertonDaniel\Bitfinex\Exceptions\BitfinexPathNotFoundException;
use EwertonDaniel\Bitfinex\Http\Requests\BitfinexRequest;
use EwertonDaniel\Bitfinex\Http\Responses\AuthenticatedBitfinexResponse;
use EwertonDaniel\Bitfinex\ValueObjects\BitfinexCredentials;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class BitfinexAuthenticatedPositions
{
    private readonly string $basePath;

    public function __construct(
        private readonly UrlBuilder $url,
        private readonly BitfinexCredentials $credentials,
        private readonly RequestBuilder $request,
        private readonly Client $client
    ) {
        $this->basePath = 'private.positions';
    }

    /**
     * Margin info for a given key (e.g., 'base' or symbol specific).
     * @throws BitfinexPathNotFoundException|GuzzleException
     */
    final public function marginInfo(string $key): AuthenticatedBitfinexResponse
    {
        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);
        $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.margin_info", ['key' => $key])->getPath());

        return $response->marginInfo();
    }

    /**
     * Retrieve open positions
     * @throws BitfinexPathNotFoundException|GuzzleException
     */
    final public function retrieve(): AuthenticatedBitfinexResponse
    {
        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);
        $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.retrieve_positions")->getPath());

        return $response->positions();
    }

    /**
     * Claim a position (symbol as trading pair like 'BTCUSD').
     * @throws BitfinexPathNotFoundException|GuzzleException
     */
    final public function claim(string $pair, float $amount): AuthenticatedBitfinexResponse
    {
        $symbol = BitfinexType::TRADING->symbol($pair);
        $this->request->setBody(['symbol' => $symbol, 'amount' => (string) $amount]);
        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);
        $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.claim_position")->getPath());

        return $response->positionsClaim();
    }

    /**
     * Increase a position size
     * @throws BitfinexPathNotFoundException|GuzzleException
     */
    final public function increase(string $pair, float $amount, ?float $price = null): AuthenticatedBitfinexResponse
    {
        $symbol = BitfinexType::TRADING->symbol($pair);
        $this->request->setBody(['symbol' => $symbol, 'amount' => (string) $amount]);
        $this->request->addBody('price', $price, true);
        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);
        $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.increase_position")->getPath());

        return $response->positionsIncrease();
    }

    /**
     * Increase position info (optional symbol/amount details)
     * @throws BitfinexPathNotFoundException|GuzzleException
     */
    final public function increaseInfo(?string $pair = null, ?float $amount = null): AuthenticatedBitfinexResponse
    {
        if ($pair) {
            $this->request->addBody('symbol', BitfinexType::TRADING->symbol($pair), true);
        }
        $this->request->addBody('amount', $amount, true);
        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);
        $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.increase_position_info")->getPath());

        return $response->positionIncreaseInfo();
    }

    /**
     * Positions history
     * @throws BitfinexPathNotFoundException|GuzzleException
     */
    final public function history(?int $start = null, ?int $end = null, ?int $limit = null, ?int $sort = null): AuthenticatedBitfinexResponse
    {
        array_walk(compact('start', 'end', 'limit', 'sort'), fn ($v, $k) => $this->request->addBody($k, $v, true));
        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);
        $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.positions_history")->getPath());

        return $response->positionsHistory();
    }

    /**
     * Positions snapshot
     * @throws BitfinexPathNotFoundException|GuzzleException
     */
    final public function snapshot(): AuthenticatedBitfinexResponse
    {
        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);
        $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.positions_snapshot")->getPath());

        return $response->positionsSnapshot();
    }

    /**
     * Positions audit
     * @throws BitfinexPathNotFoundException|GuzzleException
     */
    final public function audit(): AuthenticatedBitfinexResponse
    {
        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);
        $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.positions_audit")->getPath());

        return $response->positionsAudit();
    }

    /**
     * Set derivative position collateral
     * @throws BitfinexPathNotFoundException|GuzzleException
     */
    final public function setDerivativeCollateral(string $pair, float $collateral): AuthenticatedBitfinexResponse
    {
        $symbol = BitfinexType::TRADING->symbol($pair);
        $this->request->setBody(['symbol' => $symbol, 'collateral' => (string) $collateral]);
        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);
        $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.derivative_position_collateral")->getPath());

        return $response->derivativePositionCollateral();
    }

    /**
     * Derivative position collateral limits (calc)
     * @throws BitfinexPathNotFoundException|GuzzleException
     */
    final public function derivativeCollateralLimits(?string $pair = null): AuthenticatedBitfinexResponse
    {
        if ($pair) {
            $this->request->addBody('symbol', BitfinexType::TRADING->symbol($pair), true);
        }
        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);
        $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.derivative_position_collateral_limits")->getPath());

        return $response->derivativePositionCollateralLimits();
    }
}

