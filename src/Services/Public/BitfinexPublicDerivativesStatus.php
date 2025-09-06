<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Services\Public;

use EwertonDaniel\Bitfinex\Builders\UrlBuilder;
use EwertonDaniel\Bitfinex\Exceptions\BitfinexException;
use EwertonDaniel\Bitfinex\Exceptions\BitfinexPathNotFoundException;
use EwertonDaniel\Bitfinex\Http\Responses\PublicBitfinexResponse;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class BitfinexPublicDerivativesStatus
{
    public function __construct(
        private readonly Client ,
        private readonly UrlBuilder 
    ) {}

    /**
     * Fetch derivatives status. When start/end/limit/sort are provided, returns historical data.
     *
     * @param  string|array|null    Optional list of symbols/keys; joined by comma.
     * @param  int|null    MTS >= start (ms).
     * @param  int|null    MTS <= end (ms).
     * @param  int|null    Max number of records.
     * @param  int|null    +1 asc, -1 desc.
     * @return PublicBitfinexResponse
     *
     * @throws BitfinexException
     * @throws BitfinexPathNotFoundException
     *
     * @link https://docs.bitfinex.com/reference/rest-public-derivatives-status
     */
    final public function get(string|array|null  = null, ?int  = null, ?int  = null, ?int  = null, ?int  = null): PublicBitfinexResponse
    {
        try {
             = ->url->setPath('public.derivatives_status')->getPath();

             = array_filter([
                'keys' => is_null() ? null : (is_array() ? implode(',', ) : ),
                'start' => ,
                'end' => ,
                'limit' => ,
                'sort' => ,
            ], fn () => ! is_null());

             = ->client->get(, ['query' => ]);

            return (new PublicBitfinexResponse())->derivativesStatus(is_array() ?  : (is_null() ? [] : []));
        } catch (GuzzleException ) {
            throw new BitfinexException(->getMessage(), ->getCode());
        }
    }
}

