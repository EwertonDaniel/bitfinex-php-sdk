<?php

use EwertonDaniel\Bitfinex\Entities\Alert;
use EwertonDaniel\Bitfinex\Entities\DepositAddress;
use EwertonDaniel\Bitfinex\Entities\Movement;
use EwertonDaniel\Bitfinex\Entities\Summary;
use EwertonDaniel\Bitfinex\Entities\User;
use EwertonDaniel\Bitfinex\Entities\Withdrawal;
use EwertonDaniel\Bitfinex\Enums\BitfinexAction;
use EwertonDaniel\Bitfinex\Enums\BitfinexType;
use EwertonDaniel\Bitfinex\Enums\BitfinexWalletType;
use EwertonDaniel\Bitfinex\Enums\OrderOfferType;
use EwertonDaniel\Bitfinex\Exceptions\BitfinexApiException;
use EwertonDaniel\Bitfinex\Exceptions\BitfinexNotificationException;
use EwertonDaniel\Bitfinex\Http\Responses\AuthenticatedBitfinexResponse;
use GuzzleHttp\Psr7\Response;
use Tests\Support\BitfinexMock;
use Tests\Support\Fixtures;

test('Can generate bitfinex token', function () {
    $mock = BitfinexMock::queue([Fixtures::token()]);

    $token = $mock->authenticated()->generateToken(writePermission: true, caps: ['o'])->getToken();

    expect($token)->toBeString()->toStartWith('eyJ')
        ->and($mock->paths()[0])->toBe('/v2/auth/w/token')
        ->and($mock->bodyOf(0))->toMatchArray(['scope' => 'api', 'writePermission' => true, 'caps' => ['o']]);
});

test('a generated token replaces the api key on the next request but not the nonce', function () {
    $mock = BitfinexMock::queue([Fixtures::token(), Fixtures::keyPermissions()]);

    $mock->authenticated()->generateToken()->accountAction()->keyPermissions();

    $headers = $mock->headersOf(1);

    expect($headers)->toHaveKey('bfx-token')
        ->and($headers)->toHaveKey('bfx-nonce')
        ->and($headers)->not->toHaveKey('bfx-apikey');
});

test('Can retrieve key permissions', function () {
    $mock = BitfinexMock::queue([Fixtures::token(), Fixtures::keyPermissions()]);

    $response = $mock->authenticated()->generateToken(writePermission: true, caps: ['o'])
        ->accountAction()->keyPermissions();

    expect($response)->toBeInstanceOf(AuthenticatedBitfinexResponse::class)
        ->and($response->content['permissions'])->toHaveCount(5)
        ->and($response->content['permissions'][0]->scope)->toBe('account')
        ->and($response->content['permissions'][0]->read)->toBeTrue()
        ->and($response->content['permissions'][0]->write)->toBeFalse()
        ->and($response->content['permissions'][1]->write)->toBeTrue();
});

test('Can retrieve user info', function () {
    $mock = BitfinexMock::queue([Fixtures::token(), Fixtures::userInfo()]);

    $response = $mock->authenticated()->generateToken()->accountAction()->userInfo();

    expect($response->content['user'])->toBeInstanceOf(User::class)
        ->and($response->content['user']->id)->toBe(1234567)
        ->and($response->content['user']->email)->toBe('trader@example.com')
        ->and($response->content['user']->verified)->toBeTrue()
        ->and($response->content['user']->timezone)->toBe('Europe/Lisbon')
        // [26] holds the 2FA modes; reading it one index off would lose this.
        ->and($response->content['user']->twoFactorAuthModes->oneTimePassword)->toBeTrue();
});

test('Can retrieve user login history', function () {
    $mock = BitfinexMock::queue([Fixtures::token(), Fixtures::loginHistory()]);

    $response = $mock->authenticated()->generateToken()->accountAction()->loginHistory();

    expect($response->content['history'])->toHaveCount(2)
        ->and($response->content['history'][0]->ip)->toBe('203.0.113.10')
        ->and($response->content['history'][0]->time->timestamp)->toBe(1754006400)
        ->and($response->content['history'][0]->extraInfo)->toBe(['user_agent' => ['browser' => 'Firefox']]);
});

