<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Services;

use Carbon\Carbon;
use EwertonDaniel\Bitfinex\Builders\UrlBuilder;
use EwertonDaniel\Bitfinex\Enums\BookPrecision;
use EwertonDaniel\Bitfinex\Exceptions\BitfinexException;
use EwertonDaniel\Bitfinex\Helpers\BitfinexConfig;
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
        $this->client = new Client(config: [
            'base_uri' => $this->url->getBaseUrl(),
            'timeout' => BitfinexConfig::float('timeout.public', 'BITFINEX_PUBLIC_TIMEOUT', 10.0),
        ]);
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
     * Only two keys take a fourth path segment: `pos.size` (`long` or `short`)
     * and `credits.size.sym` (a trading pair). Passing one for any other key
     * builds a path the API answers with a literal null under HTTP 200, which is
     * how `funding.size`, `credits.size`, `vol.*` and `vwap` used to be
     * unreachable through this SDK.
     *
     * @param  string  $key  The type of statistic to retrieve (e.g., 'pos.size', 'funding.size', 'vwap').
     * @param  string  $size  The interval of the data (e.g., '1m', '1d').
     * @param  string|null  $sidePair  Fourth segment: 'long'/'short' for `pos.size`, a pair for
     *                                 `credits.size.sym`, null for everything else.
     * @param  string  $section  Specifies whether to fetch the 'last' or 'hist' data section.
     * @return BitfinexPublicStats The stats service for accessing public statistics data.
     *
     * @link https://docs.bitfinex.com/reference/rest-public-stats
     */
    final public function stats(string $key, string $size, ?string $sidePair = null, string $section = 'hist'): BitfinexPublicStats
    {
        return new BitfinexPublicStats($this->client, $this->url, $key, $size, $sidePair, $section);
    }

    /**
     * Provides an instance to fetch OHLCV candles.
     *
     * @param  string  $timeframe  Timeframe (e.g., 1m, 5m, 15m, 1h, 1D, 1W).
     * @param  string  $section  'hist' or 'last' (default: 'hist').
     *
     * @link https://docs.bitfinex.com/reference/rest-public-candles
     */
    final public function candles(string $timeframe, string $section = 'hist'): \EwertonDaniel\Bitfinex\Services\Public\BitfinexPublicCandles
    {
        return new \EwertonDaniel\Bitfinex\Services\Public\BitfinexPublicCandles($this->client, $this->url, $timeframe, $section);
    }

    /**
     * Provides an instance to fetch public configurations (conf).
     *
     *
     * @link https://docs.bitfinex.com/reference/rest-public-conf
     */
    final public function configs(): \EwertonDaniel\Bitfinex\Services\Public\BitfinexPublicConfigs
    {
        return new \EwertonDaniel\Bitfinex\Services\Public\BitfinexPublicConfigs($this->client, $this->url);
    }

    /**
     * Provides an instance to fetch derivatives status.
     *
     * Call `get()` for the current snapshot, or `history($symbol, ...)` for a
     * time range. The two use different endpoints and different row layouts.
     *
     * @link https://docs.bitfinex.com/reference/rest-public-derivatives-status
     */
    final public function derivativesStatus(): \EwertonDaniel\Bitfinex\Services\Public\BitfinexPublicDerivativesStatus
    {
        return new \EwertonDaniel\Bitfinex\Services\Public\BitfinexPublicDerivativesStatus($this->client, $this->url);
    }

    /**
     * Provides an instance to fetch derivatives status history.
     *
     * Identical to `derivativesStatus()`; kept so existing callers keep working.
     * The history itself comes from `history($symbol, ...)` on the returned
     * service, which requires a symbol because the endpoint carries it in the path.
     *
     * @link https://docs.bitfinex.com/reference/rest-public-derivatives-status-history
     */
    final public function derivativesStatusHistory(): \EwertonDaniel\Bitfinex\Services\Public\BitfinexPublicDerivativesStatus
    {
        return $this->derivativesStatus();
    }

    /** @throws BitfinexException
     *
     * @link https://docs.bitfinex.com/reference/rest-public-liquidations
     */
    final public function liquidations(): \EwertonDaniel\Bitfinex\Services\Public\BitfinexPublicLiquidations
    {
        return new \EwertonDaniel\Bitfinex\Services\Public\BitfinexPublicLiquidations($this->client, $this->url);
    }

    /** @throws BitfinexException
     *
     * @link https://docs.bitfinex.com/reference/rest-public-rankings
     */
    final public function leaderboards(string $key, string $timeframe, string $section = 'hist'): \EwertonDaniel\Bitfinex\Services\Public\BitfinexPublicLeaderboards
    {
        return new \EwertonDaniel\Bitfinex\Services\Public\BitfinexPublicLeaderboards($this->client, $this->url, $key, $timeframe, $section);
    }

    /**
     * Provides an instance to fetch funding statistics.
     *
     *
     * @link https://docs.bitfinex.com/reference/rest-public-funding-stats
     */
    final public function fundingStats(): \EwertonDaniel\Bitfinex\Services\Public\BitfinexPublicFundingStats
    {
        return new \EwertonDaniel\Bitfinex\Services\Public\BitfinexPublicFundingStats($this->client, $this->url);
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

    /**
     * Calculates Market Average Price for a hypothetical order (public calc endpoint).
     *
     * Pass the payload according to the Bitfinex docs (e.g., symbol, amount, and other optional fields).
     *
     * @param  array  $payload  JSON body as defined by the API.
     *
     * @throws BitfinexException
     *
     * @link https://docs.bitfinex.com/reference/rest-public-market-average-price
     */
    final public function marketAveragePrice(array $payload): PublicBitfinexResponse
    {
        try {
            $apiPath = $this->url->setPath('public.market_average_price')->getPath();
            $apiResponse = $this->client->post($apiPath, ['json' => $payload]);

            return (new PublicBitfinexResponse($apiResponse))->marketAveragePrice();
        } catch (GuzzleException $e) {
            throw new BitfinexException($e->getMessage(), $e->getCode());
        }
    }
}
