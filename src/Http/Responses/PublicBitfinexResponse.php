<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Http\Responses;

use EwertonDaniel\Bitfinex\Entities\BookFunding;
use EwertonDaniel\Bitfinex\Entities\BookTrading;
use EwertonDaniel\Bitfinex\Entities\Candle;
use EwertonDaniel\Bitfinex\Entities\CurrencyTrade;
use EwertonDaniel\Bitfinex\Entities\ForeignExchangeRate;
use EwertonDaniel\Bitfinex\Entities\FundingCurrency;
use EwertonDaniel\Bitfinex\Entities\Liquidation;
use EwertonDaniel\Bitfinex\Entities\PairTrade;
use EwertonDaniel\Bitfinex\Entities\PlatformStatus;
use EwertonDaniel\Bitfinex\Entities\Stat;
use EwertonDaniel\Bitfinex\Entities\TickerHistory;
use EwertonDaniel\Bitfinex\Entities\TradingPair;
use EwertonDaniel\Bitfinex\Enums\BitfinexType;
use EwertonDaniel\Bitfinex\Enums\BookPrecision;
use EwertonDaniel\Bitfinex\Http\Responses\Configs\ConfigsTransformer;
use EwertonDaniel\Bitfinex\Http\Responses\Public\Contracts\PublicTransformer;
use EwertonDaniel\Bitfinex\Http\Responses\Public\TransformerFactory;
use Illuminate\Container\Container;

/**
 * Class PublicBitfinexResponse
 *
 * Handles responses from the public Bitfinex API endpoints.
 * Delegates transformation logic to dedicated transformers (Strategy via Factory),
 * producing entities or collections ready for consumption.
 *
 * Key Features:
 * - Parses response data into domain-specific entities like `TradingPair`, `PlatformStatus`, or `TickerHistory`.
 * - Supports various public API endpoints, including tickers, trades, books, stats, and more.
 * - Leverages Laravel's collections for advanced data transformations where applicable.
 *
 * @author Ewerton Daniel
 *
 * @contact contact@ewertondaniel.work
 */
class PublicBitfinexResponse extends BitfinexResponse
{
    /**
     * Transforms response content into a `PlatformStatus` entity.
     */
    final public function platformStatus(): PublicBitfinexResponse
    {
        return $this->transformContent(function ($content) {
            $t = $this->transformer('platformStatus');

            return $t->transform([], $content);
        });
    }

    /**
     * Transforms a single ticker into either a `TradingPair` or `FundingCurrency` entity.
     *
     * @param  string  $symbol  The symbol of the ticker.
     * @param  BitfinexType  $type  The type of the ticker (TRADING or FUNDING).
     */
    final public function ticker(string $symbol, BitfinexType $type): PublicBitfinexResponse
    {
        return $this->transformContent(function ($content) use ($symbol, $type) {
            $t = $this->transformer('ticker');

            return $t->transform(['symbol' => $symbol, 'type' => $type], $content);
        });
    }

    /**
     * Transforms a list of tickers into entities grouped by type.
     *
     * @param  BitfinexType  $type  The type of tickers (TRADING or FUNDING).
     */
    final public function tickers(BitfinexType $type): PublicBitfinexResponse
    {
        return $this->transformContent(function ($content) use ($type) {
            $t = $this->transformer('tickers');

            return $t->transform(['type' => $type], $content);
        });
    }

    /**
     * Transforms ticker history into grouped collections of `TickerHistory` entities.
     */
    final public function tickerHistory(): PublicBitfinexResponse
    {
        return $this->transformContent(function ($content) {
            $t = $this->transformer('tickerHistory');

            return $t->transform([], $content);
        });
    }

    /**
     * Transforms foreign exchange rate data into a `ForeignExchangeRate` entity.
     *
     * @param  string  $in  Base currency.
     * @param  string  $out  Quote currency.
     */
    public function foreignExchangeRate(string $in, string $out): PublicBitfinexResponse
    {
        return $this->transformContent(function ($content) use ($in, $out) {
            $t = $this->transformer('foreignExchangeRate');

            return $t->transform(['in' => $in, 'out' => $out], $content);
        });
    }

    /**
     * Transforms trades data into either `PairTrade` or `CurrencyTrade` entities.
     *
     * @param  string  $symbol  The symbol of the trades.
     * @param  BitfinexType  $type  The type of trades (TRADING or FUNDING).
     */
    final public function trades(string $symbol, BitfinexType $type): PublicBitfinexResponse
    {
        return $this->transformContent(function ($content) use ($symbol, $type) {
            $t = $this->transformer('trades');

            return $t->transform(['symbol' => $symbol, 'type' => $type], $content);
        });
    }

