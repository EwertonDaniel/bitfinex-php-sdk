<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Http\Responses;

use EwertonDaniel\Bitfinex\Entities\BookFunding;
use EwertonDaniel\Bitfinex\Entities\BookTrading;
use EwertonDaniel\Bitfinex\Entities\Candle;
use EwertonDaniel\Bitfinex\Entities\Liquidation;
use EwertonDaniel\Bitfinex\Entities\FundingStat;
use EwertonDaniel\Bitfinex\Entities\LeaderboardEntry;
use EwertonDaniel\Bitfinex\Entities\MarketAveragePriceResult;
use EwertonDaniel\Bitfinex\Entities\CurrencyTrade;
use EwertonDaniel\Bitfinex\Entities\ForeignExchangeRate;
use EwertonDaniel\Bitfinex\Entities\FundingCurrency;
use EwertonDaniel\Bitfinex\Entities\PairTrade;
use EwertonDaniel\Bitfinex\Entities\PlatformStatus;
use EwertonDaniel\Bitfinex\Entities\Stat;
use EwertonDaniel\Bitfinex\Entities\TickerHistory;
use EwertonDaniel\Bitfinex\Entities\TradingPair;
use EwertonDaniel\Bitfinex\Enums\BitfinexType;
use Illuminate\Support\Arr;
use EwertonDaniel\Bitfinex\Entities\ConfigEntry;
use EwertonDaniel\Bitfinex\Entities\PairInfo;
use EwertonDaniel\Bitfinex\Entities\TxStatus;
use EwertonDaniel\Bitfinex\Entities\DerivativeStatus;
use EwertonDaniel\Bitfinex\Helpers\GetThis;

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
            $t = GetThis::ifTrueOrFallback(
            boolean: function_exists('app'),
            callback: fn () => app(\EwertonDaniel\Bitfinex\Http\Responses\Public\TransformerFactory::class),
            fallback: fn () => new \EwertonDaniel\Bitfinex\Http\Responses\Public\TransformerFactory()
        )->make('platformStatus');
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
            $t = GetThis::ifTrueOrFallback(
            boolean: function_exists('app'),
            callback: fn () => app(\EwertonDaniel\Bitfinex\Http\Responses\Public\TransformerFactory::class),
            fallback: fn () => new \EwertonDaniel\Bitfinex\Http\Responses\Public\TransformerFactory()
        )->make('ticker');
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
            $t = GetThis::ifTrueOrFallback(
            boolean: function_exists('app'),
            callback: fn () => app(\EwertonDaniel\Bitfinex\Http\Responses\Public\TransformerFactory::class),
            fallback: fn () => new \EwertonDaniel\Bitfinex\Http\Responses\Public\TransformerFactory()
        )->make('tickers');
            return $t->transform(['type' => $type], $content);
        });
    }

    /**
     * Transforms ticker history into grouped collections of `TickerHistory` entities.
     */
    final public function tickerHistory(): PublicBitfinexResponse
    {
        return $this->transformContent(function ($content) {
            $t = GetThis::ifTrueOrFallback(
            boolean: function_exists('app'),
            callback: fn () => app(\EwertonDaniel\Bitfinex\Http\Responses\Public\TransformerFactory::class),
            fallback: fn () => new \EwertonDaniel\Bitfinex\Http\Responses\Public\TransformerFactory()
        )->make('tickerHistory');
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
            $t = GetThis::ifTrueOrFallback(
            boolean: function_exists('app'),
            callback: fn () => app(\EwertonDaniel\Bitfinex\Http\Responses\Public\TransformerFactory::class),
            fallback: fn () => new \EwertonDaniel\Bitfinex\Http\Responses\Public\TransformerFactory()
        )->make('foreignExchangeRate');
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
            $factory = GetThis::ifTrueOrFallback(
                boolean: function_exists('app'),
                callback: fn () => app(\EwertonDaniel\Bitfinex\Http\Responses\Public\TransformerFactory::class),
                fallback: fn () => new \EwertonDaniel\Bitfinex\Http\Responses\Public\TransformerFactory()
            );
            $t = $factory->make('trades');
            return $t->transform(['symbol' => $symbol, 'type' => $type], $content);
        });
    }

    /**
     * Transforms book data into either `BookTrading` or `BookFunding` entities.
     *
     * @param  string  $symbol  The symbol of the book data.
     * @param  BitfinexType  $type  The type of book data (TRADING or FUNDING).
     */
    final public function book(string $symbol, BitfinexType $type): PublicBitfinexResponse
    {
        return $this->transformContent(function ($content) use ($symbol, $type) {
            $t = GetThis::ifTrueOrFallback(
            boolean: function_exists('app'),
            callback: fn () => app(\EwertonDaniel\Bitfinex\Http\Responses\Public\TransformerFactory::class),
            fallback: fn () => new \EwertonDaniel\Bitfinex\Http\Responses\Public\TransformerFactory()
        )->make('book');
            return $t->transform(['symbol' => $symbol, 'type' => $type], $content);
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
    final public function stats(string $key, string $size, string $symPlatform, string $sidePair, string $section): PublicBitfinexResponse
    {
        return $this->transformContent(function ($content) use ($key, $size, $symPlatform, $sidePair, $section) {
            $t = GetThis::ifTrueOrFallback(
            boolean: function_exists('app'),
            callback: fn () => app(\EwertonDaniel\Bitfinex\Http\Responses\Public\TransformerFactory::class),
            fallback: fn () => new \EwertonDaniel\Bitfinex\Http\Responses\Public\TransformerFactory()
        )->make('stats');
            return $t->transform(compact('key','size','symPlatform','sidePair','section'), $content);
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
            $manager = GetThis::ifTrueOrFallback(
                boolean: function_exists('app'),
                callback: fn () => app(\EwertonDaniel\Bitfinex\Http\Responses\Configs\ConfigsTransformer::class),
                fallback: fn () => new \EwertonDaniel\Bitfinex\Http\Responses\Configs\ConfigsTransformer()
            );

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
            $t = GetThis::ifTrueOrFallback(
            boolean: function_exists('app'),
            callback: fn () => app(\EwertonDaniel\Bitfinex\Http\Responses\Public\TransformerFactory::class),
            fallback: fn () => new \EwertonDaniel\Bitfinex\Http\Responses\Public\TransformerFactory()
        )->make('candles');
            return $t->transform(compact('symbol','timeframe','section'), $content);
        });
    }

    /**
     * Transforms derivatives status responses.
     *
     * @param  array  $keys  Keys requested from status endpoint.
     */
    final public function derivativesStatus(array $keys): PublicBitfinexResponse
    {
        return $this->transformContent(function ($content) use ($keys) {
            $t = GetThis::ifTrueOrFallback(
            boolean: function_exists('app'),
            callback: fn () => app(\EwertonDaniel\Bitfinex\Http\Responses\Public\TransformerFactory::class),
            fallback: fn () => new \EwertonDaniel\Bitfinex\Http\Responses\Public\TransformerFactory()
        )->make('derivativesStatus');
            return $t->transform(['keys' => $keys], $content);
        });
    }

    /**
     * Transforms liquidations into Liquidation entities.
     */
    final public function liquidations(): PublicBitfinexResponse
    {
        return $this->transformContent(function ($content) {
            $t = GetThis::ifTrueOrFallback(
            boolean: function_exists('app'),
            callback: fn () => app(\EwertonDaniel\Bitfinex\Http\Responses\Public\TransformerFactory::class),
            fallback: fn () => new \EwertonDaniel\Bitfinex\Http\Responses\Public\TransformerFactory()
        )->make('liquidations');
            return $t->transform([], $content);
        });
    }

    /**
     * Transforms leaderboards response.
     */
    final public function leaderboards(string $key, string $timeframe, string $symbol, string $section): PublicBitfinexResponse
    {
        return $this->transformContent(function ($content) use ($key, $timeframe, $symbol, $section) {
            $t = GetThis::ifTrueOrFallback(
            boolean: function_exists('app'),
            callback: fn () => app(\EwertonDaniel\Bitfinex\Http\Responses\Public\TransformerFactory::class),
            fallback: fn () => new \EwertonDaniel\Bitfinex\Http\Responses\Public\TransformerFactory()
        )->make('leaderboards');
            return $t->transform(compact('key','timeframe','symbol','section'), $content);
        });
    }

    /**
     * Transforms funding statistics response.
     */
    final public function fundingStats(string $symbol): PublicBitfinexResponse
    {
        return $this->transformContent(function ($content) use ($symbol) {
            $t = GetThis::ifTrueOrFallback(
            boolean: function_exists('app'),
            callback: fn () => app(\EwertonDaniel\Bitfinex\Http\Responses\Public\TransformerFactory::class),
            fallback: fn () => new \EwertonDaniel\Bitfinex\Http\Responses\Public\TransformerFactory()
        )->make('fundingStats');
            return $t->transform(['symbol' => $symbol], $content);
        });
    }

    /**
     * Transforms market average price calculation result.
     */
    final public function marketAveragePrice(): PublicBitfinexResponse
    {
        return $this->transformContent(function ($content) {
            $t = GetThis::ifTrueOrFallback(
            boolean: function_exists('app'),
            callback: fn () => app(\EwertonDaniel\Bitfinex\Http\Responses\Public\TransformerFactory::class),
            fallback: fn () => new \EwertonDaniel\Bitfinex\Http\Responses\Public\TransformerFactory()
        )->make('marketAveragePrice');
            return $t->transform([], $content);
        });
    }
}
