<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Services\Public;

use Carbon\Carbon;
use EwertonDaniel\Bitfinex\Builders\UrlBuilder;
use EwertonDaniel\Bitfinex\Enums\BitfinexType;
use EwertonDaniel\Bitfinex\Exceptions\BitfinexException;
use EwertonDaniel\Bitfinex\Helpers\DateToTimestamp;
use EwertonDaniel\Bitfinex\Http\Responses\PublicBitfinexResponse;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Class BitfinexPublicTrade
 *
 * Provides functionality for accessing Bitfinex public trade data.
 * This class supports retrieving historical trade data for specific symbols,
 * filtering by date range, sorting, and limiting the number of results.
 *
 * @author  Ewerton Daniel
 *
 * @contact contact@ewertondaniel.work
 */
class BitfinexPublicTrade
{
    private readonly ?int $start;

    private readonly ?int $end;

    /**
     * @param  Client  $client  Instance of Guzzle HTTP client for making requests.
     * @param  UrlBuilder  $url  Instance of UrlBuilder for constructing API paths.
     * @param  int  $limit  Number of records to retrieve (default: 125).
     * @param  int  $sort  Sort order: +1 (ascending), -1 (descending).
     * @param  Carbon|string|null  $start  Start date or timestamp for filtering results (optional).
     * @param  Carbon|string|null  $end  End date or timestamp for filtering results (optional).
     */
    public function __construct(
        private readonly Client $client,
        private readonly UrlBuilder $url,
        private readonly int $limit,
        private readonly int $sort,
        Carbon|string|null $start,
        Carbon|string|null $end
    ) {
        $this->start = DateToTimestamp::convert($start);
        $this->end = DateToTimestamp::convert($end);
    }

    /**
     * Retrieves historical trade data for a given symbol.
     *
     * This method fetches public trade data for a specified trading pair or currency.
     * It supports pagination, sorting, and filtering using timestamps.
     *
     * @param  string  $symbol  Trading pair or currency symbol (e.g., tBTCUSD, fUSD).
     * @param  BitfinexType  $type  The type of trade data (e.g., trading or funding).
     * @return PublicBitfinexResponse A structured response containing trade data.
     *
     * @throws BitfinexException If the request fails or an error occurs during processing.
     *
     * @link https://docs.bitfinex.com/reference/rest-public-trades
     */
    protected function get(string $symbol, BitfinexType $type): PublicBitfinexResponse
    {
        try {
            $apiPath = $this->url->setPath(path: 'public.trades', params: ['symbol' => $symbol])->getPath();

            $apiResponse = $this->client->get(
                uri: $apiPath,
                options: [
                    'query' => [
                        'limit' => $this->limit,
                        'sort' => $this->sort,
                        'start' => $this->start,
                        'end' => $this->end,
                    ],
                ]
            );

            return (new PublicBitfinexResponse($apiResponse))->trades(symbol: $symbol, type: $type);

        } catch (GuzzleException $e) {
            throw new BitfinexException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Retrieves trade data for a specific currency.
     *
     * Fetches funding trade data using the given currency symbol.
     *
     * @param  string  $currency  The currency symbol (e.g., USD, EUR).
     * @return PublicBitfinexResponse A structured response containing trade data.
     *
     * @throws BitfinexException If the request fails or an error occurs during processing.
     */
    final public function byCurrency(string $currency): PublicBitfinexResponse
    {
        $type = BitfinexType::FUNDING;
        $symbol = $type->symbol($currency);

        return $this->get(symbol: $symbol, type: $type);
    }

    /**
     * Retrieves trade data for a specific trading pair.
     *
     * Fetches trading data using the given trading pair symbol.
     *
     * @param  string  $pair  The trading pair symbol (e.g., BTCUSD).
     * @return PublicBitfinexResponse A structured response containing trade data.
     *
     * @throws BitfinexException If the request fails or an error occurs during processing.
     */
    final public function byPair(string $pair): PublicBitfinexResponse
    {
        $type = BitfinexType::TRADING;
        $symbol = $type->symbol($pair);

        return $this->get(symbol: $symbol, type: $type);
    }
}
