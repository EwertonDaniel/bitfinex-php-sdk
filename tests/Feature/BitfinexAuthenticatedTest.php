<?php

use EwertonDaniel\Bitfinex\Bitfinex;
use EwertonDaniel\Bitfinex\Entities\Movement;
use EwertonDaniel\Bitfinex\Entities\Summary;
use EwertonDaniel\Bitfinex\Entities\User;
use EwertonDaniel\Bitfinex\Http\Responses\BitfinexResponse;
use EwertonDaniel\Bitfinex\ValueObjects\BitfinexCredentials;

test('Can generate bitfinex token', function (BitfinexCredentials $credentials, Bitfinex $bitfinex) {
    $token = $bitfinex->authenticated($credentials)->generateToken(writePermission: true, caps: ['o'])->getToken();

    expect($token)->toBeString();
    sleep(1);
})->with('Authenticate')
    ->with(['Bitfinex Private' => fn() => new Bitfinex]);

test('Can retrieve key permissions', function (BitfinexCredentials $credentials, Bitfinex $bitfinex) {
    $authenticated = $bitfinex->authenticated($credentials)->generateToken(writePermission: true, caps: ['o']);

    $keyPermissions = $authenticated->accountAction()->keyPermissions();

    expect($keyPermissions)->toBeInstanceOf(BitfinexResponse::class)->and($keyPermissions->content['permissions'])->toBeArray();
})->with('Authenticate')
    ->with(['Bitfinex Private' => fn() => new Bitfinex]);

test('Can retrieve user info', function (BitfinexCredentials $credentials, Bitfinex $bitfinex) {
    $authenticated = $bitfinex->authenticated($credentials)->generateToken(writePermission: true, caps: ['o']);

    $userInfo = $authenticated->accountAction()->userInfo();

    expect($userInfo)->toBeInstanceOf(BitfinexResponse::class)->and($userInfo->content['user'])->toBeInstanceOf(User::class);
    sleep(1);
})->with('Authenticate')
    ->with(['Bitfinex Private' => fn() => new Bitfinex]);

test('Can retrieve user login history', function (BitfinexCredentials $credentials, Bitfinex $bitfinex) {
    $authenticated = $bitfinex->authenticated($credentials)->generateToken(writePermission: true);

    $history = $authenticated->accountAction()->loginHistory();

    expect($history)->toBeInstanceOf(BitfinexResponse::class)->and($history->content['history'])->toBeArray();
    sleep(1);
})->with('Authenticate')
    ->with(['Bitfinex Private' => fn() => new Bitfinex]);

test('Can retrieve summary', function (BitfinexCredentials $credentials, Bitfinex $bitfinex) {
    $authenticated = $bitfinex->authenticated($credentials)->generateToken(writePermission: true);

    $summary = $authenticated->accountAction()->summary();

    expect($summary)->toBeInstanceOf(BitfinexResponse::class)->and($summary->content['summary'])->toBeInstanceOf(Summary::class);
    sleep(1);
})->with('Authenticate')
    ->with(['Bitfinex Private' => fn() => new Bitfinex]);

test('Can retrieve changelog', function (BitfinexCredentials $credentials, Bitfinex $bitfinex) {
    $authenticated = $bitfinex->authenticated($credentials)->generateToken(writePermission: true);

    $changelog = $authenticated->accountAction()->changelog();

    expect($changelog)->toBeInstanceOf(BitfinexResponse::class)->and($changelog->content['changelog'])->toBeArray();
    sleep(1);
})->with('Authenticate')
    ->with(['Bitfinex Private' => fn() => new Bitfinex]);

test('Can retrieve movements', function (BitfinexCredentials $credentials, Bitfinex $bitfinex) {
    $authenticated = $bitfinex->authenticated($credentials)->generateToken(writePermission: true);

    $movements = $authenticated->accountAction()->movements('UST');

    expect($movements)->toBeInstanceOf(BitfinexResponse::class)->and($movements->content['movements'])->toBeArray();
    sleep(1);
})->with('Authenticate')
    ->with(['Bitfinex Private' => fn() => new Bitfinex]);

test('Can retrieve movement info', function (BitfinexCredentials $credentials, Bitfinex $bitfinex, int $id) {
    $authenticated = $bitfinex->authenticated($credentials)->generateToken(writePermission: true);

    $movement = $authenticated->accountAction()->movementInfo($id);

    expect($movement)->toBeInstanceOf(BitfinexResponse::class)->and($movement->content['movement'])->toBeInstanceOf(Movement::class);
    sleep(1);
})->with('Authenticate')
    ->with(['Bitfinex Private' => fn() => new Bitfinex])
    ->with('Movement Id');