test('Can retrieve summary', function () {
    $mock = BitfinexMock::queue([Fixtures::token(), Fixtures::summary()]);

    $response = $mock->authenticated()->generateToken()->accountAction()->summary();

    expect($response->content['summary'])->toBeInstanceOf(Summary::class)
        ->and($response->content['summary']->feeInfo->makerFeeInfo->makerFeeToCrypto)->toBe(0.001)
        ->and($response->content['summary']->feeInfo->takerFeeInfo->takerFeeToCrypto)->toBe(0.002)
        ->and($response->content['summary']->tradingVolAndFee->tradeVolMonth[0]['vol'])->toBe(125000.5)
        ->and($response->content['summary']->tradingVolAndFee->feesTradingTotalMonth)->toBe(300.10)
        // LeoInfo is keyed by name rather than by position, and typed float.
        ->and($response->content['summary']->leoInfo->leoLevel)->toBe(2.0);
});

test('Can retrieve changelog', function () {
    $mock = BitfinexMock::queue([Fixtures::token(), Fixtures::changelog()]);

    $response = $mock->authenticated()->generateToken()->accountAction()->changelog();

    expect($response->content['changelog'])->toHaveCount(1)
        ->and($response->content['changelog'][0]->log)->toBe('Password changed')
        ->and($response->content['changelog'][0]->ip)->toBe('203.0.113.10');
});

test('Should retrieve deposit address', function () {
    $mock = BitfinexMock::queue([Fixtures::depositAddress()]);

    $response = $mock->authenticated()->accountAction()->depositAddress(BitfinexWalletType::EXCHANGE, 'monero');

    expect($response->content['address'])->toBeInstanceOf(DepositAddress::class)
        // The address sits at DATA[4], not at the top level of the envelope.
        ->and($response->content['address']->address)->toStartWith('44AFFq5kSiGBoZ4NMDwYtN18obc8AemS33DBLWs3H7ot')
        ->and($response->content['address']->currencyCode)->toBe('XMR')
        ->and($response->content['address']->method)->toBe('monero')
        ->and($response->content['address']->walletType)->toBe(BitfinexWalletType::EXCHANGE);
});

test('Should retrieve deposit address list', function () {
    $mock = BitfinexMock::queue([Fixtures::depositAddressList()]);

    $response = $mock->authenticated()->accountAction()->depositAddressList('monero');

    expect($response->content['addresses']['method'])->toBe('monero')
        ->and($response->content['addresses']['items'])->toHaveCount(2)
        ->and($response->content['addresses']['items'][0]['address'])->toBeInstanceOf(DepositAddress::class)
        ->and($response->content['addresses']['items'][0]['address']->walletType)->toBe(BitfinexWalletType::EXCHANGE)
        ->and($response->content['addresses']['items'][1]['address']->walletType)->toBe(BitfinexWalletType::MARGIN);
});

test('Can retrieve movements', function () {
    $mock = BitfinexMock::queue([Fixtures::token(), Fixtures::movements()]);

    $response = $mock->authenticated()->generateToken()->accountAction()->movements('UST');

    expect($response->content['movements'])->toHaveCount(2)
        ->and($response->content['movements'][0])->toBeInstanceOf(Movement::class)
        ->and($response->content['movements'][0]->currency)->toBe('UST')
        ->and($response->content['movements'][0]->amount)->toBe(-250.0)
        ->and($response->content['movements'][0]->status)->toBe('COMPLETED');
});

test('deposit and withdrawal history split the same rows by the sign of the amount', function () {
    $mock = BitfinexMock::queue([Fixtures::movements(), Fixtures::movements()]);
    $account = $mock->authenticated()->accountAction();

    $deposits = $account->depositHistory('UST')->content['deposits'];
    $withdrawals = $account->withdrawalHistory('UST')->content['withdrawals'];

    expect($deposits)->toHaveCount(1)
        ->and($deposits[0]->amount)->toBe(500.0)
        ->and($withdrawals)->toHaveCount(1)
        ->and($withdrawals[0]->amount)->toBe(-250.0);
});

