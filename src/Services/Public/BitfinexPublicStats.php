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
 * Class BitfinexPublicStats
 *
 * Provides access to statistical data from the Bitfinex API, including
 * position sizes, funding sizes, and other platform-specific metrics.
 * The class allows retrieving historical or real-time statistics
 * based on configurable parameters.
 *
 * @author  Ewerton Daniel
 *
 * @contact contact@ewertondaniel.work
 */
class BitfinexPublicStats
{
    /**
     * Constructor for the BitfinexPublicStats class.
     *
     * @param  Client  $client  Instance of Guzzle HTTP client for making API requests.
     * @param  UrlBuilder  $url  Instance of UrlBuilder for constructing API paths.
     * @param  string  $key  The type of statistic to retrieve (e.g., 'pos.size', 'funding.size').
     * @param  string  $size  The interval or granularity of the data (e.g., '1m', '30m', '1d').
     * @param  string  $sidePair  The side of the data (e.g., 'long', 'short'), or a pair for credits.
     * @param  string  $section  Specifies whether to fetch the 'last' or 'hist' data section.
     */
    public function __construct(
        private readonly Client $client,
        private readonly UrlBuilder $url,
        private readonly string $key,
        private readonly string $size,
        private readonly string $sidePair,
        private readonly string $section
    ) {}

    /**
     * Retrieves statistics from the platform's operational data based on specified parameters.
     *
     * This method fetches statistics from the Bitfinex API by constructing the appropriate URL
     * with the provided key, size, symPlatform, sidePair, and section. It also allows additional
     * query parameters such as sorting and filtering by start and end time.
     *
     * @param  string  $symPlatform  The trading pair or funding currency symbol.
     * @param  ?int  $sort  [Optional] Sort order: +1 for ascending, -1 for descending.
     * @param  ?int  $start  [Optional] Records with MTS >= start (milliseconds).
     * @param  ?int  $end  [Optional] Records with MTS <= end (milliseconds).
     * @param  ?int  $limit  [Optional] Maximum number of records (default: 100).
     * @return PublicBitfinexResponse Returns a response object containing the requested statistics.
     *
     * @throws BitfinexException If the API request fails or an error occurs during processing.
     * @throws BitfinexPathNotFoundException If the API path is not found.
     *
     * @link https://docs.bitfinex.com/reference/rest-public-stats
     */
    protected function get(string $symPlatform, ?int $sort, ?int $start, ?int $end, ?int $limit): PublicBitfinexResponse
    {
        try {
            $apiPath = $this->url->setPath('public.stats_one', [
                'key' => $this->key,
                'size' => $this->size,
                'sym_platform' => $symPlatform,
                'side_pair' => $this->sidePair,
                'section' => $this->section,
            ])->getPath();

            $apiResponse = $this->client->get($apiPath, [
                'query' => array_filter([
                    'sort' => $sort,
                    'start' => $start,
                    'end' => $end,
                    'limit' => $limit,
                ], fn ($value) => ! is_null($value)),
            ]);

            return (new PublicBitfinexResponse($apiResponse))->stats($this->key, $this->size, $symPlatform, $this->sidePair, $this->section);
        } catch (GuzzleException $e) {
            throw new BitfinexException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Retrieves statistics for a specific trading pair.
     *
     * @param  string  $pair  The trading pair symbol (e.g., tBTCUSD).
     * @param  ?int  $sort  [Optional] Sort order: +1 for ascending, -1 for descending.
     * @param  Carbon|string|null  $start  [Optional] Start time (as Carbon instance or string).
     * @param  Carbon|string|null  $end  [Optional] End time (as Carbon instance or string).
     * @param  ?int  $limit  [Optional] Maximum number of records to retrieve.
     * @return PublicBitfinexResponse Returns a response object containing the requested statistics.
     *
     * @throws BitfinexException If the API request fails or an error occurs during processing.
     */
    final public function byPair(
        string $pair,
        ?int $sort = null,
        Carbon|string|null $start = null,
        Carbon|string|null $end = null,
        ?int $limit = null
    ): PublicBitfinexResponse {
        $type = BitfinexType::TRADING;

        $symPlatform = $type->symbol($pair);

        return $this->get(
            symPlatform: $symPlatform,
            sort: $sort,
            start: DateToTimestamp::convert($start),
            end: DateToTimestamp::convert($end),
            limit: $limit
        );
    }

    /**
     * Retrieves statistics for a specific funding currency.
     *
     * @param  string  $currency  The funding currency symbol (e.g., USD, EUR).
     * @param  ?int  $sort  [Optional] Sort order: +1 for ascending, -1 for descending.
     * @param  Carbon|string|null  $start  [Optional] Start time (as Carbon instance or string).
     * @param  Carbon|string|null  $end  [Optional] End time (as Carbon instance or string).
     * @param  ?int  $limit  [Optional] Maximum number of records to retrieve.
     * @return PublicBitfinexResponse Returns a response object containing the requested statistics.
     *
     * @throws BitfinexException If the API request fails or an error occurs during processing.
     * @throws BitfinexPathNotFoundException If the API path is not found.
     */
    final public function byCurrency(
        string $currency,
        ?int $sort = null,
        Carbon|string|null $start = null,
        Carbon|string|null $end = null,
        ?int $limit = null
    ): PublicBitfinexResponse {
        $type = BitfinexType::FUNDING;

        $symPlatform = $type->symbol($currency);

        return $this->get(
            symPlatform: $symPlatform,
            sort: $sort,
            start: DateToTimestamp::convert($start),
            end: DateToTimestamp::convert($end),
            limit: $limit
        );
    }
}
