<?php

use EwertonDaniel\Bitfinex\Bitfinex;
use EwertonDaniel\Bitfinex\Core\Services\BitfinexPublic;

test('Can retrieve bitfinex public class', function (Bitfinex $bitfinex) {
    expect($bitfinex->public())->toBeInstanceOf(BitfinexPublic::class);
})->with([
    'Bitfinex Public' => fn() => new Bitfinex(),
]);

test('Can retrieve bitfinex platform status', function (Bitfinex $bitfinex) {
    expect($bitfinex->public()->platformStatus())->toBeArray();
})->with([
    'Bitfinex Public' => fn() => new Bitfinex(),
]);

test('Can retrieve bitfinex ticker', function (Bitfinex $bitfinex) {
    expect($bitfinex->public()->ticker('EURUSD', 'trading'))->toBeArray();
})->with([
    'Bitfinex Public' => fn() => new Bitfinex(),
]);

test('Can retrieve bitfinex tickers', function (Bitfinex $bitfinex) {
    expect($bitfinex->public()->tickers(['EURUSD', 'BTCUSD'], 'trading'))->toBeArray();
})->with([
    'Bitfinex Public' => fn() => new Bitfinex(),
]);

test('Can retrieve bitfinex ticker history', function (Bitfinex $bitfinex) {
    expect($bitfinex->public()->tickerHistory(['EURUSD', 'BTCUSD']))->toBeArray();
})->with([
    'Bitfinex Public' => fn() => new Bitfinex(),
]);

test('Can retrieve bitfinex trades', function (Bitfinex $bitfinex) {
    expect($bitfinex->public()->trades('BTCUSD', 'trading'))->toBeArray();
})->with([
    'Bitfinex Public' => fn() => new Bitfinex(),
]);