test('Can retrieve movement info', function () {
    $mock = BitfinexMock::queue([Fixtures::token(), Fixtures::movements()[0]]);

    $response = $mock->authenticated()->generateToken()->accountAction()->movementInfo(987654321);

    expect($response->content['movement'])->toBeInstanceOf(Movement::class)
        ->and($response->content['movement']->id)->toBe(987654321);
});

test('Can retrieve alert set', function () {
    $mock = BitfinexMock::queue([Fixtures::token(), Fixtures::alert()]);

    $response = $mock->authenticated()->generateToken(writePermission: true, caps: ['o', 'a'])
        ->accountAction()->alertSet(pair: 'XMRUSD', price: 250);

    expect($response->content['alert'])->toBeInstanceOf(Alert::class)
        ->and($response->content['alert']->price)->toBe(250.0)
        ->and($response->content['alert']->pair)->toBe('XMRUSD')
        ->and($response->content['alert']->bitfinexType)->toBe(BitfinexType::TRADING);
});

test('Can retrieve delete alert', function () {
    // The endpoint answers with a boolean, not with a 1.
    $mock = BitfinexMock::queue([Fixtures::token(), Fixtures::alertDelete()]);

    $response = $mock->authenticated()->generateToken(writePermission: true, caps: ['o', 'a'])
        ->accountAction()->alertDelete(pair: 'XMRUSD', price: 250);

    expect($response->content['deleted'])->toBeTrue()
        // The price is a path segment here, so a formatting slip changes the target.
        ->and($mock->paths()[1])->toBe('/v2/auth/w/alert/price:tXMRUSD:250/del');
});

test('Can retrieve alert list', function () {
    $mock = BitfinexMock::queue([Fixtures::token(), Fixtures::alertList()]);

    $response = $mock->authenticated()->generateToken()->accountAction()->alertList('price');

    expect($response->content['alerts'])->toHaveCount(2)
        ->and($response->content['alerts'][0])->toBeInstanceOf(Alert::class)
        ->and($response->content['alerts'][1]->price)->toBe(300.0);
});

/** @link https://docs.bitfinex.com/reference/rest-auth-calc-order-avail */
test('Can Retrieve Available Balance for Orders and Offers', function () {
    $mock = BitfinexMock::queue([Fixtures::token(), Fixtures::balanceAvailable()]);

    $response = $mock->authenticated()->generateToken()->accountAction()
        ->balanceAvailableForOrdersOffers(
            type: BitfinexType::TRADING,
            pairOrCurrency: 'XMRUSD',
            action: BitfinexAction::BUY,
            orderOfferType: OrderOfferType::DERIV,
            rate: '0.1'
        );

    expect($response->content['available'])->toBeNumeric()->toBe(0.8056309);
});

test('a refused transfer raises instead of returning a status field', function () {
    $mock = BitfinexMock::queue([
        Fixtures::notification(
            [1754006400000, 'exchange', 'margin', null, 'USD', 'USD', null, 50.0],
            'ERROR',
            'acc_tf',
            'Not enough balance in exchange wallet.'
        ),
    ]);

    $mock->authenticated()->accountAction()->transferBetweenWallets(
        from: BitfinexWalletType::EXCHANGE,
        to: BitfinexWalletType::MARGIN,
        currency: 'USD',
        amount: 50.0
    );
})->throws(BitfinexNotificationException::class, 'Not enough balance in exchange wallet.');

test('an error envelope reaches the caller with the code the API gave', function () {
    // The API reports failure in the body while answering HTTP 500.
    $mock = BitfinexMock::queue([
        new Response(500, ['Content-Type' => 'application/json'], json_encode(Fixtures::error(10100, 'apikey: digest invalid'))),
    ]);

    try {
        $mock->authenticated()->accountAction()->userInfo();
        $this->fail('the error envelope was accepted as a success');
    } catch (BitfinexApiException $e) {
        expect($e->apiCode)->toBe(BitfinexApiException::ERR_AUTH_FAIL)
            ->and($e->httpStatus)->toBe(500)
            ->and($e->getMessage())->toBe('apikey: digest invalid')
            ->and($e->isRetryable())->toBeFalse();
    }
});

