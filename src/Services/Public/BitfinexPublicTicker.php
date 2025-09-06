<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Services\Public;

use Carbon\Carbon;
use EwertonDaniel\Bitfinex\Builders\UrlBuilder;
use EwertonDaniel\Bitfinex\Enums\BitfinexType;
use EwertonDaniel\Bitfinex\Exceptions\BitfinexException;
use EwertonDaniel\Bitfinex\Exceptions\BitfinexPathNotFoundException;
use EwertonDaniel\Bitfinex\Helpers\DateToTimestamp;
use EwertonDaniel\Bitfinex\Http\Responses\PublicBitfinexResponse;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Class BitfinexPublicTicker
 *
 * Provides access to Bitfinex public ticker data, including real-time and historical
 * market data for trading pairs and funding currencies.
 *
 * @author  Ewerton Daniel
 *
 * @contact contact@ewertondaniel.work
 */
class BitfinexPublicTicker
{
    /**
     * Constructor for the BitfinexPublicTicker class.
     *
     * Initializes the service with a Guzzle HTTP client and a URL builder.
     *
     * @param  Client  $client  Instance of Guzzle HTTP client for making API requests.
     * @param  UrlBuilder  $url  Instance of UrlBuilder for constructing API paths.
     */
    public function __construct(private readonly Client $client, private readonly UrlBuilder $url) {}

    /**
     * Retrieves the latest state of multiple markets (tickers) based on provided symbols.
     *
     * @param  string  $symbols  Comma-separated list of symbols (e.g., "tBTCUSD,fUSD").
     * @param  BitfinexType  $type  The type of market (trading or funding).
     * @return PublicBitfinexResponse Response containing the tickers for the requested symbols.
     *
     * @throws BitfinexException If the API request fails or an unexpected error occurs.
     * @throws BitfinexPathNotFoundException If the API path is not found.
     *
     * @link https://docs.bitfinex.com/reference/rest-public-tickers
     */
    protected function get(string $symbols, BitfinexType $type): PublicBitfinexResponse
    {
        try {
            $apiPath = $this->url->setPath(path: 'public.tickers')->getPath();

            $options = ['query' => ['symbols' => $symbols]];

            $apiResponse = $this->client->get($apiPath, $options);

            return (new PublicBitfinexResponse($apiResponse))->tickers($type);
        } catch (GuzzleException $e) {
            throw new BitfinexException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Retrieves the latest state of all funding currencies.
     *
     * @param  array  $currencies  List of currencies (e.g., ["USD", "EUR"]).
     * @return PublicBitfinexResponse Response containing tickers for the specified currencies.
     *
     * @throws BitfinexPathNotFoundException If the API path is not found.
     * @throws BitfinexException If the API request fails or an unexpected error occurs.
     */
    final public function byCurrencies(array $currencies): PublicBitfinexResponse
    {
        $type = BitfinexType::FUNDING;
        $symbols = $type->symbols($currencies);

        return $this->get(symbols: $symbols, type: $type);
    }

    /**
     * Retrieves the latest state of all trading pairs.
     *
     * @param  array  $pairs  List of trading pairs (e.g., ["tBTCUSD", "tETHUSD"]).
     * @return PublicBitfinexResponse Response containing tickers for the specified trading pairs.
     *
     * @throws BitfinexPathNotFoundException If the API path is not found.
     * @throws BitfinexException If the API request fails or an unexpected error occurs.
     */
    final public function byPairs(array $pairs): PublicBitfinexResponse
    {
        $type = BitfinexType::TRADING;
        $symbols = $type->symbols($pairs);

        return $this->get(symbols: $symbols, type: $type);
    }

    /**
     * Retrieves market data for a specific trading pair or funding currency.
     *
     * @param  string  $symbol  The trading pair (e.g., tBTCUSD) or funding currency (e.g., fUSD).
     * @param  BitfinexType|string  $type  The type of market (trading or funding).
     * @return PublicBitfinexResponse Response containing ticker data for the specified symbol.
     *
     * @throws BitfinexException If the API request fails or an unexpected error occurs.
     *
     * @link https://docs.bitfinex.com/reference/rest-public-ticker
     */
    protected function ticker(string $symbol, BitfinexType|string $type): PublicBitfinexResponse
    {
        try {
            $apiPath = $this->url->setPath(path: 'public.ticker', params: ['symbol' => $symbol])->getPath();

            $apiResponse = $this->client->get($apiPath);

            return (new PublicBitfinexResponse($apiResponse))->ticker($symbol, $type);
        } catch (GuzzleException $e) {
            throw new BitfinexException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Retrieves ticker data for a specific trading pair.
     *
     * @param  string  $pair  The trading pair symbol (e.g., tBTCUSD).
     * @return PublicBitfinexResponse Response containing ticker data for the trading pair.
     *
     * @throws BitfinexException If the API request fails or an unexpected error occurs.
     */
    final public function byPair(string $pair): PublicBitfinexResponse
    {
        $type = BitfinexType::TRADING;

        return $this->ticker(symbol: $type->symbol($pair), type: $type);
    }

    /**
     * Retrieves ticker data for a specific funding currency.
     *
     * @param  string  $currency  The currency symbol (e.g., USD, EUR).
     * @return PublicBitfinexResponse Response containing ticker data for the currency.
     *
     * @throws BitfinexException If the API request fails or an unexpected error occurs.
     */
    final public function byCurrency(string $currency): PublicBitfinexResponse
    {
        $type = BitfinexType::FUNDING;

        return $this->ticker(symbol: $type->symbol($currency), type: $type);
    }

    /**
     * Retrieves historical ticker data for a list of trading pairs.
     *
     * @param  array  $pairs  List of trading pairs (e.g., ["tBTCUSD", "tETHUSD"]).
     * @param  int  $limit  Maximum number of records to fetch (default: 100).
     * @param  string|Carbon|null  $start  Start timestamp in milliseconds (optional).
     * @param  string|Carbon|null  $end  End timestamp in milliseconds (optional).
     * @return PublicBitfinexResponse Response containing historical ticker data.
     *
     * @throws BitfinexException If the API request fails or an unexpected error occurs.
     * @throws BitfinexPathNotFoundException
     *
     * @link https://docs.bitfinex.com/reference/rest-public-ticker-history
     */
    final public function history(
        array $pairs,
        int $limit = 100,
        string|Carbon|null $start = null,
        string|Carbon|null $end = null
    ): PublicBitfinexResponse {
        try {
            $apiPath = $this->url->setPath(path: 'public.ticker_history')->getPath();

            $apiResponse = $this->client->get($apiPath, [
                'query' => [
                    'symbols' => BitfinexType::TRADING->symbols($pairs),
                    'limit' => $limit,
                    'start' => DateToTimestamp::convert($start),
                    'end' => DateToTimestamp::convert($end),
                ],
            ]);

            return (new PublicBitfinexResponse($apiResponse))->tickerHistory();
        } catch (GuzzleException $e) {
            throw new BitfinexException($e->getMessage(), $e->getCode());
        }
    }
}
