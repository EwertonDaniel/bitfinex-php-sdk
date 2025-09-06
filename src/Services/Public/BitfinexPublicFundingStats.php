<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Services\Public;

use Carbon\Carbon;
use EwertonDaniel\Bitfinex\Builders\UrlBuilder;
use EwertonDaniel\Bitfinex\Builders\RequestBuilder;
use EwertonDaniel\Bitfinex\Enums\BitfinexType;
use EwertonDaniel\Bitfinex\Helpers\GetThis;
use EwertonDaniel\Bitfinex\Exceptions\BitfinexException;
use EwertonDaniel\Bitfinex\Exceptions\BitfinexPathNotFoundException;
use EwertonDaniel\Bitfinex\Http\Responses\PublicBitfinexResponse;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class BitfinexPublicFundingStats
{
    public function __construct(
        private readonly Client $client,
        private readonly UrlBuilder $url
    ) {}

    /**
     * Fetch funding statistics for a funding currency (e.g., USD, BTC).
     *
     * @param  string  $currency  Funding currency (no prefix, e.g., 'USD').
     * @param  Carbon|string|null  $start  Optional start time.
     * @param  Carbon|string|null  $end  Optional end time.
     * @param  int|null  $limit  Optional record limit.
     * @param  int|null  $sort  +1 asc, -1 desc.
     *
     * @throws BitfinexException
     * @throws BitfinexPathNotFoundException
     */
    final public function byCurrency(
        string $currency,
        Carbon|string|null $start = null,
        Carbon|string|null $end = null,
        ?int $limit = null,
        ?int $sort = null
    ): PublicBitfinexResponse {
        try {
            $symbol = BitfinexType::FUNDING->symbol($currency);
            $apiPath = $this->url->setPath('public.funding_stats', ['symbol' => $symbol])->getPath();

            $query = array_filter([
                'start' => (fn($dt) => GetThis::ifTrueOrFallback(boolean: $dt instanceof Carbon, callback: fn () => $dt->getTimestampMs(), fallback: fn () => GetThis::ifTrueOrFallback(boolean: is_string($dt), callback: fn () => (new Carbon($dt))->getTimestampMs(), fallback: $dt)))($start),
                'end' => (fn($dt) => GetThis::ifTrueOrFallback(boolean: $dt instanceof Carbon, callback: fn () => $dt->getTimestampMs(), fallback: fn () => GetThis::ifTrueOrFallback(boolean: is_string($dt), callback: fn () => (new Carbon($dt))->getTimestampMs(), fallback: $dt)))($end),
                'limit' => $limit,
                'sort' => $sort,
            ], fn ($v) => ! is_null($v));

            $apiResponse = $this->client->get($apiPath, ['query' => $query]);

            return (new PublicBitfinexResponse($apiResponse))->fundingStats($symbol);
        } catch (GuzzleException $e) {
            throw new BitfinexException($e->getMessage(), $e->getCode());
        }
    }
}

