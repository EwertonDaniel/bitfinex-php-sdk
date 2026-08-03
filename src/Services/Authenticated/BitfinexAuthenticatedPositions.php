<?php

namespace EwertonDaniel\Bitfinex\Services\Authenticated;

use EwertonDaniel\Bitfinex\Builders\RequestBuilder;
use EwertonDaniel\Bitfinex\Builders\UrlBuilder;
use EwertonDaniel\Bitfinex\Enums\BitfinexType;
use EwertonDaniel\Bitfinex\Exceptions\BitfinexPathNotFoundException;
use EwertonDaniel\Bitfinex\Helpers\DecimalToString;
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
     *
     * @throws BitfinexPathNotFoundException|GuzzleException
     */
    final public function marginInfo(string $key): AuthenticatedBitfinexResponse
    {
        $this->request->reset();

        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);
        $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.margin_info", ['key' => $key])->getPath());

        return $response->marginInfo();
    }

    /**
     * Retrieve open positions
     *
     * @throws BitfinexPathNotFoundException|GuzzleException
     */
    final public function retrieve(): AuthenticatedBitfinexResponse
    {
        $this->request->reset();

        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);
        $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.retrieve_positions")->getPath());

        return $response->positions();
    }

    /**
     * Claim a position.
     *
     * @param  int  $id  Position ID, as returned by retrieve().
     * @param  float|string|null  $amount  Partial amount to claim; null claims the whole position.
     *
     * @throws BitfinexPathNotFoundException|GuzzleException
     *
     * @link https://docs.bitfinex.com/reference/rest-auth-position-claim
     */
    final public function claim(int $id, float|string|null $amount = null): AuthenticatedBitfinexResponse
    {
        $this->request->reset();

        $this->request->setBody(['id' => $id]);
        $this->request->addBody('amount', DecimalToString::convert($amount), true);
        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);
        $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.claim_position")->getPath());

        return $response->positionsClaim();
    }

    /**
     * Increase a position size.
     *
     * The endpoint accepts exactly `symbol` and `amount`. The `price` parameter
     * that used to sit here was sending a field the API does not define, which
     * could read as though the increase were priceable when it is not.
     *
     * @param  string  $pair  Trading pair (e.g., BTCUSD).
     * @param  float|string  $amount  Amount to add to the position.
     *
     * @throws BitfinexPathNotFoundException|GuzzleException
     *
     * @link https://docs.bitfinex.com/reference/rest-auth-position-increase
     */
    final public function increase(string $pair, float|string $amount): AuthenticatedBitfinexResponse
    {
        $this->request->reset();

        $symbol = BitfinexType::TRADING->symbol($pair);
        $this->request->setBody(['symbol' => $symbol, 'amount' => DecimalToString::convert($amount)]);
        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);
        $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.increase_position")->getPath());

        return $response->positionsIncrease();
    }

    /**
     * Increase position info (optional symbol/amount details)
     *
     * @throws BitfinexPathNotFoundException|GuzzleException
     */
    final public function increaseInfo(?string $pair = null, float|string|null $amount = null): AuthenticatedBitfinexResponse
    {
        $this->request->reset();

        if ($pair) {
            $this->request->addBody('symbol', BitfinexType::TRADING->symbol($pair), true);
        }
        $this->request->addBody('amount', DecimalToString::convert($amount), true);
        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);
        $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.increase_position_info")->getPath());

        return $response->positionIncreaseInfo();
    }

    /**
     * Positions history.
     *
     * The endpoint takes `start`, `end`, `limit` and `id`; it has no `sort`
     * field, so the parameter that used to sit here had no effect.
     *
     * @param  int|null  $start  Records with MTS >= start (ms).
     * @param  int|null  $end  Records with MTS <= end (ms).
     * @param  int|null  $limit  Maximum number of records (max 50).
     * @param  int|null  $id  Restrict the result to a single position.
     *
     * @throws BitfinexPathNotFoundException|GuzzleException
     *
     * @link https://docs.bitfinex.com/reference/rest-auth-positions-hist
     */
    final public function history(?int $start = null, ?int $end = null, ?int $limit = null, ?int $id = null): AuthenticatedBitfinexResponse
    {
        $this->request->reset();

        $params = compact('start', 'end', 'limit', 'id');
        array_walk($params, fn ($v, $k) => $this->request->addBody($k, $v, true));
        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);
        $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.positions_history")->getPath());

        return $response->positionsHistory();
    }

    /**
     * Positions snapshot
     *
     * @throws BitfinexPathNotFoundException|GuzzleException
     */
    final public function snapshot(): AuthenticatedBitfinexResponse
    {
        $this->request->reset();

        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);
        $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.positions_snapshot")->getPath());

        return $response->positionsSnapshot();
    }

    /**
     * Positions audit
     *
     * @throws BitfinexPathNotFoundException|GuzzleException
     */
    final public function audit(): AuthenticatedBitfinexResponse
    {
        $this->request->reset();

        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);
        $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.positions_audit")->getPath());

        return $response->positionsAudit();
    }

    /**
     * Set derivative position collateral
     *
     * @throws BitfinexPathNotFoundException|GuzzleException
     */
    final public function setDerivativeCollateral(string $pair, float|string $collateral): AuthenticatedBitfinexResponse
    {
        $this->request->reset();

        $symbol = BitfinexType::TRADING->symbol($pair);
        $this->request->setBody(['symbol' => $symbol, 'collateral' => DecimalToString::convert($collateral)]);
        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);
        $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.derivative_position_collateral")->getPath());

        return $response->derivativePositionCollateral();
    }

    /**
     * Derivative position collateral limits (calc)
     *
     * @throws BitfinexPathNotFoundException|GuzzleException
     */
    final public function derivativeCollateralLimits(?string $pair = null): AuthenticatedBitfinexResponse
    {
        $this->request->reset();

        if ($pair) {
            $this->request->addBody('symbol', BitfinexType::TRADING->symbol($pair), true);
        }
        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);
        $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.derivative_position_collateral_limits")->getPath());

        return $response->derivativePositionCollateralLimits();
    }
}
