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

class BitfinexAuthenticatedFunding
{
    private readonly string $basePath;

    public function __construct(
        private readonly UrlBuilder $url,
        private readonly BitfinexCredentials $credentials,
        private readonly RequestBuilder $request,
        private readonly Client $client
    ) {
        $this->basePath = 'private.funding';
    }

    final public function activeOffers(string $currency): AuthenticatedBitfinexResponse
    {
        $symbol = BitfinexType::FUNDING->symbol($currency);
        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);
        $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.active_funding_offers", ['symbol' => $symbol])->getPath());

        return $response->fundingOffers();
    }

    final public function submitOffer(string $currency, float $amount, float $rate, int $period, array $options = []): AuthenticatedBitfinexResponse
    {
        $symbol = BitfinexType::FUNDING->symbol($currency);
        $body = array_merge([
            'type' => 'LIMIT',
            'symbol' => $symbol,
            'amount' => (string) $amount,
            'rate' => (string) $rate,
            'period' => $period,
        ], $options);

        $this->request->setBody($body);
        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);
        $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.submit_funding_offer")->getPath());

        return $response->fundingOfferSubmitted();
    }

    final public function cancelOffer(int $id): AuthenticatedBitfinexResponse
    {
        $this->request->setBody(['id' => $id]);
        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);
        $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.cancel_funding_offer")->getPath());

        return $response->cancelFundingOffer();
    }

    final public function cancelAllOffers(string $currency): AuthenticatedBitfinexResponse
    {
        $symbol = BitfinexType::FUNDING->symbol($currency);
        $this->request->setBody(['symbol' => $symbol]);
        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);
        $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.cancel_all_funding_offers")->getPath());

        return $response->cancelAllFundingOffers();
    }

    final public function close(int $id): AuthenticatedBitfinexResponse
    {
        $this->request->setBody(['id' => $id]);
        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);
        $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.funding_close")->getPath());

        return $response->fundingClose();
    }

    final public function autoRenew(int $id, bool $enabled): AuthenticatedBitfinexResponse
    {
        $this->request->setBody(['id' => $id, 'enabled' => (int) $enabled]);
        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);
        $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.funding_auto_renew")->getPath());

        return $response->fundingAutoRenew();
    }

    final public function keep(int $id): AuthenticatedBitfinexResponse
    {
        $this->request->setBody(['id' => $id]);
        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);
        $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.keep_funding")->getPath());

        return $response->keepFunding();
    }

    final public function offersHistory(string $currency, ?int $start = null, ?int $end = null, ?int $limit = null, ?int $sort = null): AuthenticatedBitfinexResponse
    {
        $symbol = BitfinexType::FUNDING->symbol($currency);
        array_walk(compact('start', 'end', 'limit', 'sort'), fn ($v, $k) => $this->request->addBody($k, $v, true));
        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);
        $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.funding_offers_history", ['symbol' => $symbol])->getPath());

        return $response->fundingOffers();
    }

    final public function loans(string $currency): AuthenticatedBitfinexResponse
    {
        $symbol = BitfinexType::FUNDING->symbol($currency);
        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);
        $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.funding_loans", ['symbol' => $symbol])->getPath());

        return $response->fundingLoans();
    }

    final public function loansHistory(string $currency, ?int $start = null, ?int $end = null, ?int $limit = null, ?int $sort = null): AuthenticatedBitfinexResponse
    {
        $symbol = BitfinexType::FUNDING->symbol($currency);
        array_walk(compact('start', 'end', 'limit', 'sort'), fn ($v, $k) => $this->request->addBody($k, $v, true));
        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);
        $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.funding_loans_history", ['symbol' => $symbol])->getPath());

        return $response->fundingLoans();
    }

    final public function credits(string $currency): AuthenticatedBitfinexResponse
    {
        $symbol = BitfinexType::FUNDING->symbol($currency);
        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);
        $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.funding_credits", ['symbol' => $symbol])->getPath());

        return $response->fundingCredits();
    }

    final public function creditsHistory(string $currency, ?int $start = null, ?int $end = null, ?int $limit = null, ?int $sort = null): AuthenticatedBitfinexResponse
    {
        $symbol = BitfinexType::FUNDING->symbol($currency);
        array_walk(compact('start', 'end', 'limit', 'sort'), fn ($v, $k) => $this->request->addBody($k, $v, true));
        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);
        $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.funding_credits_history", ['symbol' => $symbol])->getPath());

        return $response->fundingCredits();
    }

    final public function trades(string $currency, ?int $start = null, ?int $end = null, ?int $limit = null, ?int $sort = null): AuthenticatedBitfinexResponse
    {
        $symbol = BitfinexType::FUNDING->symbol($currency);
        array_walk(compact('start', 'end', 'limit', 'sort'), fn ($v, $k) => $this->request->addBody($k, $v, true));
        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);
        $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.funding_trades", ['symbol' => $symbol])->getPath());

        return $response->fundingTrades();
    }

    final public function info(string $key): AuthenticatedBitfinexResponse
    {
        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);
        $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.funding_info", ['key' => $key])->getPath());

        return $response->fundingInfo();
    }
}