/** @link https://docs.bitfinex.com/reference/rest-auth-withdraw */
test('a withdrawal maps the record its notification carries', function () {
    $mock = BitfinexMock::queue([
        Fixtures::notification(
            Fixtures::withdrawalRow(),
            type: 'acc_wd-req',
            text: 'Your withdrawal request has been successfully submitted.'
        ),
    ]);

    $response = $mock->authenticated()->accountAction()->withdrawal(
        walletType: BitfinexWalletType::EXCHANGE,
        method: 'ethereum',
        amount: 0.01,
        options: ['address' => '0x742d35Cc6634C0532925a3b844Bc454e4438f44e']
    );

    $withdrawal = $response->content['withdrawal'];

    expect($withdrawal)->toBeInstanceOf(Withdrawal::class)
        ->and($withdrawal->id)->toBe(13080092)
        ->and($withdrawal->method)->toBe('ethereum')
        ->and($withdrawal->wallet)->toBe('exchange')
        ->and($withdrawal->amount)->toBe(0.01)
        ->and($withdrawal->fee)->toBe(0.00135)
        ->and($mock->paths()[0])->toBe('/v2/auth/w/withdraw')
        ->and($mock->bodyOf(0))->toMatchArray(['wallet' => 'exchange', 'method' => 'ethereum', 'amount' => '0.01']);
});

test('a refused withdrawal raises instead of returning the envelope', function () {
    $mock = BitfinexMock::queue([
        Fixtures::notification(null, 'ERROR', 'acc_wd-req', 'Invalid withdrawal amount, minimum is 0.001 (ETH)'),
    ]);

    $mock->authenticated()->accountAction()->withdrawal(
        walletType: BitfinexWalletType::EXCHANGE,
        method: 'ethereum',
        amount: 0.0001
    );
})->throws(BitfinexNotificationException::class, 'Invalid withdrawal amount, minimum is 0.001 (ETH)');

/** @link https://docs.bitfinex.com/reference/rest-auth-settings-set */
test('writing settings maps the count its notification carries', function () {
    $mock = BitfinexMock::queue([
        Fixtures::notification([2], type: 'acc_ss'),
    ]);

    $response = $mock->authenticated()->accountAction()
        ->userSettingsWrite(['api:key1' => 'foo', 'api:key2' => 'bar']);

    expect($response->content['count'])->toBe(2)
        ->and($mock->paths()[0])->toBe('/v2/auth/w/settings/set')
        ->and($mock->bodyOf(0))->toMatchArray(['settings' => ['api:key1' => 'foo', 'api:key2' => 'bar']]);
});

test('a refused settings write raises instead of returning the envelope', function () {
    $mock = BitfinexMock::queue([
        Fixtures::notification(null, 'ERROR', 'acc_ss', 'Invalid setting key.'),
    ]);

    $mock->authenticated()->accountAction()->userSettingsWrite(['bad-key' => 'x']);
})->throws(BitfinexNotificationException::class, 'Invalid setting key.');

/** @link https://docs.bitfinex.com/reference/rest-auth-settings-del */
test('deleting settings succeeds on the envelope status alone', function () {
    $mock = BitfinexMock::queue([
        Fixtures::notification([1], type: 'acc_sd'),
    ]);

    $response = $mock->authenticated()->accountAction()->userSettingsDelete(['api:key1']);

    expect($response->content)->toHaveKey('notification')
        ->and($response->content['notification']->status)->toBe('SUCCESS')
        ->and($mock->paths()[0])->toBe('/v2/auth/w/settings/del')
        ->and($mock->bodyOf(0))->toMatchArray(['keys' => ['api:key1']]);
});
