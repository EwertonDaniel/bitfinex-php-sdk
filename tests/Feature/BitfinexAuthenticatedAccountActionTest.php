<?php

use EwertonDaniel\Bitfinex\Bitfinex;
use EwertonDaniel\Bitfinex\Entities\Alert;
use EwertonDaniel\Bitfinex\Entities\DepositAddress;
use EwertonDaniel\Bitfinex\Entities\Movement;
use EwertonDaniel\Bitfinex\Entities\Summary;
use EwertonDaniel\Bitfinex\Entities\User;
use EwertonDaniel\Bitfinex\Enums\BitfinexWalletType;
use EwertonDaniel\Bitfinex\Http\Responses\AuthenticatedBitfinexResponse;
use EwertonDaniel\Bitfinex\ValueObjects\BitfinexCredentials;

test('Can generate bitfinex token', function (BitfinexCredentials $credentials, Bitfinex $bitfinex) {
    $token = $bitfinex->authenticated($credentials)->generateToken(writePermission: true, caps: ['o'])->getToken();

    expect($token)->toBeString();
    sleep(1);
})->with('Auth')
    ->with('Bitfinex');

test('Can retrieve key permissions', function (BitfinexCredentials $credentials, Bitfinex $bitfinex) {
    $authenticated = $bitfinex->authenticated($credentials)->generateToken(writePermission: true, caps: ['o']);

    $response = $authenticated->accountAction()->keyPermissions();

    expect($response)->toBeInstanceOf(AuthenticatedBitfinexResponse::class)->and($response->content['permissions'])->toBeArray();
})->with('Auth')->with('Bitfinex');

test('Can retrieve user info', function (BitfinexCredentials $credentials, Bitfinex $bitfinex) {
    $authenticated = $bitfinex->authenticated($credentials)->generateToken();

    $response = $authenticated->accountAction()->userInfo();

    expect($response)->toBeInstanceOf(AuthenticatedBitfinexResponse::class)->and($response->content['user'])->toBeInstanceOf(User::class);
    sleep(1);
})->with('Auth')
    ->with('Bitfinex');

test('Can retrieve user login history', function (BitfinexCredentials $credentials, Bitfinex $bitfinex) {
    $authenticated = $bitfinex->authenticated($credentials)->generateToken();

    $response = $authenticated->accountAction()->loginHistory();

    expect($response)->toBeInstanceOf(AuthenticatedBitfinexResponse::class)->and($response->content['history'])->toBeArray();
    sleep(1);
})->with('Auth')
    ->with('Bitfinex');

test('Can retrieve summary', function (BitfinexCredentials $credentials, Bitfinex $bitfinex) {
    $authenticated = $bitfinex->authenticated($credentials)->generateToken();

    $response = $authenticated->accountAction()->summary();

    expect($response)->toBeInstanceOf(AuthenticatedBitfinexResponse::class)->and($response->content['summary'])->toBeInstanceOf(Summary::class);
    sleep(1);
})->with('Auth')
    ->with('Bitfinex');

test('Can retrieve changelog', function (BitfinexCredentials $credentials, Bitfinex $bitfinex) {
    $authenticated = $bitfinex->authenticated($credentials)->generateToken();

    $response = $authenticated->accountAction()->changelog();

    expect($response)->toBeInstanceOf(AuthenticatedBitfinexResponse::class)->and($response->content['changelog'])->toBeArray();
    sleep(1);
})->with('Auth')
    ->with('Bitfinex');

test('Should retrieve deposit address', function (BitfinexCredentials $credentials, Bitfinex $bitfinex) {
    $response = $bitfinex->authenticated($credentials)->accountAction()->depositAddress(BitfinexWalletType::EXCHANGE, 'monero');
    expect($response)
        ->toBeInstanceOf(AuthenticatedBitfinexResponse::class)
        ->and($response->content['address'])
        ->toBeInstanceOf(DepositAddress::class);
    sleep(1);
})->with('Auth')->with('Bitfinex');

