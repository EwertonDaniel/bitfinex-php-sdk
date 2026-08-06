<?php

use EwertonDaniel\Bitfinex\Entities\Wallet;
use EwertonDaniel\Bitfinex\Http\Responses\AuthenticatedBitfinexResponse;
use Tests\Support\BitfinexMock;
use Tests\Support\Fixtures;

/** @link https://docs.bitfinex.com/reference/rest-auth-wallets */
test('Should Retrieve Wallets', function () {
    $mock = BitfinexMock::queue([Fixtures::token(), Fixtures::wallets()]);

    $response = $mock->authenticated()->generateToken()->wallets()->get();

    expect($response)->toBeInstanceOf(AuthenticatedBitfinexResponse::class)
        ->and($response->content['wallets'])->toHaveCount(3)
        ->and($response->content['wallets'][0])->toBeInstanceOf(Wallet::class)
        ->and($response->content['wallets'][0]->type)->toBe('exchange')
        ->and($response->content['wallets'][0]->currency)->toBe('UST')
        ->and($response->content['wallets'][0]->balance)->toBe(1500.25)
        // [4] is the available balance, distinct from the [2] total.
        ->and($response->content['wallets'][1]->availableBalance)->toBe(250.0)
        ->and($response->content['wallets'][2]->unsettledInterest)->toBe(0.5)
        ->and($mock->paths()[1])->toBe('/v2/auth/r/wallets');
});
