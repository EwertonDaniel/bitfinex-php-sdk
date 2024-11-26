<?php

use EwertonDaniel\Bitfinex\Bitfinex;
use EwertonDaniel\Bitfinex\Enums\BitfinexType;
use EwertonDaniel\Bitfinex\Enums\BookPrecision;
use EwertonDaniel\Bitfinex\Http\Responses\PublicBitfinexResponse;
use EwertonDaniel\Bitfinex\Services\BitfinexPublic;

test('Should retrieve bitfinex public class', function (Bitfinex $bitfinex) {
    expect($bitfinex->public())->toBeInstanceOf(BitfinexPublic::class);
})->with(['Bitfinex Public' => fn () => new Bitfinex]);

test('Should retrieve bitfinex platform status', function (Bitfinex $bitfinex) {
    expect($bitfinex->public()->platformStatus())
        ->toBeInstanceOf(PublicBitfinexResponse::class);
})->with('Bitfinex');

test('Should retrieve bitfinex ticker', function (Bitfinex $bitfinex, string $symbol, BitfinexType $type) {
    expect($bitfinex->public()->ticker($symbol, $type))
        ->toBeInstanceOf(PublicBitfinexResponse::class);
})->with('Bitfinex')->with('Pair and Type');

test('Should retrieve bitfinex tickers', function (Bitfinex $bitfinex, array $pairs, BitfinexType $type) {
    expect($bitfinex->public()->tickers($pairs, $type))
        ->toBeInstanceOf(PublicBitfinexResponse::class);
})->with('Bitfinex')->with('Pairs and Type');

test('Should retrieve bitfinex ticker history', function (Bitfinex $bitfinex, array $pairs) {
    expect($bitfinex->public()->tickerHistory($pairs))
        ->toBeInstanceOf(PublicBitfinexResponse::class);
})->with('Bitfinex')->with('Pairs');

test('Should retrieve bitfinex trades', function (Bitfinex $bitfinex, $symbol, BitfinexType $type) {
    expect($bitfinex->public()->trades($symbol, $type))
        ->toBeInstanceOf(PublicBitfinexResponse::class);
})->with('Bitfinex')->with('Pair and Type');

test('Should retrieve bitfinex book', function (Bitfinex $bitfinex, $symbol, BitfinexType $type) {
    expect($bitfinex->public()->book($symbol, $type, BookPrecision::R0))
        ->toBeInstanceOf(PublicBitfinexResponse::class);
})->with('Bitfinex')->with('Pair and Type');

test('Should retrieve bitfinex stats', function (Bitfinex $bitfinex) {
    expect($bitfinex->public()->stats(key: 'pos.size', size: '1m', type: BitfinexType::TRADING, pair: 'BTCUSD', sidePair: 'long', section: 'hist'))
        ->toBeInstanceOf(PublicBitfinexResponse::class);
})->with('Bitfinex');

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
    expect($bitfinex->public()->foreignExchangeRate($baseCurrency, $quoteCurrency))
        ->toBeInstanceOf(PublicBitfinexResponse::class);
})->with('Bitfinex')->with('Currencies');
