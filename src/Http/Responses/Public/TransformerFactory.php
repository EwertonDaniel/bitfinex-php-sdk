<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Http\Responses\Public;

use EwertonDaniel\Bitfinex\Http\Responses\Public\Contracts\PublicTransformer;
use EwertonDaniel\Bitfinex\Http\Responses\Public\Transformers\BookTransformer;
use EwertonDaniel\Bitfinex\Http\Responses\Public\Transformers\CandlesTransformer;
use EwertonDaniel\Bitfinex\Http\Responses\Public\Transformers\DerivativesStatusTransformer;
use EwertonDaniel\Bitfinex\Http\Responses\Public\Transformers\ForeignExchangeRateTransformer;
use EwertonDaniel\Bitfinex\Http\Responses\Public\Transformers\FundingStatsTransformer;
use EwertonDaniel\Bitfinex\Http\Responses\Public\Transformers\LeaderboardsTransformer;
use EwertonDaniel\Bitfinex\Http\Responses\Public\Transformers\LiquidationsTransformer;
use EwertonDaniel\Bitfinex\Http\Responses\Public\Transformers\MarketAveragePriceTransformer;
use EwertonDaniel\Bitfinex\Http\Responses\Public\Transformers\PlatformStatusTransformer;
use EwertonDaniel\Bitfinex\Http\Responses\Public\Transformers\StatsTransformer;
use EwertonDaniel\Bitfinex\Http\Responses\Public\Transformers\TickerHistoryTransformer;
use EwertonDaniel\Bitfinex\Http\Responses\Public\Transformers\TickerTransformer;
use EwertonDaniel\Bitfinex\Http\Responses\Public\Transformers\TickersTransformer;
use EwertonDaniel\Bitfinex\Http\Responses\Public\Transformers\TradesTransformer;

class TransformerFactory
{
    public function make(string $name): PublicTransformer
    {
        return match ($name) {
            'platformStatus' => new PlatformStatusTransformer(),
            'ticker' => new TickerTransformer(),
            'tickers' => new TickersTransformer(),
            'tickerHistory' => new TickerHistoryTransformer(),
            'foreignExchangeRate' => new ForeignExchangeRateTransformer(),
            'trades' => new TradesTransformer(),
            'book' => new BookTransformer(),
            'stats' => new StatsTransformer(),
            'candles' => new CandlesTransformer(),
            'derivativesStatus' => new DerivativesStatusTransformer(),
            'liquidations' => new LiquidationsTransformer(),
            'leaderboards' => new LeaderboardsTransformer(),
            'fundingStats' => new FundingStatsTransformer(),
            'marketAveragePrice' => new MarketAveragePriceTransformer(),
            default => new PlatformStatusTransformer(),
        };
    }
}
