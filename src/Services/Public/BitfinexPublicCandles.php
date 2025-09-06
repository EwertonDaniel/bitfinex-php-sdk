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

class BitfinexPublicCandles
{
    public function __construct(
        private readonly Client $client,
        private readonly UrlBuilder $url,
        private readonly string $timeframe,
        private readonly string $section = 'hist'
    ) {}

    /**
     * Fetch candles for a trading pair.
     *
     * @param  string  $pair  Pair like BTCUSD (prefix is added automatically).
     * @param  Carbon|string|null  $start  Optional start time.
     * @param  Carbon|string|null  $end  Optional end time.
     * @param  int|null  $limit  Optional record limit.
     * @param  int|null  $sort  Optional sort: +1 asc, -1 desc.
     *
     * @throws BitfinexException
     * @throws BitfinexPathNotFoundException
     */
    final public function byPair(
        string $pair,
        Carbon|string|null $start = null,
        Carbon|string|null $end = null,
        ?int $limit = null,
        ?int $sort = null
    ): PublicBitfinexResponse {
        return $this->get(BitfinexType::TRADING->symbol($pair), $start, $end, $limit, $sort);
    }

    /**
     * Fetch candles for a funding currency.
     *
     * @param  string  $currency  Currency like USD (prefix is added automatically).
     * @param  Carbon|string|null  $start  Optional start time.
     * @param  Carbon|string|null  $end  Optional end time.
     * @param  int|null  $limit  Optional record limit.
     * @param  int|null  $sort  Optional sort: +1 asc, -1 desc.
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
        return $this->get(BitfinexType::FUNDING->symbol($currency), $start, $end, $limit, $sort);
    }

    /**
     * Internal fetch method.
     */
    private function get(
        string $symbol,
        Carbon|string|null $start,
        Carbon|string|null $end,
        ?int $limit,
        ?int $sort
    ): PublicBitfinexResponse {
        try {
            $apiPath = $this->url->setPath('public.candles', [
                'timeframe' => $this->timeframe,
                'symbol' => $symbol,
                'section' => $this->section,
            ])->getPath();

            $apiResponse = $this->client->get($apiPath, [
                'query' => array_filter([
                    'start' => DateToTimestamp::convert($start),
                    'end' => DateToTimestamp::convert($end),
                    'limit' => $limit,
                    'sort' => $sort,
                ], fn ($v) => ! is_null($v)),
            ]);

            return (new PublicBitfinexResponse($apiResponse))->candles($symbol, $this->timeframe, $this->section);
        } catch (GuzzleException $e) {
            throw new BitfinexException($e->getMessage(), $e->getCode());
        }
    }
}

