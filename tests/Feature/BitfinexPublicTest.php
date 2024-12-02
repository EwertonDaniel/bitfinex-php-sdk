<?php

use EwertonDaniel\Bitfinex\Bitfinex;
use EwertonDaniel\Bitfinex\Enums\BitfinexType;
use EwertonDaniel\Bitfinex\Http\Responses\PublicBitfinexResponse;
use EwertonDaniel\Bitfinex\Services\BitfinexPublic;

test('Should retrieve bitfinex public class', function (Bitfinex $bitfinex) {
    expect($bitfinex->public())->toBeInstanceOf(BitfinexPublic::class);
})->with(['Bitfinex Public' => fn () => new Bitfinex]);

test('Should retrieve bitfinex platform status', function (Bitfinex $bitfinex) {
    expect($bitfinex->public()->platformStatus())->toBeInstanceOf(PublicBitfinexResponse::class);
})->with('Bitfinex');

test('Should retrieve bitfinex ticker', function (Bitfinex $bitfinex, string $symbol, BitfinexType $type) {
    $expected = $type->isFunding() ? $bitfinex->public()->ticker()->byCurrency($symbol) : $bitfinex->public()->ticker()->byPair($symbol);

    expect($expected)->toBeInstanceOf(PublicBitfinexResponse::class);
})->with('Bitfinex')->with('Pair/Currency and Type');

test('Should retrieve bitfinex tickers', function (Bitfinex $bitfinex, array $symbols, BitfinexType $type) {
    $expected = $type->isFunding() ? $bitfinex->public()->ticker()->byCurrencies($symbols) : $bitfinex->public()->ticker()->byPairs($symbols);

    expect($expected)->toBeInstanceOf(PublicBitfinexResponse::class);
})->with('Bitfinex')->with('Pairs/Currencies and Type');

test('Should retrieve bitfinex ticker history', function (Bitfinex $bitfinex, array $pairs) {
    expect($bitfinex->public()->ticker()->history($pairs))->toBeInstanceOf(PublicBitfinexResponse::class);
})->with('Bitfinex')->with('Pairs');

test('Should retrieve bitfinex trades', function (Bitfinex $bitfinex, $symbol, BitfinexType $type) {
    $trades = $bitfinex->public()->trades();

    $expected = $type->isFunding() ? $trades->byCurrency($symbol) : $trades->byPair($symbol);

    expect($expected)->toBeInstanceOf(PublicBitfinexResponse::class);
})->with('Bitfinex')->with('Pair/Currency and Type');

test('Should retrieve bitfinex book', function (Bitfinex $bitfinex, $symbol, BitfinexType $type) {
    $book = $bitfinex->public()->book();

    $expected = $type->isFunding() ? $book->byCurrency($symbol) : $book->byPair($symbol);

    expect($expected)->toBeInstanceOf(PublicBitfinexResponse::class);
})->with('Bitfinex')->with('Pair/Currency and Type');

test('Should retrieve bitfinex stats', function (Bitfinex $bitfinex, $symbol, BitfinexType $type) {
    $stats = $bitfinex->public()->stats(key: 'pos.size', size: '1m', sidePair: 'long', section: 'hist');
    $expected = $type->isFunding() ? $stats->byCurrency($symbol) : $stats->byPair($symbol);

    expect($expected)->toBeInstanceOf(PublicBitfinexResponse::class);
})->with('Bitfinex')->with('Pair/Currency and Type');

test('Should retrieve candles', function (Bitfinex $bitfinex) {
    /** @todo */
})->with('Bitfinex')->skip();

test('Should retrieve configs', function (Bitfinex $bitfinex) {
    /** @todo */
})->with('Bitfinex')->skip();

test('Should retrieve derivatives status', function (Bitfinex $bitfinex) {
    /** @todo */
})->with('Bitfinex')->skip();

test('Should retrieve derivatives status history', function (Bitfinex $bitfinex) {
    /** @todo */
})->with('Bitfinex')->skip();

test('Should retrieve liquidations', function (Bitfinex $bitfinex) {
    /** @todo */
})->with('Bitfinex')->skip();

test('Should retrieve leaderboards', function (Bitfinex $bitfinex) {
    /** @todo */
})->with('Bitfinex')->skip();

test('Should retrieve funding stats', function (Bitfinex $bitfinex) {
    /** @todo */
})->with('Bitfinex')->skip();

test('Should retrieve market average price', function (Bitfinex $bitfinex) {
    /** @todo */
})->with('Bitfinex')->skip();

test('Should retrieve bitfinex foreign exchange rate', function (Bitfinex $bitfinex, $baseCurrency, $quoteCurrency) {
    expect($bitfinex->public()->foreignExchangeRate($baseCurrency, $quoteCurrency))->toBeInstanceOf(PublicBitfinexResponse::class);
})->with('Bitfinex')->with('Currencies');
