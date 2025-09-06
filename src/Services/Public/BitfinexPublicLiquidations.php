<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Services\Public;

use EwertonDaniel\Bitfinex\Builders\UrlBuilder;
use EwertonDaniel\Bitfinex\Exceptions\BitfinexException;
use EwertonDaniel\Bitfinex\Exceptions\BitfinexPathNotFoundException;
use EwertonDaniel\Bitfinex\Http\Responses\PublicBitfinexResponse;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class BitfinexPublicLiquidations
{
    public function __construct(
        private readonly Client $client,
        private readonly UrlBuilder $url
    ) {}

    /**
     * Fetch public liquidations with optional time window and sorting.
     *
     * @param  int|null  $start  MTS >= start (ms)
     * @param  int|null  $end  MTS <= end (ms)
     * @param  int|null  $limit  Max number of records
     * @param  int|null  $sort  +1 asc, -1 desc
     * @return PublicBitfinexResponse
     *
     * @throws BitfinexException
     * @throws BitfinexPathNotFoundException
     *
     * @link https://docs.bitfinex.com/reference/rest-public-liquidations
     */
    final public function get(?int $start = null, ?int $end = null, ?int $limit = null, ?int $sort = null): PublicBitfinexResponse
    {
        try {
            $apiPath = $this->url->setPath('public.liquidations')->getPath();

            $apiResponse = $this->client->get($apiPath, [
                'query' => array_filter(compact('start', 'end', 'limit', 'sort'), fn ($v) => ! is_null($v)),
            ]);

            return (new PublicBitfinexResponse($apiResponse))->liquidations();
        } catch (GuzzleException $e) {
            throw new BitfinexException($e->getMessage(), $e->getCode());
        }
    }
}