test('Should retrieve deposit address list', function (BitfinexCredentials $credentials, Bitfinex $bitfinex) {
    $response = $bitfinex->authenticated($credentials)->accountAction()->depositAddressList('monero');
    expect($response)->toBeInstanceOf(AuthenticatedBitfinexResponse::class)->and($response->content['addresses'])->toBeArray();
    sleep(1);
})->with('Auth')->with('Bitfinex');

test('Can retrieve movements', function (BitfinexCredentials $credentials, Bitfinex $bitfinex) {
    $authenticated = $bitfinex->authenticated($credentials)->generateToken();

    $response = $authenticated->accountAction()->movements('UST');

    expect($response)->toBeInstanceOf(AuthenticatedBitfinexResponse::class)->and($response->content['movements'])->toBeArray();
    sleep(1);
})->with('Auth')
    ->with('Bitfinex');

test('Can retrieve movement info', function (BitfinexCredentials $credentials, Bitfinex $bitfinex, int $id) {
    $authenticated = $bitfinex->authenticated($credentials)->generateToken();

    $response = $authenticated->accountAction()->movements($id);

    expect($response)->toBeInstanceOf(AuthenticatedBitfinexResponse::class)->and($response->content['movement'])->toBeInstanceOf(Movement::class);
    sleep(1);
})->with('Auth')
    ->with('Bitfinex')
    ->with('Movement Id')
    ->skip();

test('Can retrieve alert set', function (BitfinexCredentials $credentials, Bitfinex $bitfinex) {
    $authenticated = $bitfinex->authenticated($credentials)->generateToken(writePermission: true, caps: ['o', 'a']);

    $response = $authenticated->accountAction()->alertSet(symbol: 'XMRUSD', price: 250);

    expect($response)->toBeInstanceOf(AuthenticatedBitfinexResponse::class)->and($response->content['alert'])->toBeInstanceOf(Alert::class);

    sleep(1);
})->with('Auth')
    ->with('Bitfinex');

test('Can retrieve delete alert', function (BitfinexCredentials $credentials, Bitfinex $bitfinex) {
    $authenticated = $bitfinex->authenticated($credentials)->generateToken(writePermission: true, caps: ['o', 'a']);

    $response = $authenticated->accountAction()->alertDelete(symbol: 'XMRUSD', price: 250);

    expect($response)->toBeInstanceOf(AuthenticatedBitfinexResponse::class)->and($response->content['deleted'])->toBeTrue();

    sleep(1);
})->with('Auth')
    ->with('Bitfinex');

test('Can retrieve alert list', function (BitfinexCredentials $credentials, Bitfinex $bitfinex) {
    $authenticated = $bitfinex->authenticated($credentials)->generateToken();

    $response = $authenticated->accountAction()->alertList('price');

    expect($response)->toBeInstanceOf(AuthenticatedBitfinexResponse::class)->and($response->content['alerts'])->toBeArray();

    sleep(1);
})->with('Auth')
    ->with('Bitfinex');

/** @link https://docs.bitfinex.com/reference/rest-auth-calc-order-avail */
test('Can Retrieve Available Balance for Orders and Offers', function (BitfinexCredentials $credentials, Bitfinex $bitfinex) {
    $authenticated = $bitfinex->authenticated($credentials)->generateToken();

    $response = $authenticated->accountAction()
        ->balanceAvailableForOrdersOffers(
            type: \EwertonDaniel\Bitfinex\Enums\BitfinexType::TRADING,
            pairOrCurrency: 'XMRUSD',
            action: \EwertonDaniel\Bitfinex\Enums\BitfinexAction::BUY,
            orderOfferType: \EwertonDaniel\Bitfinex\Enums\OrderOfferType::DERIV,
            rate: '0.1'
        );

    expect($response)
        ->toBeInstanceOf(AuthenticatedBitfinexResponse::class)
        ->and($response->content['available'])
        ->toBeNumeric();

    sleep(1);
})->with('Auth')
    ->with('Bitfinex');
