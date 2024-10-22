<?php

use EwertonDaniel\Bitfinex\Core\Services\BitfinexPublic;
use EwertonDaniel\Bitfinex\Facades\Bitfinex;

test('Can retrieve bitfinex public class', function (BitfinexPublic $bitfinexPublic) {
    expect($bitfinexPublic)->toBeInstanceOf(BitfinexPublic::class);
})->with([
    'Bitfinex Public' => fn () => Bitfinex::public(),
]);

test('Can retrieve bitfinex platform status', function (BitfinexPublic $bitfinexPublic) {
    expect($bitfinexPublic->platformStatus())->toBeArray();
})->with([
    'Bitfinex Public' => fn () => Bitfinex::public(),
]);

test('Can retrieve bitfinex ticker', function (BitfinexPublic $bitfinexPublic) {
    expect($bitfinexPublic->ticker('EURUSD', 'trading'))->toBeArray();
})->with([
    'Bitfinex Public' => fn () => Bitfinex::public(),
]);

test('Can retrieve bitfinex tickers', function (BitfinexPublic $bitfinexPublic) {
    expect($bitfinexPublic->tickers(['EURUSD', 'BTCUSD'], 'trading'))->toBeArray();
})->with([
    'Bitfinex Public' => fn () => Bitfinex::public(),
]);

test('Can retrieve bitfinex ticker history', function (BitfinexPublic $bitfinexPublic) {
    expect($bitfinexPublic->tickerHistory(['EURUSD', 'BTCUSD']))->toBeArray();
})->with([
    'Bitfinex Public' => fn () => Bitfinex::public(),
]);

test('Can retrieve bitfinex trades', function (BitfinexPublic $bitfinexPublic) {
    expect($bitfinexPublic->trades('BTCUSD', 'trading'))->toBeArray();
})->with([
    'Bitfinex Public' => fn () => Bitfinex::public(),
]);
