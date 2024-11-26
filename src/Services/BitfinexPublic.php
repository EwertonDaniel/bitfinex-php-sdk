<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Services;

use EwertonDaniel\Bitfinex\Builders\UrlBuilder;
use EwertonDaniel\Bitfinex\Enums\BitfinexType;
use EwertonDaniel\Bitfinex\Enums\BookPrecision;
use EwertonDaniel\Bitfinex\Exceptions\BitfinexException;
use EwertonDaniel\Bitfinex\Exceptions\BitfinexPathNotFoundException;
use EwertonDaniel\Bitfinex\Helpers\GetThis;
use EwertonDaniel\Bitfinex\Http\Responses\PublicBitfinexResponse;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Class BitfinexPublic
 *
 * Provides a comprehensive interface for accessing Bitfinex public API endpoints.
 * This class handles operations such as retrieving platform status, market data,
 * historical ticker data, trades, order book details, and more.
 *
 * @author  Ewerton Daniel
 * @contact contact@ewertondaniel.work
 */
class BitfinexPublic
{
    private readonly Client $client;

    public function __construct(private readonly UrlBuilder $url)
    {
        $this->client = new Client(config: ['base_uri' => $this->url->getBaseUrl(), 'timeout' => 3.0]);
    }

    /**
     * Retrieves the current operational status of the Bitfinex platform.
     *
     *
     * @throws BitfinexException If the API request fails or an unexpected error occurs.
     */
    final public function platformStatus(): PublicBitfinexResponse
    {
        try {
            $apiPath = $this->url->setPath('public.status')->getPath();
            $apiResponse = $this->client->get($apiPath);

            return (new PublicBitfinexResponse($apiResponse))->platformStatus();
        } catch (GuzzleException $e) {
            throw new BitfinexException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Retrieves the latest state of multiple markets (tickers) based on provided symbols.
     *
     * @param array $pairs
     * @param BitfinexType $type The type of market (e.g., trading or funding).
     *
     * @return PublicBitfinexResponse
     * @throws BitfinexException If the API request fails or an unexpected error occurs.
     * @throws BitfinexPathNotFoundException
     */
    final public function tickers(array $pairs, BitfinexType $type): PublicBitfinexResponse
    {
        try {
            $apiPath = $this->url->setPath(path: 'public.tickers')->getPath();

            $options = [
                'query' => [
                    'symbols' => implode(',', array_map([$type, 'symbol'], $pairs)),
                ],
            ];

            $apiResponse = $this->client->get($apiPath, $options);

            return (new PublicBitfinexResponse($apiResponse))->tickers($type);
        } catch (GuzzleException $e) {
            throw new BitfinexException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Retrieves market data for a specific trading pair or funding currency.
     *
     * @param  string  $pair  The trading pair (e.g., tBTCUSD) or funding currency (e.g., fUSD).
     * @param  BitfinexType|string  $type  The type of market (trading or funding).
     *
     * @throws BitfinexException If the API request fails or an unexpected error occurs.
     */
    final public function ticker(string $pair, BitfinexType|string $type): PublicBitfinexResponse
    {
        try {
            $symbol = $type->symbol($pair);

            $bitfinexType = GetThis::ifTrueOrFallback(is_string($type), fn () => BitfinexType::from($type), $type);

            $apiPath = $this->url->setPath(path: 'public.ticker', params: ['symbol' => $symbol])->getPath();

            $apiResponse = $this->client->get($apiPath);

            return (new PublicBitfinexResponse($apiResponse))->ticker($symbol, $bitfinexType);
        } catch (GuzzleException $e) {
            throw new BitfinexException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Retrieves historical ticker data for a list of trading pairs.
     *
     * @param  array  $pairs  List of trading pairs.
     * @param  int  $limit  Maximum number of records to fetch (default: 100).
     * @param  string|null  $start  Start timestamp in milliseconds.
     * @param  string|null  $end  End timestamp in milliseconds.
     *
     * @throws BitfinexException If the API request fails or an unexpected error occurs.
     */
    final public function tickerHistory(array $pairs, int $limit = 100, ?string $start = null, ?string $end = null): PublicBitfinexResponse
    {
        try {
            $apiPath = $this->url->setPath(path: 'public.ticker_history')->getPath();

            $apiResponse = $this->client->get($apiPath, [
                'query' => [
                    'symbols' => implode(',', array_map(fn ($pair) => BitfinexType::TRADING->symbol($pair), $pairs)),
                    'limit' => $limit,
                    'start' => $start,
                    'end' => $end,
                ],
            ]);

            return (new PublicBitfinexResponse($apiResponse))->tickerHistory();
        } catch (GuzzleException $e) {
            throw new BitfinexException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Retrieves historical trade data for a given symbol.
     *
     * @throws BitfinexException
     */
    final public function trades(
        string $pair,
        string|BitfinexType $type,
        int $limit = 125,
        int $sort = -1,
        ?int $start = null,
        ?int $end = null
    ): PublicBitfinexResponse {
        try {
            $bitfinexType = GetThis::ifTrueOrFallback(is_string($type), fn () => BitfinexType::from($type), $type);
            $symbol = $bitfinexType->symbol($pair);

            $apiPath = $this->url->setPath(path: 'public.trades', params: ['symbol' => $symbol])->getPath();

            $apiResponse = $this->client->get($apiPath, ['query' => ['limit' => $limit, 'sort' => $sort, 'start' => $start, 'end' => $end]]);

            return (new PublicBitfinexResponse($apiResponse))->trades($symbol, $bitfinexType);
        } catch (GuzzleException $e) {
            throw new BitfinexException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Retrieves the order book data for a specified trading pair or funding currency.
     *
     * This method fetches real-time order book data for a given symbol and precision level.
     * It allows specifying the maximum number of price levels to retrieve.
     *
     * @param  string  $pair  The trading pair (e.g., tBTCUSD) or funding currency (e.g., fUSD).
     * @param  BitfinexType  $type  The type of market (trading or funding).
     * @param  BookPrecision  $precision  The precision level for the order book data (e.g., P0, P1).
     * @param  int  $length  The maximum number of price levels to retrieve (default: 25).
     * @return PublicBitfinexResponse Returns a response object containing the requested order book data.
     *
     * @throws BitfinexException If the API request fails or an unexpected error occurs.
     */
    final public function book(string $pair, BitfinexType $type, BookPrecision $precision, int $length = 25): PublicBitfinexResponse
    {
        try {
            $symbol = $type->symbol($pair);

            $apiPath = $this->url->setPath(path: 'public.book', params: ['symbol' => $symbol, 'precision' => $precision->name])->getPath();

            $apiResponse = $this->client->get($apiPath, ['query' => ['len' => $length]]);

            return (new PublicBitfinexResponse($apiResponse))->book($symbol, $type);
        } catch (GuzzleException $e) {
            throw new BitfinexException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Retrieves statistics from the platform's operational data based on specified parameters.
     *
     * This method fetches statistics from the Bitfinex API by constructing the appropriate URL
     * with the provided key, size, symPlatform, sidePair, and section. It also allows additional
     * query parameters such as sorting and filtering by start time.
     *
     * @param  string  $key  The type of statistic to retrieve (e.g., "pos.size", "funding.size").
     * @param  string  $size  The interval or granularity of the data (e.g., "1m", "30m", "1d").
     * @param  string  $sidePair  The side of the data (e.g., "long", "short"), or a pair for credits.
     * @param  string  $section  Specifies whether to fetch the "last" or "hist" data section.
     * @param  string|null  $pair  The trading pair or platform to query (e.g., "tBTCUSD").
     * @param  string|null  $currency  The trading currency or platform to query (e.g."fUSD").
     * @param  ?int  $sort  [Optional] Sort order: +1 for ascending, -1 for descending.
     * @param  ?int  $start  [Optional] Records with MTS >= start (milliseconds).
     * @param  ?int  $end  [Optional] Records with MTS <= end (milliseconds).
     * @param  ?int  $limit  [Optional] Number of records (max 10000).
     * @return PublicBitfinexResponse Returns a response object containing the requested stats.
     *
     * @throws BitfinexException If the API request fails or an error occurs during processing.
     */
    final public function stats(
        BitfinexType $type,
        string $key,
        string $size,
        string $sidePair,
        string $section,
        ?string $pair = null,
        ?string $currency = null,
        ?int $sort = null,
        ?int $start = null,
        ?int $end = null,
        ?int $limit = null
    ): PublicBitfinexResponse {
        try {
            $symPlatform = GetThis::ifTrueOrFallback($pair, fn () => $type->symbol($pair), fn () => $type->symbol($currency));

            $apiPath = $this->url->setPath('public.stats_one', [
                'key' => $key,
                'size' => $size,
                'sym_platform' => $symPlatform,
                'side_pair' => $sidePair,
                'section' => $section,
            ])->getPath();

            $apiResponse = $this->client->get($apiPath, [
                'query' => array_filter([
                    'sort' => $sort,
                    'start' => $start,
                    'end' => $end,
                    'limit' => $limit,
                ], fn ($value) => ! is_null($value)),
            ]);

            return (new PublicBitfinexResponse($apiResponse))
                ->stats($key, $size, $symPlatform, $sidePair, $section);
        } catch (GuzzleException $e) {
            throw new BitfinexException($e->getMessage(), $e->getCode());
        }
    }

    /** @throws BitfinexException
     * @todo Implement method for GET Candles
     */
    final public function candles(): PublicBitfinexResponse
    {
        throw new BitfinexException('Method not implemented.');
    }

    /** @throws BitfinexException
     * @todo Implement method for GET Configs
     */
    final public function configs(): PublicBitfinexResponse
    {
        throw new BitfinexException('Method not implemented.');
    }

    /** @throws BitfinexException
     * @todo Implement method for GET Derivatives Status
     */
    final public function derivativesStatus(): PublicBitfinexResponse
    {
        throw new BitfinexException('Method not implemented.');
    }

    /** @throws BitfinexException
     * @todo Implement method for GET Derivatives Status History
     */
    final public function derivativesStatusHistory(): PublicBitfinexResponse
    {
        throw new BitfinexException('Method not implemented.');
    }

    /** @throws BitfinexException
     * @todo Implement method for GET Liquidations
     */
    final public function liquidations(): PublicBitfinexResponse
    {
        throw new BitfinexException('Method not implemented.');
    }

    /** @throws BitfinexException
     * @todo Implement method for GET Leaderboards
     */
    final public function leaderboards(): PublicBitfinexResponse
    {
        throw new BitfinexException('Method not implemented.');
    }

    /** @throws BitfinexException
     * @todo Implement method for GET Funding Stats
     */
    final public function fundingStats(): PublicBitfinexResponse
    {
        throw new BitfinexException('Method not implemented.');
    }

    // ** Calculation Endpoints **

    /**
     * Retrieves the exchange rate between two specified currencies.
     *
     * This method sends a request to the Bitfinex public API to obtain the
     * exchange rate for a given base and quote currency pair.
     *
     * @param  string  $baseCurrency  The base currency (e.g., USD, EUR).
     * @param  string  $quoteCurrency  The quote currency (e.g., BTC, ETH).
     * @return PublicBitfinexResponse Returns a response object containing the exchange rate data.
     *
     * @throws BitfinexException If the API request fails or an unexpected error occurs.
     */
    final public function foreignExchangeRate(string $baseCurrency, string $quoteCurrency): PublicBitfinexResponse
    {
        try {
            $apiPath = $this->url->setPath(path: 'public.foreign_exchange_rate')->getPath();

            $apiResponse = $this->client->post($apiPath, ['json' => ['ccy1' => $baseCurrency, 'ccy2' => $quoteCurrency]]);

            return (new PublicBitfinexResponse($apiResponse))->foreignExchangeRate($baseCurrency, $quoteCurrency);
        } catch (GuzzleException $e) {
            throw new BitfinexException($e->getMessage(), $e->getCode());
        }
    }

    /** @throws BitfinexException
     * @todo Implement method for POST Market Average Price
     */
    final public function marketAveragePrice(): PublicBitfinexResponse
    {
        throw new BitfinexException('Method not implemented.');
    }
}
