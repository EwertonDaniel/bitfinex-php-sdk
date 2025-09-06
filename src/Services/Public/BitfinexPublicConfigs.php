<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Services\Public;

use EwertonDaniel\Bitfinex\Builders\UrlBuilder;
use EwertonDaniel\Bitfinex\Exceptions\BitfinexException;
use EwertonDaniel\Bitfinex\Exceptions\BitfinexPathNotFoundException;
use EwertonDaniel\Bitfinex\Http\Responses\PublicBitfinexResponse;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class BitfinexPublicConfigs
{
    public function __construct(
        private readonly Client ,
        private readonly UrlBuilder 
    ) {}

    /**
     * Fetch configuration sections by keys, as defined by the Bitfinex API.
     *
     * @param  string|array    Single key or list of keys, joined by comma.
     * @param  array    Optional query params (e.g., flags).
     * @return PublicBitfinexResponse
     *
     * @throws BitfinexException
     * @throws BitfinexPathNotFoundException
     *
     * @link https://docs.bitfinex.com/reference/rest-public-conf
     */
    final public function get(string|array , array  = []): PublicBitfinexResponse
    {
        try {
             = is_array() ?  : [];

             = ->url->setPath('public.configs', [
                'keys' => implode(',', ),
            ])->getPath();

             = ->client->get(, [
                'query' => ,
            ]);

            return (new PublicBitfinexResponse())->configs();
        } catch (GuzzleException ) {
            throw new BitfinexException(->getMessage(), ->getCode());
        }
    }
}