    /**
     * Transforms book data into either `BookTrading` or `BookFunding` entities.
     *
     * @param  string  $symbol  The symbol of the book data.
     * @param  BitfinexType  $type  The type of book data (TRADING or FUNDING).
     */
    final public function book(string $symbol, BitfinexType $type, ?BookPrecision $precision = null): PublicBitfinexResponse
    {
        return $this->transformContent(function ($content) use ($symbol, $type, $precision) {
            $t = $this->transformer('book');

            return $t->transform(['symbol' => $symbol, 'type' => $type, 'precision' => $precision], $content);
        });
    }

    /**
     * Transforms stats data into `Stat` entities with metadata.
     *
     * @param  string  $key  The stat key.
     * @param  string  $size  The stat size.
     * @param  string  $symPlatform  The symbol platform.
     * @param  string  $sidePair  The side pair.
     * @param  string  $section  The section.
     */
    final public function stats(string $key, string $size, string $symPlatform, ?string $sidePair, string $section): PublicBitfinexResponse
    {
        return $this->transformContent(function ($content) use ($key, $size, $symPlatform, $sidePair, $section) {
            $t = $this->transformer('stats');

            return $t->transform(compact('key', 'size', 'symPlatform', 'sidePair', 'section'), $content);
        });
    }

    /**
     * Transforms configuration responses.
     *
     * @param  array  $keys  Keys requested from conf endpoint.
     */
    final public function configs(array $keys): PublicBitfinexResponse
    {
        return $this->transformContent(function ($content) use ($keys) {
            $manager = Container::getInstance()->make(ConfigsTransformer::class);

            return $manager->transform($keys, $content);
        });
    }

    /**
     * Transforms candles into Candle entities with metadata.
     *
     * @param  string  $symbol  Trading pair (e.g., tBTCUSD) or funding currency (e.g., fUSD).
     * @param  string  $timeframe  Timeframe (e.g., 1m, 5m, 1h, 1D).
     * @param  string  $section  'hist' or 'last'.
     */
    final public function candles(string $symbol, string $timeframe, string $section): PublicBitfinexResponse
    {
        return $this->transformContent(function ($content) use ($symbol, $timeframe, $section) {
            $t = $this->transformer('candles');

            return $t->transform(compact('symbol', 'timeframe', 'section'), $content);
        });
    }

    /**
     * Transforms derivatives status responses.
     *
     * @param  array  $keys  Keys requested from status endpoint.
     */
    final public function derivativesStatus(array $keys, bool $history = false): PublicBitfinexResponse
    {
        return $this->transformContent(function ($content) use ($keys, $history) {
            $t = $this->transformer('derivativesStatus');

            return $t->transform(['keys' => $keys, 'history' => $history], $content);
        });
    }

    /**
     * Transforms liquidations into Liquidation entities.
     */
    final public function liquidations(): PublicBitfinexResponse
    {
        return $this->transformContent(function ($content) {
            $t = $this->transformer('liquidations');

            return $t->transform([], $content);
        });
    }

    /**
     * Transforms leaderboards response.
     */
    final public function leaderboards(string $key, string $timeframe, string $symbol, string $section): PublicBitfinexResponse
    {
        return $this->transformContent(function ($content) use ($key, $timeframe, $symbol, $section) {
            $t = $this->transformer('leaderboards');

            return $t->transform(compact('key', 'timeframe', 'symbol', 'section'), $content);
        });
    }

    /**
     * Transforms funding statistics response.
     */
    final public function fundingStats(string $symbol): PublicBitfinexResponse
    {
        return $this->transformContent(function ($content) use ($symbol) {
            $t = $this->transformer('fundingStats');

            return $t->transform(['symbol' => $symbol], $content);
        });
    }

    /**
     * Transforms market average price calculation result.
     */
    final public function marketAveragePrice(): PublicBitfinexResponse
    {
        return $this->transformContent(function ($content) {
            $t = $this->transformer('marketAveragePrice');

            return $t->transform([], $content);
        });
    }

    /**
     * Resolves the transformer registered under the given name.
     *
     * Resolution goes through the container so a host application can rebind
     * `TransformerFactory`. Outside Laravel the container has no binding and
     * builds the default factory, which keeps the SDK usable standalone.
     *
     * @param  string  $name  Transformer name understood by `TransformerFactory::make()`.
     */
    private function transformer(string $name): PublicTransformer
    {
        return Container::getInstance()->make(TransformerFactory::class)->make($name);
    }
}
