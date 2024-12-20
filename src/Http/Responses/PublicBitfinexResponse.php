<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Http\Responses;

use EwertonDaniel\Bitfinex\Entities\BookFunding;
use EwertonDaniel\Bitfinex\Entities\BookTrading;
use EwertonDaniel\Bitfinex\Entities\CurrencyTrade;
use EwertonDaniel\Bitfinex\Entities\ForeignExchangeRate;
use EwertonDaniel\Bitfinex\Entities\FundingCurrency;
use EwertonDaniel\Bitfinex\Entities\PairTrade;
use EwertonDaniel\Bitfinex\Entities\PlatformStatus;
use EwertonDaniel\Bitfinex\Entities\Stat;
use EwertonDaniel\Bitfinex\Entities\TickerHistory;
use EwertonDaniel\Bitfinex\Entities\TradingPair;
use EwertonDaniel\Bitfinex\Enums\BitfinexType;

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
}
