<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Services;

use Carbon\Carbon;
use EwertonDaniel\Bitfinex\Builders\UrlBuilder;
use EwertonDaniel\Bitfinex\Enums\BookPrecision;
use EwertonDaniel\Bitfinex\Exceptions\BitfinexException;
use EwertonDaniel\Bitfinex\Http\Responses\PublicBitfinexResponse;
use EwertonDaniel\Bitfinex\Services\Public\BitfinexPublicBook;
use EwertonDaniel\Bitfinex\Services\Public\BitfinexPublicStats;
use EwertonDaniel\Bitfinex\Services\Public\BitfinexPublicTicker;
use EwertonDaniel\Bitfinex\Services\Public\BitfinexPublicTrade;
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
 *
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
     * This method checks whether the platform is operational or under maintenance.
     * It provides a quick way to verify the platform's availability.
     *
     * @return PublicBitfinexResponse Contains the status of the platform (e.g., operational or maintenance).
     *
     * @throws BitfinexException If the API request fails or an unexpected error occurs.
     *
     * @link https://docs.bitfinex.com/reference/rest-public-platform-status
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
     * Provides an instance of BitfinexPublicTicker.
     *
     * @return BitfinexPublicTicker The ticker service for accessing public ticker data.
     */
    final public function ticker(): BitfinexPublicTicker
    {
        return new BitfinexPublicTicker($this->client, $this->url);
    }

    /**
     * Provides an instance of BitfinexPublicTrade.
     *
     * @param  int  $limit  Maximum number of records to retrieve (default: 125).
     * @param  int  $sort  Sort order: +1 (ascending), -1 (descending).
     * @param  Carbon|string|null  $start  Start time for filtering (optional).
     * @param  Carbon|string|null  $end  End time for filtering (optional).
     * @return BitfinexPublicTrade The trade service for accessing public trade data.
     */
    public function trades(int $limit = 125, int $sort = -1, Carbon|string|null $start = null, Carbon|string|null $end = null): BitfinexPublicTrade
    {
        return new BitfinexPublicTrade($this->client, $this->url, $limit, $sort, $start, $end);
    }

    /**
     * Provides an instance of BitfinexPublicBook.
     *
     * @param  BookPrecision|null  $precision  The precision level for order book data (e.g., P0, P1, R0).
     * @param  int  $length  Maximum number of price levels to retrieve (default: 25).
     * @return BitfinexPublicBook The book service for accessing public order book data.
     */
    final public function book(?BookPrecision $precision = null, int $length = 25): BitfinexPublicBook
    {
        return new BitfinexPublicBook($this->client, $this->url, $precision, $length);
    }

    /**
     * Provides an instance of BitfinexPublicStats.
     *
     * @param  string  $key  The type of statistic to retrieve (e.g., 'pos.size').
     * @param  string  $size  The interval of the data (e.g., '1m', '1d').
     * @param  string  $sidePair  The side of the data (e.g., 'long', 'short'), or a pair for credits.
     * @param  string  $section  Specifies whether to fetch the 'last' or 'hist' data section.
     * @return BitfinexPublicStats The stats service for accessing public statistics data.
     */
    final public function stats(string $key, string $size, string $sidePair, string $section): BitfinexPublicStats
    {
        return new BitfinexPublicStats($this->client, $this->url, $key, $size, $sidePair, $section);
    }

    /** @throws BitfinexException
     *
     * @link https://docs.bitfinex.com/reference/rest-public-candles
     *
     * @todo Implement method for GET Candles
     */
    final public function candles(): PublicBitfinexResponse
    {
        throw new BitfinexException('Method not implemented.');
    }

    /** @throws BitfinexException
     *
     * @link https://docs.bitfinex.com/reference/rest-public-conf
     *
     * @todo Implement method for GET Configs
     */
    final public function configs(): PublicBitfinexResponse
    {
        throw new BitfinexException('Method not implemented.');
    }

    /** @throws BitfinexException
     *
     * @link https://docs.bitfinex.com/reference/rest-public-derivatives-status
     *
     * @todo Implement method for GET Derivatives Status
     */
    final public function derivativesStatus(): PublicBitfinexResponse
    {
        throw new BitfinexException('Method not implemented.');
    }

    /** @throws BitfinexException
     *
     * @link https://docs.bitfinex.com/reference/rest-public-derivatives-status-history
     *
     * @todo Implement method for GET Derivatives Status History
     */
    final public function derivativesStatusHistory(): PublicBitfinexResponse
    {
        throw new BitfinexException('Method not implemented.');
    }

    /** @throws BitfinexException
     *
     * @link https://docs.bitfinex.com/reference/rest-public-liquidations
     *
     * @todo Implement method for GET Liquidations
     */
    final public function liquidations(): PublicBitfinexResponse
    {
        throw new BitfinexException('Method not implemented.');
    }

    /** @throws BitfinexException
     *
     * @link https://docs.bitfinex.com/reference/rest-public-rankings
     *
     * @todo Implement method for GET Leaderboards
     */
    final public function leaderboards(): PublicBitfinexResponse
    {
        throw new BitfinexException('Method not implemented.');
    }

    /** @throws BitfinexException
     *
     * @link https://docs.bitfinex.com/reference/rest-public-funding-stats
     *
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
     *
     * @link https://docs.bitfinex.com/reference/rest-public-foreign-exchange-rate
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
     *
     * @link https://docs.bitfinex.com/reference/rest-public-market-average-price
     *
     * @todo Implement method for POST Market Average Price
     */
    final public function marketAveragePrice(): PublicBitfinexResponse
    {
        throw new BitfinexException('Method not implemented.');
    }
}
