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

class BitfinexPublicLeaderboards
{
    public function __construct(
        private readonly Client $client,
        private readonly UrlBuilder $url,
        private readonly string $key,
        private readonly string $timeframe,
        private readonly string $section = 'hist'
    ) {}

    /**
     * Leaderboards by trading pair.
     */
    final public function byPair(
        string $pair,
        Carbon|string|null $start = null,
        Carbon|string|null $end = null,
        ?int $limit = null,
        ?int $sort = null
    ): PublicBitfinexResponse {
        $symbol = BitfinexType::TRADING->symbol($pair);
        return $this->get($symbol, $start, $end, $limit, $sort);
    }

    /**
     * Leaderboards by funding currency.
     */
    final public function byCurrency(
        string $currency,
        Carbon|string|null $start = null,
        Carbon|string|null $end = null,
        ?int $limit = null,
        ?int $sort = null
    ): PublicBitfinexResponse {
        $symbol = BitfinexType::FUNDING->symbol($currency);
        return $this->get($symbol, $start, $end, $limit, $sort);
    }

    private function get(
        string $symbol,
        Carbon|string|null $start,
        Carbon|string|null $end,
        ?int $limit,
        ?int $sort
    ): PublicBitfinexResponse {
        try {
            $apiPath = $this->url->setPath('public.leaderboards', [
                'key' => $this->key,
                'timeframe' => $this->timeframe,
                'symbol' => $symbol,
                'section' => $this->section,
            ])->getPath();

            $options = (new RequestBuilder())->setMethod('GET')->setQuery(array_filter([
                    'start' => (fn($dt) => GetThis::ifTrueOrFallback(boolean: $dt instanceof Carbon, callback: fn () => $dt->getTimestampMs(), fallback: fn () => GetThis::ifTrueOrFallback(boolean: is_string($dt), callback: fn () => (new Carbon($dt))->getTimestampMs(), fallback: $dt)))($start),
                    'end' => (fn($dt) => GetThis::ifTrueOrFallback(boolean: $dt instanceof Carbon, callback: fn () => $dt->getTimestampMs(), fallback: fn () => GetThis::ifTrueOrFallback(boolean: is_string($dt), callback: fn () => (new Carbon($dt))->getTimestampMs(), fallback: $dt)))($end),
                    'limit' => $limit,
                    'sort' => $sort,
                ], fn ($v) => ! is_null($v)))->getOptions();
            $apiResponse = $this->client->get($apiPath, $options);

            return (new PublicBitfinexResponse($apiResponse))->leaderboards($this->key, $this->timeframe, $symbol, $this->section);
        } catch (GuzzleException $e) {
            throw new BitfinexException($e->getMessage(), $e->getCode());
        }
    }
}

