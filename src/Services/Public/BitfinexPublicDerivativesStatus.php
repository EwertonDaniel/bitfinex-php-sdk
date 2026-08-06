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
     * Fetch the current derivatives status snapshot.
     *
     * Despite what the reference states, omitting `keys` returns an empty array
     * rather than a default pair, so it defaults to `ALL` here.
     *
     * This endpoint has no history: `start`, `end`, `limit` and `sort` are
     * ignored by the snapshot path. Use `history()` for a time range.
     *
     * @param  string|array|null  $keys  Symbols/keys to fetch; joined by comma. Defaults to `ALL`.
     *
     * @throws BitfinexException
     * @throws BitfinexPathNotFoundException
     *
     * @link https://docs.bitfinex.com/reference/rest-public-derivatives-status
     */
    final public function get(string|array|null $keys = null): PublicBitfinexResponse
    {
        try {
            $apiPath = $this->url->setPath('public.derivatives_status')->getPath();

            $apiResponse = $this->client->get($apiPath, ['query' => ['keys' => $this->keysToQuery($keys)]]);

            return (new PublicBitfinexResponse($apiResponse))->derivativesStatus(keys: $this->keysToList($keys));
        } catch (GuzzleException $e) {
            throw new BitfinexException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Fetch the historical derivatives status of a single symbol.
     *
     * History rows have no leading KEY field, so they map to
     * `DerivativeStatusHistory` rather than to `DerivativeStatus`.
     *
     * @param  string  $symbol  Derivative symbol (e.g., tBTCF0:USTF0).
     * @param  int|null  $start  MTS >= start (ms).
     * @param  int|null  $end  MTS <= end (ms).
     * @param  int|null  $limit  Max number of records (max 5000).
     * @param  int|null  $sort  +1 ascending, -1 descending.
     *
     * @throws BitfinexException
     * @throws BitfinexPathNotFoundException
     *
     * @link https://docs.bitfinex.com/reference/rest-public-derivatives-status-history
     */
    final public function history(
        string $symbol,
        ?int $start = null,
        ?int $end = null,
        ?int $limit = null,
        ?int $sort = null
    ): PublicBitfinexResponse {
        try {
            $apiPath = $this->url
                ->setPath('public.derivatives_status_history', ['symbol' => $symbol])
                ->getPath();

            $query = array_filter(
                ['start' => $start, 'end' => $end, 'limit' => $limit, 'sort' => $sort],
                fn ($value) => ! is_null($value)
            );

            $apiResponse = $this->client->get($apiPath, ['query' => $query]);

            return (new PublicBitfinexResponse($apiResponse))
                ->derivativesStatus(keys: [$symbol], history: true);
        } catch (GuzzleException $e) {
            throw new BitfinexException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Normalizes the keys argument into the comma-separated form the query expects.
     */
    private function keysToQuery(string|array|null $keys): string
    {
        return implode(',', $this->keysToList($keys));
    }

    /**
     * Normalizes the keys argument into a list, defaulting to `ALL`.
     *
     * @return list<string>
     */
    private function keysToList(string|array|null $keys): array
    {
        return GetThis::ifTrueOrFallback(
            boolean: is_array($keys),
            callback: fn () => array_values($keys),
            fallback: fn () => GetThis::ifTrueOrFallback(
                boolean: is_null($keys),
                callback: ['ALL'],
                fallback: fn () => [$keys]
            )
        );
    }
}
