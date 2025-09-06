<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Http\Responses;

use EwertonDaniel\Bitfinex\Entities\BookFunding;
use EwertonDaniel\Bitfinex\Entities\BookTrading;
use EwertonDaniel\Bitfinex\Entities\Candle;
use EwertonDaniel\Bitfinex\Entities\Liquidation;
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
use EwertonDaniel\Bitfinex\Entities\DerivativeStatus;

/**
 * Class PublicBitfinexResponse
 *
 * Handles responses from the public Bitfinex API endpoints.
 * Provides methods to parse and transform response content into specific entities or collections of entities.
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
        return $this->transformContent(fn ($content) => new PlatformStatus($content));
    }

    /**
     * Transforms a single ticker into either a `TradingPair` or `FundingCurrency` entity.
     *
     * @param  string  $symbol  The symbol of the ticker.
     * @param  BitfinexType  $type  The type of the ticker (TRADING or FUNDING).
     */
    final public function ticker(string $symbol, BitfinexType $type): PublicBitfinexResponse
    {
        return $this->transformContent(
            fn ($ticker) => [
                'ticker' => match ($type) {
                    BitfinexType::TRADING => new TradingPair($symbol, $ticker),
                    BitfinexType::FUNDING => new FundingCurrency($symbol, $ticker),
                },
            ]
        );
    }

    /**
     * Transforms a list of tickers into entities grouped by type.
     *
     * @param  BitfinexType  $type  The type of tickers (TRADING or FUNDING).
     */
    final public function tickers(BitfinexType $type): PublicBitfinexResponse
    {
        return $this->transformContent(
            fn ($tickers) => [
                'tickers' => array_map(
                    function ($ticker) use ($type) {
                        $symbol = $ticker[0];
                        array_shift($ticker);

                        return match ($type) {
                            BitfinexType::TRADING => new TradingPair($symbol, $ticker),
                            BitfinexType::FUNDING => new FundingCurrency($symbol, $ticker),
                        };
                    },
                    $tickers
                ),
            ]
        );
    }

    /**
     * Transforms ticker history into grouped collections of `TickerHistory` entities.
     */
    final public function tickerHistory(): PublicBitfinexResponse
    {
        return $this->transformContent(
            fn ($tickerHistories) => collect($tickerHistories)
                ->map(fn ($history) => new TickerHistory($history))
                ->groupBy('pair')
                ->toArray()
        );
    }

    /**
     * Transforms foreign exchange rate data into a `ForeignExchangeRate` entity.
     *
     * @param  string  $in  Base currency.
     * @param  string  $out  Quote currency.
     */
    public function foreignExchangeRate(string $in, string $out): PublicBitfinexResponse
    {
        return $this->transformContent(fn ($rate) => new ForeignExchangeRate($in, $out, $rate));
    }

    /**
     * Transforms trades data into either `PairTrade` or `CurrencyTrade` entities.
     *
     * @param  string  $symbol  The symbol of the trades.
     * @param  BitfinexType  $type  The type of trades (TRADING or FUNDING).
     */
    final public function trades(string $symbol, BitfinexType $type): PublicBitfinexResponse
    {
        return $this->transformContent(
            fn ($content) => [
                'symbol' => $symbol,
                'trades' => array_map(
                    fn ($trade) => match ($type) {
                        BitfinexType::TRADING => new PairTrade($symbol, $trade),
                        BitfinexType::FUNDING => new CurrencyTrade($symbol, $trade),
                    },
                    $content
                ),
            ]
        );
    }

    /**
     * Transforms book data into either `BookTrading` or `BookFunding` entities.
     *
     * @param  string  $symbol  The symbol of the book data.
     * @param  BitfinexType  $type  The type of book data (TRADING or FUNDING).
     */
    final public function book(string $symbol, BitfinexType $type): PublicBitfinexResponse
    {
        return $this->transformContent(
            fn ($content) => [
                'symbol' => $symbol,
                'books' => array_map(
                    fn ($book) => match ($type) {
                        BitfinexType::TRADING => new BookTrading($symbol, $book),
                        BitfinexType::FUNDING => new BookFunding($symbol, $book),
                    },
                    $content
                ),
            ]
        );
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
        return $this->transformContent(
            fn ($content) => [
                'key' => $key,
                'size' => $size,
                'symPlatform' => $symPlatform,
                'sidePair' => $sidePair,
                'section' => $section,
                'stats' => array_map(fn ($data) => new Stat($data), $content),
            ]
        );
    }

    /**
     * Transforms configuration responses.
     *
     * @param  array  $keys  Keys requested from conf endpoint.
     */
    final public function configs(array $keys): PublicBitfinexResponse
    {
        return $this->transformContent(function ($content) use ($keys) {
            $entries = [];
            foreach ($keys as $i => $key) {
                if (array_key_exists($i, $content) && $content[$i] !== null) {
                    $entries[] = new ConfigEntry($key, $content[$i]);
                }
            }

            return ['configs' => $entries];
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
        return $this->transformContent(
            fn ($content) => [
                'symbol' => $symbol,
                'timeframe' => $timeframe,
                'section' => $section,
                'candles' => is_array($content) && isset($content[0]) && is_array($content[0])
                    ? array_map(fn ($row) => new Candle($row), $content)
                    : [new Candle($content)],
            ]
        );
    }

    /**
     * Transforms derivatives status responses.
     *
     * @param  array  $keys  Keys requested from status endpoint.
     */
    final public function derivativesStatus(array $keys): PublicBitfinexResponse
    {
        return $this->transformContent(
            fn ($content) => [
                'keys' => $keys,
                'items' => array_map(fn ($row) => new DerivativeStatus($row), $content),
            ]
        );
    }

    /**
     * Transforms liquidations into Liquidation entities.
     */
    final public function liquidations(): PublicBitfinexResponse
    {
        return $this->transformContent(
            fn ($content) => ['liquidations' => array_map(fn ($row) => new Liquidation($row), $content)]
        );
    }

    /**
     * Transforms leaderboards response.
     */
    final public function leaderboards(string $key, string $timeframe, string $symbol, string $section): PublicBitfinexResponse
    {
        return $this->transformContent(
            fn ($content) => [
                'key' => $key,
                'timeframe' => $timeframe,
                'symbol' => $symbol,
                'section' => $section,
                'items' => $content,
            ]
        );
    }

    /**
     * Transforms funding statistics response.
     */
    final public function fundingStats(string $symbol): PublicBitfinexResponse
    {
        return $this->transformContent(
            fn ($content) => [
                'symbol' => $symbol,
                'items' => $content,
            ]
        );
    }
}
