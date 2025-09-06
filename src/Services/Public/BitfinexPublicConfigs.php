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

class BitfinexPublicConfigs
{
    public function __construct(
        private readonly Client $client,
        private readonly UrlBuilder $url
    ) {}

    /**
     * Fetch configuration sections by keys, as defined by the Bitfinex API.
     *
     * @param  string|array  $keys  Single key or list of keys, joined by comma.
     * @param  array  $query  Optional query params (e.g., flags).
     * @return PublicBitfinexResponse
     *
     * @throws BitfinexException
     * @throws BitfinexPathNotFoundException
     *
     * @link https://docs.bitfinex.com/reference/rest-public-conf
     */
    final public function get(string|array $keys, array $query = []): PublicBitfinexResponse
    {
        try {
            $keysList = GetThis::ifTrueOrFallback(boolean: is_array($keys), callback: fn () => $keys, fallback: fn () => [$keys]);

            // Support structured modes: ['map' => ['currency:sym'], 'list' => ['pair:exchange'], 'info' => ['pair','tx:status']]
            $hasAssociative = array_keys($keysList) !== range(0, count($keysList) - 1);
            if ($hasAssociative) {
                $expanded = [];
                foreach ($keysList as $mode => $items) {
                    if (!is_array($items)) {
                        $items = [$items];
                    }
                    foreach ($items as $item) {
                        // If already full key, keep as-is
                        if (is_string($item) && str_starts_with($item, 'pub:')) {
                            $expanded[] = $item;
                            continue;
                        }
                        $item = (string) $item;
                        switch ((string) $mode) {
                            case 'map':
                                $expanded[] = 'pub:map:' . $item;
                                break;
                            case 'list':
                                $expanded[] = 'pub:list:' . $item;
                                break;
                            case 'info':
                                if (str_starts_with($item, 'tx:status')) {
                                    $expanded[] = 'pub:info:tx:status';
                                } else {
                                    $expanded[] = 'pub:info:' . $item;
                                }
                                break;
                            default:
                                $expanded[] = $item;
                        }
                    }
                }
                $keysList = $expanded;
            }

            $apiPath = $this->url->setPath('public.configs', [
                'keys' => implode(',', $keysList),
            ])->getPath();

            $apiResponse = $this->client->get($apiPath, [
                'query' => $query,
            ]);

            return (new PublicBitfinexResponse($apiResponse))->configs($keysList);
        } catch (GuzzleException $e) {
            throw new BitfinexException($e->getMessage(), $e->getCode());
        }
    }
}
