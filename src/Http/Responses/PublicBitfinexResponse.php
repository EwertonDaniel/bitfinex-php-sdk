<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Http\Responses;

use EwertonDaniel\Bitfinex\Core\Entities\CurrencyTrade;
use EwertonDaniel\Bitfinex\Core\Entities\FundingCurrency;
use EwertonDaniel\Bitfinex\Core\Entities\PairTrade;
use EwertonDaniel\Bitfinex\Core\Entities\PlatformStatus;
use EwertonDaniel\Bitfinex\Core\Entities\TickerHistory;
use EwertonDaniel\Bitfinex\Core\Entities\TradingPair;

class PublicBitfinexResponse extends BitfinexResponse
{
    final public function platformStatus(): BitfinexResponse
    {
        return $this->transformContent(fn ($content) => new PlatformStatus($content));
    }

    final public function tickers(string $type): BitfinexResponse
    {
        return $this->transformContent(
            fn ($tickers) => array_map(
                function ($ticker) use ($type) {
                    $symbol = $ticker[0];
                    array_shift($ticker);

                    return match ($type) {
                        't' => new TradingPair($symbol, $ticker),
                        'f' => new FundingCurrency($symbol, $ticker)
                    };
                },
                $tickers
            )
        );
    }

    final public function tickerHistory(): BitfinexResponse
    {
        return $this->transformContent(
            fn ($tickerHistories) => collect($tickerHistories)
                ->map(fn ($history) => new TickerHistory($history))
                ->groupBy('symbol')
                ->toArray()
        );
    }

    final public function trades(string $symbol, string $type): BitfinexResponse
    {
        return $this->transformContent(
            fn ($trades) => array_map(
                fn ($trade) => match ($type) {
                    't' => new PairTrade($symbol, $trade),
                    'f' => new CurrencyTrade($symbol, $trade)
                },
                $trades
            )
        );
    }

    public function crossRateTicker($symbol, mixed $usdEurTradingPair): BitfinexResponse
    {
        return $this->transformContent(function ($ticker) use ($symbol, $usdEurTradingPair) {
            $tradingPair = new TradingPair($symbol, $ticker);

            return [
                $usdEurTradingPair->pair => $usdEurTradingPair,
                $tradingPair->pair => $tradingPair,
            ];
        });
    }

    final public function ticker(string $symbol, string $type): BitfinexResponse
    {
        return $this->transformContent(
            fn ($ticker) => match ($type) {
                't' => new TradingPair($symbol, $ticker),
                'f' => new FundingCurrency($symbol, $ticker)
            }
        );
    }
}
