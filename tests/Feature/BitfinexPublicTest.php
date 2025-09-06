<?php

use EwertonDaniel\Bitfinex\Bitfinex;
use EwertonDaniel\Bitfinex\Enums\BitfinexType;
use EwertonDaniel\Bitfinex\Http\Responses\PublicBitfinexResponse;
use EwertonDaniel\Bitfinex\Services\BitfinexPublic;
use EwertonDaniel\Bitfinex\Entities\TradingPair;
use EwertonDaniel\Bitfinex\Entities\FundingCurrency;
use EwertonDaniel\Bitfinex\Entities\PairTrade;
use EwertonDaniel\Bitfinex\Entities\CurrencyTrade;
use EwertonDaniel\Bitfinex\Entities\BookTrading;
use EwertonDaniel\Bitfinex\Entities\BookFunding;
use EwertonDaniel\Bitfinex\Entities\Stat as StatEntity;
use EwertonDaniel\Bitfinex\Entities\Candle as CandleEntity;
use EwertonDaniel\Bitfinex\Entities\ConfigEntry;
use EwertonDaniel\Bitfinex\Entities\DerivativeStatus as DerivativeStatusEntity;
use EwertonDaniel\Bitfinex\Entities\LeaderboardEntry;
use EwertonDaniel\Bitfinex\Entities\FundingStat as FundingStatEntity;
use EwertonDaniel\Bitfinex\Entities\MarketAveragePriceResult;
use EwertonDaniel\Bitfinex\Entities\ForeignExchangeRate;

test('Should retrieve bitfinex public class', function (Bitfinex $bitfinex) {
    expect($bitfinex->public())->toBeInstanceOf(BitfinexPublic::class);
})->with(['Bitfinex Public' => fn () => new Bitfinex]);

test('Should retrieve bitfinex platform status', function (Bitfinex $bitfinex) {
    expect($bitfinex->public()->platformStatus())->toBeInstanceOf(PublicBitfinexResponse::class);
})->with('Bitfinex');

test('Should retrieve bitfinex ticker', function (Bitfinex $bitfinex, string $symbol, BitfinexType $type) {
    $expected = $type->isFunding() ? $bitfinex->public()->ticker()->byCurrency($symbol) : $bitfinex->public()->ticker()->byPair($symbol);

    expect($expected)->toBeInstanceOf(PublicBitfinexResponse::class);
    $ticker = $expected->content['ticker'] ?? null;
    if ($type->isFunding()) {
        expect($ticker)->toBeInstanceOf(FundingCurrency::class);
    } else {
        expect($ticker)->toBeInstanceOf(TradingPair::class);
    }
})->with('Bitfinex')->with('Pair/Currency and Type');

test('Should retrieve bitfinex tickers', function (Bitfinex $bitfinex, array $symbols, BitfinexType $type) {
    $expected = $type->isFunding() ? $bitfinex->public()->ticker()->byCurrencies($symbols) : $bitfinex->public()->ticker()->byPairs($symbols);

    expect($expected)->toBeInstanceOf(PublicBitfinexResponse::class);
    $items = $expected->content['tickers'] ?? [];
    expect($items)->toBeArray();
    if (!empty($items)) {
        if ($type->isFunding()) {
            expect($items[0])->toBeInstanceOf(FundingCurrency::class);
        } else {
            expect($items[0])->toBeInstanceOf(TradingPair::class);
        }
    }
})->with('Bitfinex')->with('Pairs/Currencies and Type');

test('Should retrieve bitfinex ticker history', function (Bitfinex $bitfinex, array $pairs) {
    expect($bitfinex->public()->ticker()->history($pairs))->toBeInstanceOf(PublicBitfinexResponse::class);
})->with('Bitfinex')->with('Pairs');

test('Should retrieve bitfinex trades', function (Bitfinex $bitfinex, $symbol, BitfinexType $type) {
    $trades = $bitfinex->public()->trades();

    $expected = $type->isFunding() ? $trades->byCurrency($symbol) : $trades->byPair($symbol);

    expect($expected)->toBeInstanceOf(PublicBitfinexResponse::class);
    $trades = $expected->content['trades'] ?? [];
    expect($trades)->toBeArray();
    if (!empty($trades)) {
        if ($type->isFunding()) {
            expect($trades[0])->toBeInstanceOf(CurrencyTrade::class);
        } else {
            expect($trades[0])->toBeInstanceOf(PairTrade::class);
        }
    }
})->with('Bitfinex')->with('Pair/Currency and Type');

test('Should retrieve bitfinex book', function (Bitfinex $bitfinex, $symbol, BitfinexType $type) {
    $book = $bitfinex->public()->book();

    $expected = $type->isFunding() ? $book->byCurrency($symbol) : $book->byPair($symbol);

    expect($expected)->toBeInstanceOf(PublicBitfinexResponse::class);
    $books = $expected->content['books'] ?? [];
    expect($books)->toBeArray();
    if (!empty($books)) {
        if ($type->isFunding()) {
            expect($books[0])->toBeInstanceOf(BookFunding::class);
        } else {
            expect($books[0])->toBeInstanceOf(BookTrading::class);
        }
    }
})->with('Bitfinex')->with('Pair/Currency and Type');

