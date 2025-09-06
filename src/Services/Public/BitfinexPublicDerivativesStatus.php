<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Services\Public;

use EwertonDaniel\Bitfinex\Builders\UrlBuilder;
use EwertonDaniel\Bitfinex\Exceptions\BitfinexException;
use EwertonDaniel\Bitfinex\Exceptions\BitfinexPathNotFoundException;
use EwertonDaniel\Bitfinex\Helpers\GetThis;
use EwertonDaniel\Bitfinex\Http\Responses\PublicBitfinexResponse;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class BitfinexPublicDerivativesStatus
{
    public function __construct(
        private readonly Client $client,
        private readonly UrlBuilder $url
    ) {}

    /**
     * Fetch derivatives status. When start/end/limit/sort are provided, returns historical data.
     *
     * @param string|array|null $keys Optional list of symbols/keys; joined by comma.
     * @param int|null $start MTS >= start (ms).
     * @param int|null $end MTS <= end (ms).
     * @param int|null $limit Max number of records.
     * @param int|null $sort +1 asc, -1 desc.
     * @return PublicBitfinexResponse
     *
     * @throws BitfinexException
     * @throws BitfinexPathNotFoundException
     *
     * @link https://docs.bitfinex.com/reference/rest-public-derivatives-status
     */
    final public function get(
        string|array|null $keys = null,
        ?int $start = null,
        ?int $end = null,
        ?int $limit = null,
        ?int $sort = null
    ): PublicBitfinexResponse
    {
        try {
            $apiPath = $this->url->setPath('public.derivatives_status')->getPath();

            $query = array_filter([
                'keys' => GetThis::ifTrueOrFallback(boolean: is_null($keys), callback: null, fallback: GetThis::ifTrueOrFallback(boolean: is_array($keys), callback: fn () => implode(',', $keys), fallback: $keys)),
                'start' => $start,
                'end' => $end,
                'limit' => $limit,
                'sort' => $sort,
            ], fn($v) => !is_null($v));

            $apiResponse = $this->client->get($apiPath, ['query' => $query]);

            return (new PublicBitfinexResponse($apiResponse))
                ->derivativesStatus(
                    keys: GetThis::ifTrueOrFallback(boolean: is_array($keys), callback: fn() => $keys, fallback: fn() => GetThis::ifTrueOrFallback(
                    boolean: is_null($keys),
                    callback: [], fallback: fn() => [$keys]
                ))
                );
        } catch (GuzzleException $e) {
            throw new BitfinexException($e->getMessage(), $e->getCode());
        }
    }
}
