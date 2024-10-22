<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Core\Services;

use EwertonDaniel\Bitfinex\Core\Builders\UrlBuilder;
use EwertonDaniel\Bitfinex\Exceptions\BitfinexException;
use EwertonDaniel\Bitfinex\Helpers\GetThis;
use EwertonDaniel\Bitfinex\Http\Responses\BitfinexResponse;
use EwertonDaniel\Bitfinex\Http\Responses\PublicBitfinexResponse;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class BitfinexPublic
{
    private readonly Client $client;

    public function __construct(private readonly UrlBuilder $url)
    {
        $this->client = new Client;
    }

    /**
     * Retrieves the current status of the Bitfinex platform, either "Operative" or "Maintenance".
     *
     * @throws BitfinexException
     *
     * @note This method checks the platform's operational status.
     */
    final public function platformStatus(): BitfinexResponse
    {
        try {

            $url = $this->url->setPath('public.status')->get();

            $apiResponse = $this->client->get($url);

            $response = new PublicBitfinexResponse($apiResponse);

            return $response->platformStatus();

        } catch (GuzzleException|Exception $e) {

            throw new BitfinexException($e->getMessage(), $e->getCode());
        }

    }

    /**
     * Provides a high-level overview of the state of the market for a specified pair.
     * It shows the current best bid and ask, the last traded price, daily volume,
     * and price movement over the last day.
     *
     * @throws BitfinexException
     *
     * @note This method interacts with the ticker endpoint of the Bitfinex API.
     */
    final public function ticker(string $symbol, string $type): BitfinexResponse
    {
        try {

            $params = ['symbol' => $symbol, 'type' => GetThis::type($type)];

            $url = $this->url->setPath(path: 'public.ticker', params: $params)->get();

            $response = new PublicBitfinexResponse($this->client->get($url));

            return $response->ticker($symbol, $params['type']);

        } catch (GuzzleException|Exception $e) {

            throw new BitfinexException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @throws BitfinexException
     *
     * @note The tickers endpoint provides a high level overview of the state of the market.
     * It shows the current best bid and ask, the last traded price, as well as information on the daily volume and price movement over the last day.
     * The endpoint can retrieve multiple tickers with a single query.
     */
    final public function tickers(array $symbols, string $type): BitfinexResponse
    {

        try {

            $type = GetThis::type($type);

            $symbols = implode(",$type", $symbols);

            $url = $this->url->setPath(path: 'public.tickers')->get();

            $options = ['query' => ['symbols' => "$type$symbols"]];

            $response = new PublicBitfinexResponse($this->client->get($url, $options));

            return $response->tickers($type);

        } catch (GuzzleException|Exception $e) {

            throw new BitfinexException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Retrieves the price of an asset (e.g., BTC, XMR, ETH) in USD using the Bitfinex ticker endpoint.
     * After obtaining the asset's price in USD, it calculates the cross-rate for another currency,
     * such as EUR, by converting the asset's USD price into the target currency (e.g., USD to EUR).
     * This is achieved by first retrieving the asset's USD price, followed by a secondary ticker request
     * for the USD to target currency pair.
     *
     * @throws BitfinexException
     *
     * @note This method involves two ticker requests: one for the asset's USD price and one for the USD to target currency conversion.
     */
    final public function crossRate($symbol, $currency): BitfinexResponse
    {
        try {
            $url['asset'] = $this->url->setPath(path: 'public.ticker', params: ['symbol' => $symbol, 'type' => 't'])->get();

            $response = new PublicBitfinexResponse($this->client->get($url['asset']));
            $crossSymbol = "USD$currency";

            $url['currency'] = $this->url->setPath(path: 'public.ticker', params: ['symbol' => $crossSymbol, 'type' => 't'])->get();
            $currencyResponse = new PublicBitfinexResponse($this->client->get($url['currency']));

            return $response->crossRateTicker($symbol, $currencyResponse->ticker($crossSymbol, 't')->contents);

        } catch (GuzzleException|Exception $e) {
            throw new BitfinexException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Retrieves the history of recent trading tickers, providing historical data of the best bid and ask prices at hourly intervals.
     * The historic data spans up to 1 year.
     *
     * @throws BitfinexException
     *
     * @note This method provides historical trading data at an hourly interval for the past year.
     */
    final public function tickerHistory(array $symbols, int $limit = 100, ?string $start = null, ?string $end = null): BitfinexResponse
    {
        try {

            $symbols = implode(',t', $symbols);

            $options = ['query' => ['symbols' => "t$symbols", 'limit' => $limit, 'start' => $start, 'end' => $end]];

            $response = new PublicBitfinexResponse(
                $this->client->get($this->url->setPath(path: 'public.ticker_history')->get(), $options)
            );

            return $response->tickerHistory();

        } catch (GuzzleException|Exception $e) {

            throw new BitfinexException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Retrieves past public trades, including details such as price, size, and time.
     * Optional parameters allow limiting the number of results, specifying a start and end timestamp,
     * setting a limit, and choosing a sorting method.
     *
     * @throws BitfinexException
     *
     * @note This method interacts with the trades endpoint to fetch historical trade data with customizable parameters.
     */
    final public function trades(string $symbol, string $type, int $limit = 125, int $sort = -1, ?int $start = null, ?int $end = null): BitfinexResponse
    {
        try {

            $type = GetThis::type($type);

            $url = $this->url->setPath(path: 'public.trades', params: ['symbol' => $symbol, 'type' => $type])->get();

            $options = ['query' => ['limit' => $limit, 'sort' => $sort, 'start' => $start, 'end' => $end]];

            $response = new PublicBitfinexResponse($this->client->get($url, $options));

            return $response->trades($symbol, $type);

        } catch (GuzzleException|Exception $e) {

            throw new BitfinexException($e->getMessage(), $e->getCode());
        }
    }
}
