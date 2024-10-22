<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Core\Services;

use EwertonDaniel\Bitfinex\Core\Builders\UrlBuilder;
use EwertonDaniel\Bitfinex\Core\Entities\CurrencyTrade;
use EwertonDaniel\Bitfinex\Core\Entities\FundingCurrency;
use EwertonDaniel\Bitfinex\Core\Entities\PairTrade;
use EwertonDaniel\Bitfinex\Core\Entities\TickerHistory;
use EwertonDaniel\Bitfinex\Core\Entities\TradingPair;
use EwertonDaniel\Bitfinex\Exceptions\BitfinexException;
use EwertonDaniel\Bitfinex\Helpers\GetThis;
use EwertonDaniel\Bitfinex\Http\Responses\BitfinexResponse;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\HttpFoundation\Response;

class BitfinexPublic
{
    private readonly Client $client;

    public function __construct(private readonly UrlBuilder $url)
    {
        $this->client = new Client;
    }

    /**
     * @note Get the current status of the platform, “Operative” or “Maintenance”.
     *
     * @throws BitfinexException
     */
    final public function platformStatus(): array
    {
        try {
            $response = new BitfinexResponse($this->client->get($this->url->setPath('public.status')->get()));

            return $response->result(
                closure: fn (array $data) => collect([
                    'status' => GetThis::ifTrueOrFallback($data['0'], 'operative', 'maintenance'),
                ])
            );
        } catch (GuzzleException|Exception $e) {
            throw new BitfinexException($e->getMessage(), $e->getCode());
        }

    }

    /**
     * @throws BitfinexException
     *
     * @note The ticker endpoint provides a high level overview of the state of the market for a specified pair.
     * It shows the current best bid and ask, the last traded price, as well as information on the daily volume and price movement over the last day.
     */
    final public function ticker(string $symbol, string $type): array
    {
        try {

            $type = $this->getType($type);

            $url = $this->url->setPath(path: 'public.ticker', params: ['symbol' => $symbol, 'type' => $type])->get();

            $response = new BitfinexResponse($this->client->get($url));

            return $response->result(fn (array $ticker) => match ($type) {
                't' => new TradingPair($symbol, $ticker),
                'f' => new FundingCurrency($symbol, $ticker)
            });

        } catch (GuzzleException|Exception $e) {
            throw new BitfinexException($e->getMessage(), $e->getCode());
        }
    }

    /** @throws BitfinexException */
    private function getType(string $type): string
    {
        return match ($type) {
            'trading', 't' => 't',
            'funding', 'f' => 'f',
            default => throw new BitfinexException('Invalid ticker type', Response::HTTP_INTERNAL_SERVER_ERROR)
        };
    }

    /**
     * @throws BitfinexException
     *
     * @note The tickers endpoint provides a high level overview of the state of the market.
     * It shows the current best bid and ask, the last traded price, as well as information on the daily volume and price movement over the last day.
     * The endpoint can retrieve multiple tickers with a single query.
     */
    final public function tickers(array $symbols, string $type): array
    {

        try {
            $type = $this->getType($type);

            $symbols = implode(",$type", $symbols);

            $response = new BitfinexResponse($this->client->get($this->url->setPath(path: 'public.tickers')->get(), [
                'query' => [
                    'symbols' => "$type$symbols",
                ],
            ]));

            return $response->result(
                closure: fn ($data) => collect($data)->map(function ($ticker) use ($type) {

                    $symbol = $ticker[0];
                    array_shift($ticker);

                    return match ($type) {
                        't' => new TradingPair($symbol, $ticker),
                        'f' => new FundingCurrency($symbol, $ticker)
                    };
                })
            );

        } catch (GuzzleException|Exception $e) {
            throw new BitfinexException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @throws BitfinexException
     *
     * @note History of recent trading tickers. Provides historic data of the best bid and ask at an hourly interval.
     * Historic data goes back 1 year.
     */
    final public function tickerHistory(array $symbols, int $limit = 100, ?string $start = null, ?string $end = null): array
    {
        try {

            $symbols = implode(',t', $symbols);

            $response = new BitfinexResponse(
                $this->client->get($this->url->setPath(path: 'public.ticker_history')->get(), [
                    'query' => [
                        'symbols' => "t$symbols",
                        'limit' => $limit,
                        'start' => $start,
                        'end' => $end,
                    ],
                ])
            );

            return $response->result(closure: fn ($data) => collect($data)->map(fn ($ticker) => new TickerHistory($ticker))->groupBy('symbol'));

        } catch (GuzzleException $e) {
            throw new BitfinexException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @throws BitfinexException
     *
     * @note The trades endpoint allows the retrieval of past public trades and includes details such as price, size, and time.
     * Optional parameters can be used to limit the number of results; you can specify a start and end timestamp, a limit, and a sorting method.
     */
    final public function trades(string $symbol, string $type, int $limit = 125, int $sort = -1, ?int $start = null, ?int $end = null): array
    {
        try {
            $type = $this->getType($type);

            $url = $this->url->setPath(path: 'public.trades', params: ['symbol' => $symbol, 'type' => $type])->get();

            $response = new BitfinexResponse(
                $this->client->get($url, [
                    'query' => [
                        'limit' => $limit,
                        'sort' => $sort,
                        'start' => $start,
                        'end' => $end,
                    ],
                ])
            );

            return $response->result(fn ($data) => collect($data)->map(fn ($trade) => match ($type) {
                't' => new PairTrade($symbol, $trade),
                'f' => new CurrencyTrade($symbol, $trade)
            }));

        } catch (GuzzleException|Exception $e) {
            throw new BitfinexException($e->getMessage(), $e->getCode());
        }
    }
}