test('Should retrieve bitfinex stats', function (Bitfinex $bitfinex, $symbol, BitfinexType $type) {
    $stats = $bitfinex->public()->stats(key: 'pos.size', size: '1m', sidePair: 'long', section: 'hist');
    $expected = $type->isFunding() ? $stats->byCurrency($symbol) : $stats->byPair($symbol);

    expect($expected)->toBeInstanceOf(PublicBitfinexResponse::class);
    $stats = $expected->content['stats'] ?? [];
    expect($stats)->toBeArray();
    if (!empty($stats)) {
        expect($stats[0])->toBeInstanceOf(StatEntity::class);
    }
})->with('Bitfinex')->with('Pair/Currency and Type');

test('Should retrieve candles', function (Bitfinex $bitfinex) {
    $resp = $bitfinex->public()->candles('1m')->byPair('XMR', limit: 5);
    expect($resp)->toBeInstanceOf(PublicBitfinexResponse::class);
    $candles = $resp->content['candles'] ?? [];
    expect($candles)->toBeArray();
    if (!empty($candles)) {
        expect($candles[0])->toBeInstanceOf(CandleEntity::class);
    }
})->with('Bitfinex');

test('Should retrieve configs', function (Bitfinex $bitfinex) {
    $resp = $bitfinex->public()->configs()->get(['pub:map:currency:sym']);
    expect($resp)->toBeInstanceOf(PublicBitfinexResponse::class);
    $configs = $resp->content['configs'] ?? [];
    expect($configs)->toBeArray();
    if (!empty($configs)) {
        expect($configs[0])->toBeInstanceOf(ConfigEntry::class);
    }
})->with('Bitfinex');

test('Should retrieve derivatives status', function (Bitfinex $bitfinex) {
    $resp = $bitfinex->public()->derivativesStatus()->get();
    expect($resp)->toBeInstanceOf(PublicBitfinexResponse::class);
    $items = $resp->content['items'] ?? [];
    expect($items)->toBeArray();
    if (!empty($items)) {
        expect($items[0])->toBeInstanceOf(DerivativeStatusEntity::class);
    }
})->with('Bitfinex');

test('Should retrieve derivatives status history', function (Bitfinex $bitfinex) {
    $resp = $bitfinex->public()->derivativesStatusHistory()->get(start: 1700000000000, end: 1700100000000, limit: 5, sort: -1);
    expect($resp)->toBeInstanceOf(PublicBitfinexResponse::class);
})->with('Bitfinex');

test('Should retrieve liquidations', function (Bitfinex $bitfinex) {
    $resp = $bitfinex->public()->liquidations()->get(limit: 5);
    expect($resp)->toBeInstanceOf(PublicBitfinexResponse::class);
})->with('Bitfinex');

test('Should retrieve leaderboards', function (Bitfinex $bitfinex) {
    $resp = $bitfinex->public()->leaderboards('pnl', '1D')->byPair('BTCUSD', limit: 5);
    expect($resp)->toBeInstanceOf(PublicBitfinexResponse::class);
    $items = $resp->content['items'] ?? [];
    expect($items)->toBeArray();
    if (!empty($items)) {
        expect($items[0])->toBeInstanceOf(LeaderboardEntry::class);
    }
})->with('Bitfinex');

test('Should retrieve funding stats', function (Bitfinex $bitfinex) {
    $resp = $bitfinex->public()->fundingStats()->byCurrency('USD', limit: 5);
    expect($resp)->toBeInstanceOf(PublicBitfinexResponse::class);
    $items = $resp->content['items'] ?? [];
    expect($items)->toBeArray();
    if (!empty($items)) {
        expect($items[0])->toBeInstanceOf(FundingStatEntity::class);
    }
})->with('Bitfinex');

test('Should retrieve market average price', function (Bitfinex $bitfinex) {
    $resp = $bitfinex->public()->marketAveragePrice(['symbol' => 'tBTCUSD', 'amount' => '0.01']);
    expect($resp)->toBeInstanceOf(PublicBitfinexResponse::class);
    expect($resp->content['result'] ?? null)->toBeInstanceOf(MarketAveragePriceResult::class);
})->with('Bitfinex');

test('Should retrieve bitfinex foreign exchange rate', function (Bitfinex $bitfinex, $baseCurrency, $quoteCurrency) {
    $resp = $bitfinex->public()->foreignExchangeRate($baseCurrency, $quoteCurrency);
    expect($resp)->toBeInstanceOf(PublicBitfinexResponse::class);
    expect($resp->content)->toBeInstanceOf(ForeignExchangeRate::class);
})->with('Bitfinex')->with('Currencies');
