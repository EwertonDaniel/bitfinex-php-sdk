<?php

use EwertonDaniel\Bitfinex\Bitfinex;
use EwertonDaniel\Bitfinex\Core\Services\BitfinexPublic;
use EwertonDaniel\Bitfinex\Http\Responses\BitfinexResponse;

test('Can retrieve bitfinex public class', function (Bitfinex $bitfinex) {
    expect($bitfinex->public())->toBeInstanceOf(BitfinexPublic::class);
})->with([
    'Bitfinex Public' => fn () => new Bitfinex,
]);

test('Can retrieve bitfinex platform status', function (Bitfinex $bitfinex) {
    expect($bitfinex->public()->platformStatus())->toBeInstanceOf(BitfinexResponse::class);
})->with([
    'Bitfinex Public' => fn () => new Bitfinex,
]);

test('Can retrieve bitfinex ticker', function (Bitfinex $bitfinex) {
    expect($bitfinex->public()->ticker('EURUSD', 'trading'))->toBeInstanceOf(BitfinexResponse::class);
})->with([
    'Bitfinex Public' => fn () => new Bitfinex,
]);

test('Can retrieve bitfinex ticker cross rate', function (Bitfinex $bitfinex) {
    expect($bitfinex->public()->crossRate('BTCUSD', 'EUR'))->toBeInstanceOf(BitfinexResponse::class);
})->with([
    'Bitfinex Public' => fn () => new Bitfinex,
]);

test('Can retrieve bitfinex tickers', function (Bitfinex $bitfinex) {
    expect($bitfinex->public()->tickers(['EURUSD', 'BTCUSD'], 'trading'))->toBeInstanceOf(BitfinexResponse::class);
})->with([
    'Bitfinex Public' => fn () => new Bitfinex,
]);

test('Can retrieve bitfinex ticker history', function (Bitfinex $bitfinex) {
    expect($bitfinex->public()->tickerHistory(['EURUSD', 'BTCUSD']))->toBeInstanceOf(BitfinexResponse::class);
})->with([
    'Bitfinex Public' => fn () => new Bitfinex,
]);

test('Can retrieve bitfinex trades', function (Bitfinex $bitfinex) {
    expect($bitfinex->public()->trades('BTCUSD', 'trading'))->toBeInstanceOf(BitfinexResponse::class);
})->with([
    'Bitfinex Public' => fn () => new Bitfinex,
]);
