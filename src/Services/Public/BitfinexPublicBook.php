<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Services\Public;

use EwertonDaniel\Bitfinex\Builders\UrlBuilder;
use EwertonDaniel\Bitfinex\Builders\RequestBuilder;
use EwertonDaniel\Bitfinex\Enums\BitfinexType;
use EwertonDaniel\Bitfinex\Enums\BookPrecision;
use EwertonDaniel\Bitfinex\Exceptions\BitfinexException;
use EwertonDaniel\Bitfinex\Helpers\GetThis;
use EwertonDaniel\Bitfinex\Http\Responses\PublicBitfinexResponse;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class BitfinexPublicBook
{
    /**
     * @param  BookPrecision|null  $precision  The precision level for the order book data (e.g., P0, P1).
     * @param  int|null  $length  The maximum number of price levels to retrieve (default: 25).
     */
    public function __construct(
        private readonly Client $client,
        private readonly UrlBuilder $url,
        private ?BookPrecision $precision,
        private readonly ?int $length = 25
    ) {
        $this->precision = GetThis::ifTrueOrFallback($precision, $this->precision, fn () => BookPrecision::R0);
    }

    /**
     * Retrieves the order book data for a specified trading pair or funding currency.
     *
     * This method fetches real-time order book data for a given symbol and precision level.
     * It allows specifying the maximum number of price levels to retrieve.
     *
     * @param  string  $symbol  The trading pair (e.g., tBTCUSD) or funding currency (e.g., fUSD).
     * @param  BitfinexType  $type  The type of market (trading or funding).
     * @return PublicBitfinexResponse Returns a response object containing the requested order book data.
     *
     * @throws BitfinexException If the API request fails or an unexpected error occurs.
     *
     * @link https://docs.bitfinex.com/reference/rest-public-books
     */
    protected function get(string $symbol, BitfinexType $type): PublicBitfinexResponse
    {
        try {
            $apiPath = $this->url->setPath(path: 'public.book', params: ['symbol' => $symbol, 'precision' => $this->precision->name])->getPath();

            $apiResponse = $this->client->get($apiPath, ['query' => ['len' => $this->length]]);

            return (new PublicBitfinexResponse($apiResponse))->book($symbol, $type);
        } catch (GuzzleException $e) {
            throw new BitfinexException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Retrieves the order book data for a specific trading pair.
     *
     * Fetches the order book data for a given trading pair.
     *
     * @param  string  $pair  The trading pair symbol (e.g., tBTCUSD).
     * @return PublicBitfinexResponse Returns a response object containing the requested order book data.
     *
     * @throws BitfinexException If the API request fails or an unexpected error occurs.
     */
    final public function byPair(string $pair): PublicBitfinexResponse
    {
        $type = BitfinexType::TRADING;
        $symbol = $type->symbol($pair);

        return $this->get(symbol: $symbol, type: $type);
    }

    /**
     * Retrieves the order book data for a specific currency.
     *
     * Fetches the order book data for a given currency.
     *
     * @param  string  $currency  The currency symbol (e.g., USD, EUR).
     * @return PublicBitfinexResponse Returns a response object containing the requested order book data.
     *
     * @throws BitfinexException If the API request fails or an unexpected error occurs.
     */
    final public function byCurrency(string $currency): PublicBitfinexResponse
    {
        $type = BitfinexType::FUNDING;
        $symbol = $type->symbol($currency);

        return $this->get(symbol: $symbol, type: $type);
    }
}
