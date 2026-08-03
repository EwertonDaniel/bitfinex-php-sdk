<?php

namespace EwertonDaniel\Bitfinex\Services\Authenticated;

use EwertonDaniel\Bitfinex\Builders\RequestBuilder;
use EwertonDaniel\Bitfinex\Builders\UrlBuilder;
use EwertonDaniel\Bitfinex\Enums\BitfinexType;
use EwertonDaniel\Bitfinex\Exceptions\BitfinexException;
use EwertonDaniel\Bitfinex\Helpers\DecimalToString;
use EwertonDaniel\Bitfinex\Helpers\GetThis;
use EwertonDaniel\Bitfinex\Http\Requests\BitfinexRequest;
use EwertonDaniel\Bitfinex\Http\Responses\AuthenticatedBitfinexResponse;
use EwertonDaniel\Bitfinex\ValueObjects\BitfinexCredentials;
use GuzzleHttp\Client;

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
        $this->request->reset();

        $symbol = BitfinexType::FUNDING->symbol($currency);
        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);
        $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.active_funding_offers", ['symbol' => $symbol])->getPath());

        return $response->fundingOffers();
    }

    final public function submitOffer(string $currency, float|string $amount, float|string $rate, int $period, array $options = []): AuthenticatedBitfinexResponse
    {
        $this->request->reset();

        $symbol = BitfinexType::FUNDING->symbol($currency);
        $body = array_merge([
            'type' => 'LIMIT',
            'symbol' => $symbol,
            'amount' => DecimalToString::convert($amount),
            'rate' => DecimalToString::convert($rate),
            'period' => $period,
        ], $options);

        $this->request->setBody($body);
        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);
        $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.submit_funding_offer")->getPath());

        return $response->fundingOfferSubmitted();
    }

    final public function cancelOffer(int $id): AuthenticatedBitfinexResponse
    {
        $this->request->reset();

        $this->request->setBody(['id' => $id]);
        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);
        $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.cancel_funding_offer")->getPath());

        return $response->cancelFundingOffer();
    }

    /**
     * Cancels all open funding offers.
     *
     * @param  string|null  $currency  Plain currency code (e.g. USD). When null, offers of every currency are cancelled.
     *
     * @link https://docs.bitfinex.com/reference/rest-auth-cancel-all-funding-offers
     */
    final public function cancelAllOffers(?string $currency = null): AuthenticatedBitfinexResponse
    {
        $this->request->reset();

        $this->request->addBody('currency', GetThis::ifTrueOrFallback($currency, fn () => strtoupper($currency)), true);
        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);
        $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.cancel_all_funding_offers")->getPath());

        return $response->cancelAllFundingOffers();
    }

    final public function close(int $id): AuthenticatedBitfinexResponse
    {
        $this->request->reset();

        $this->request->setBody(['id' => $id]);
        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);
        $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.funding_close")->getPath());

        return $response->fundingClose();
    }

    /**
     * Toggles auto-renew for a funding currency.
     *
     * @param  string  $currency  Plain currency code (e.g. USD).
     * @param  bool  $status  True activates auto-renew, false deactivates it.
     * @param  float|string|null  $amount  Amount to be auto-renewed (null means everything available).
     * @param  float|string|null  $rate  Percentage rate at which to auto-renew ('0' for FRR).
     * @param  int|null  $period  Period in days.
     *
     * @link https://docs.bitfinex.com/reference/rest-auth-funding-auto-renew
     */
    final public function autoRenew(
        string $currency,
        bool $status,
        float|string|null $amount = null,
        float|string|null $rate = null,
        ?int $period = null
    ): AuthenticatedBitfinexResponse {
        $this->request->reset();

        $this->request->setBody(['status' => (int) $status, 'currency' => strtoupper($currency)]);

        $optionalParams = [
            'amount' => DecimalToString::convert($amount),
            'rate' => DecimalToString::convert($rate),
            'period' => $period,
        ];

        foreach ($optionalParams as $key => $value) {
            $this->request->addBody($key, $value, true);
        }

        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);
        $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.funding_auto_renew")->getPath());

        return $response->fundingAutoRenew();
    }

    /**
     * Keeps funding taken (prevents it from being returned when the position closes).
     *
     * @param  string  $type  Either 'credit' or 'loan'.
     * @param  array<int>  $ids  Funding credit or loan IDs.
     *
     * @throws BitfinexException When an unsupported type is given.
     *
     * @link https://docs.bitfinex.com/reference/rest-auth-keep-funding
     */
    final public function keep(string $type, array $ids): AuthenticatedBitfinexResponse
    {
        $this->request->reset();

        $type = strtolower($type);

        if (! in_array($type, ['credit', 'loan'], true)) {
            throw new BitfinexException("Invalid funding type: $type. Use 'credit' or 'loan'.");
        }

        $this->request->setBody(['type' => $type, 'id' => array_values(array_map('intval', $ids))]);
        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);
        $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.keep_funding")->getPath());

        return $response->keepFunding();
    }

    final public function offersHistory(string $currency, ?int $start = null, ?int $end = null, ?int $limit = null, ?int $sort = null): AuthenticatedBitfinexResponse
    {
        $this->request->reset();

        $symbol = BitfinexType::FUNDING->symbol($currency);
        $params = compact('start', 'end', 'limit', 'sort');
        array_walk($params, fn ($v, $k) => $this->request->addBody($k, $v, true));
        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);
        $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.funding_offers_history", ['symbol' => $symbol])->getPath());

        return $response->fundingOffers();
    }

    final public function loans(string $currency): AuthenticatedBitfinexResponse
    {
        $this->request->reset();

        $symbol = BitfinexType::FUNDING->symbol($currency);
        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);
        $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.funding_loans", ['symbol' => $symbol])->getPath());

        return $response->fundingLoans();
    }

    final public function loansHistory(string $currency, ?int $start = null, ?int $end = null, ?int $limit = null, ?int $sort = null): AuthenticatedBitfinexResponse
    {
        $this->request->reset();

        $symbol = BitfinexType::FUNDING->symbol($currency);
        $params = compact('start', 'end', 'limit', 'sort');
        array_walk($params, fn ($v, $k) => $this->request->addBody($k, $v, true));
        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);
        $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.funding_loans_history", ['symbol' => $symbol])->getPath());

        return $response->fundingLoans();
    }

    final public function credits(string $currency): AuthenticatedBitfinexResponse
    {
        $this->request->reset();

        $symbol = BitfinexType::FUNDING->symbol($currency);
        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);
        $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.funding_credits", ['symbol' => $symbol])->getPath());

        return $response->fundingCredits();
    }

    final public function creditsHistory(string $currency, ?int $start = null, ?int $end = null, ?int $limit = null, ?int $sort = null): AuthenticatedBitfinexResponse
    {
        $this->request->reset();

        $symbol = BitfinexType::FUNDING->symbol($currency);
        $params = compact('start', 'end', 'limit', 'sort');
        array_walk($params, fn ($v, $k) => $this->request->addBody($k, $v, true));
        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);
        $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.funding_credits_history", ['symbol' => $symbol])->getPath());

        return $response->fundingCredits();
    }

    final public function trades(string $currency, ?int $start = null, ?int $end = null, ?int $limit = null, ?int $sort = null): AuthenticatedBitfinexResponse
    {
        $this->request->reset();

        $symbol = BitfinexType::FUNDING->symbol($currency);
        $params = compact('start', 'end', 'limit', 'sort');
        array_walk($params, fn ($v, $k) => $this->request->addBody($k, $v, true));
        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);
        $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.funding_trades", ['symbol' => $symbol])->getPath());

        return $response->fundingTrades();
    }

    final public function info(string $key): AuthenticatedBitfinexResponse
    {
        $this->request->reset();

        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);
        $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.funding_info", ['key' => $key])->getPath());

        return $response->fundingInfo();
    }
}
