<?php

use EwertonDaniel\Bitfinex\Bitfinex;
use EwertonDaniel\Bitfinex\Http\Responses\BitfinexResponse;
use EwertonDaniel\Bitfinex\ValueObjects\BitfinexCredentials;

test('Should Retrieve Orders', function (BitfinexCredentials $credentials, Bitfinex $bitfinex) {

    $authenticated = $bitfinex->authenticated($credentials)->generateToken();
    $response = $authenticated->wallets()->get();

    expect($response)->toBeInstanceOf(BitfinexResponse::class)->dump();

})->with('Auth')->with('Bitfinex');
