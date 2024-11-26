<?php

use EwertonDaniel\Bitfinex\Bitfinex;
use EwertonDaniel\Bitfinex\Http\Responses\BitfinexResponse;
use EwertonDaniel\Bitfinex\ValueObjects\BitfinexCredentials;

/** @link https://docs.bitfinex.com/reference/rest-auth-wallets */
test('Should Retrieve Orders', function (BitfinexCredentials $credentials, Bitfinex $bitfinex) {

    $authenticated = $bitfinex->authenticated($credentials)->generateToken();
    $response = $authenticated->wallets()->get();
    dd($response);
    expect($response)->toBeInstanceOf(BitfinexResponse::class);

})->with('Auth')->with('Bitfinex');
