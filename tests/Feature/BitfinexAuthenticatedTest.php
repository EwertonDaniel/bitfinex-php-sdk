<?php

use EwertonDaniel\Bitfinex\Bitfinex;


test('Can retrieve bitfinex token', function (Bitfinex $bitfinex) {
})->with(['Bitfinex Private' => fn() => new Bitfinex]);
