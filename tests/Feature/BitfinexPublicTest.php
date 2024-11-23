<?php

use EwertonDaniel\Bitfinex\Bitfinex;
use EwertonDaniel\Bitfinex\Http\Responses\BitfinexResponse;
use EwertonDaniel\Bitfinex\Services\BitfinexPublic;

test('Can retrieve bitfinex public class', function (Bitfinex $bitfinex) {
    expect($bitfinex->public())->toBeInstanceOf(BitfinexPublic::class);
})->with(['Bitfinex Public' => fn () => new Bitfinex]);

test('Can retrieve bitfinex platform status', function (Bitfinex $bitfinex) {
    expect($bitfinex->public()->platformStatus())->toBeInstanceOf(BitfinexResponse::class);
})->with(['Bitfinex Public' => fn () => new Bitfinex]);

test('Can retrieve bitfinex ticker', function (Bitfinex $bitfinex, string $symbol, string $type) {
    expect($bitfinex->public()->ticker($symbol, $type))->toBeInstanceOf(BitfinexResponse::class);
})->with(['Bitfinex Public' => fn () => new Bitfinex])->with([
    'Trading' => ['EURUSD', 'trading'],
    'Funding' => ['EUR', 'funding'],
]);

test('Can retrieve bitfinex foreign exchange rate', function (Bitfinex $bitfinex, $baseCurrency, $quoteCurrency) {
    expect($bitfinex->public()->foreignExchangeRate($baseCurrency, $quoteCurrency))->toBeInstanceOf(BitfinexResponse::class);
})->with(['Bitfinex Public' => fn () => new Bitfinex])
    ->with([
        'Euro/Dollar' => ['EUR', 'USD'],
        'Bitcoin/Dollar' => ['BTC', 'USD'],
        'Monero/Euro' => ['XMR', 'EUR'],
        'Ethereum/Dollar' => ['ETH', 'USD'],
    ]);

test('Can retrieve bitfinex tickers', function (Bitfinex $bitfinex) {
    expect($bitfinex->public()->tickers(['EURUSD', 'BTCUSD'], 'trading'))->toBeInstanceOf(BitfinexResponse::class);
})->with(['Bitfinex Public' => fn () => new Bitfinex])->with([
    'Trading' => ['BCHUSD', 'trading'],
    'Funding' => ['EUR', 'funding'],
]);

test('Can retrieve bitfinex ticker history', function (Bitfinex $bitfinex, $pairs) {
    expect($bitfinex->public()->tickerHistory($pairs))->toBeInstanceOf(BitfinexResponse::class);
})->with(['Bitfinex Public' => fn () => new Bitfinex])->with([
    'Euro/Dollar, Bitcoin/Dollar' => [['EURUSD', 'BTCUSD']],
    'Monero/Dollar, Ethereum/Dollar' => [['XMRUSD', 'ETHUSD']],
]);

test('Can retrieve bitfinex trades', function (Bitfinex $bitfinex) {
    expect($bitfinex->public()->trades('BTCUSD', 'trading'))->toBeInstanceOf(BitfinexResponse::class);
})->with(['Bitfinex Public' => fn () => new Bitfinex])->with([
    'Trading' => ['EURUSD', 'trading'],
    'Funding' => ['EUR', 'funding'],
]);
