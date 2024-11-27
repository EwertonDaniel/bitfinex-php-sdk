<?php

use EwertonDaniel\Bitfinex\Bitfinex;
use EwertonDaniel\Bitfinex\Enums\BitfinexAction;
use EwertonDaniel\Bitfinex\Enums\OrderType;
use EwertonDaniel\Bitfinex\Http\Responses\BitfinexResponse;
use EwertonDaniel\Bitfinex\ValueObjects\BitfinexCredentials;

/** @link https://docs.bitfinex.com/reference/rest-auth-retrieve-orders */
test('Should Retrieve Orders', function (BitfinexCredentials $credentials, Bitfinex $bitfinex, ?string $symbol) {
    $authenticated = $bitfinex->authenticated($credentials)->generateToken();

    $response = $authenticated->orders()->retrieve($symbol);
    expect($response)->toBeInstanceOf(BitfinexResponse::class)->and($response->content['orders'])->toBeArray();
    sleep(1);
})->with('Auth')->with('Bitfinex')->with(['Symbol' => ['with' => 'XMRUST', 'without' => null]]);

/** @link https://docs.bitfinex.com/reference/rest-auth-submit-order */
test('Should Submit Order', function (BitfinexCredentials $credentials, Bitfinex $bitfinex, ?string $symbol) {
    $authenticated = $bitfinex->authenticated($credentials)->generateToken(writePermission: true, caps: ['o']);

    $response = $authenticated->orders()
        ->submit(type: OrderType::EXCHANGE_LIMIT, action: BitfinexAction::BUY, pair: $symbol, amount: 0.02, price: 140);
    expect($response)->toBeInstanceOf(BitfinexResponse::class)->and($response->content['order'])->toBeArray();
})->with('Auth')->with('Bitfinex')->with(['Symbol' => ['with' => 'XMRUST', 'without' => null]])->skip();
