<?php

use EwertonDaniel\Bitfinex\Entities\FundingOffer;
use EwertonDaniel\Bitfinex\Entities\Notification;
use EwertonDaniel\Bitfinex\Exceptions\BitfinexNotificationException;
use Tests\Support\BitfinexMock;
use Tests\Support\Fixtures;

/** @link https://docs.bitfinex.com/reference/rest-auth-submit-funding-offer */
test('a submitted funding offer maps DATA, not the envelope', function () {
    $mock = BitfinexMock::queue([
        Fixtures::notification(Fixtures::fundingOfferRow(), type: 'fon-req', text: 'Submitting funding bid of 0.5 ETH.'),
    ]);

    $response = $mock->authenticated()->funding()
        ->submitOffer(currency: 'ETH', amount: 0.5, rate: 0.0024, period: 2);

    $offer = $response->content['offer'];

    // The envelope used to be handed to FundingOffer whole, which put the
    // timestamp in `id` and the type string in `symbol`.
    expect($offer)->toBeInstanceOf(FundingOffer::class)
        ->and($offer->id)->toBe(41237920)
        ->and($offer->symbol)->toBe('fETH')
        ->and($offer->rate)->toBe(0.0024)
        ->and($offer->period)->toBe(2)
        ->and($response->content['notification'])->toBeInstanceOf(Notification::class)
        ->and($mock->paths()[0])->toBe('/v2/auth/w/funding/offer/submit')
        ->and($mock->bodyOf(0))->toMatchArray(['symbol' => 'fETH', 'amount' => '0.5', 'rate' => '0.0024', 'period' => 2]);
});

test('a refused funding offer raises instead of returning', function () {
    $mock = BitfinexMock::queue([
        Fixtures::notification(null, 'ERROR', 'fon-req', 'Invalid offer: not enough funds available.'),
    ]);

    $mock->authenticated()->funding()->submitOffer(currency: 'ETH', amount: 0.5, rate: 0.0024, period: 2);
})->throws(BitfinexNotificationException::class, 'Invalid offer: not enough funds available.');

/** @link https://docs.bitfinex.com/reference/rest-auth-cancel-funding-offer */
test('a cancelled funding offer maps the offer as it stands after cancellation', function () {
    $mock = BitfinexMock::queue([
        Fixtures::notification(Fixtures::fundingOfferRow(status: 'CANCELED'), type: 'foc-req', text: 'Offer cancelled.'),
    ]);

    $response = $mock->authenticated()->funding()->cancelOffer(41237920);

    expect($response->content['offer'])->toBeInstanceOf(FundingOffer::class)
        ->and($response->content['offer']->status)->toBe('CANCELED')
        ->and($mock->bodyOf(0))->toMatchArray(['id' => 41237920]);
});

/** @link https://docs.bitfinex.com/reference/rest-auth-cancel-all-funding-offers */
test('cancel-all carries its outcome only in the notification text', function () {
    $mock = BitfinexMock::queue([
        Fixtures::notification(null, type: 'foc_all-req', text: 'All (8) submitted for cancellation'),
    ]);

    $response = $mock->authenticated()->funding()->cancelAllOffers('ETH');

    expect($response->content)->toHaveKey('notification')
        ->and($response->content['notification']->text)->toBe('All (8) submitted for cancellation')
        ->and($response->content['notification']->data)->toBeNull();
});

/** @link https://docs.bitfinex.com/reference/rest-auth-funding-close */
test('a refused funding close raises with the text the API gave', function () {
    $mock = BitfinexMock::queue([
        Fixtures::notification(null, 'ERROR', 'fr-req', 'Funding position not found.'),
    ]);

    $mock->authenticated()->funding()->close(12345);
})->throws(BitfinexNotificationException::class, 'Funding position not found.');

/** @link https://docs.bitfinex.com/reference/rest-auth-funding-auto-renew */
test('auto-renew maps the four-field DATA it answers with', function () {
    $mock = BitfinexMock::queue([
        Fixtures::notification(['UST', 2, 0, 350], type: 'fa-req', text: 'Auto-renew enabled.'),
    ]);

    $response = $mock->authenticated()->funding()->autoRenew(currency: 'UST', status: true, period: 2);

    expect($response->content['autorenew'])->toBe(['UST', 2, 0, 350])
        ->and($response->content['notification'])->toBeInstanceOf(Notification::class);
});

/** @link https://docs.bitfinex.com/reference/rest-auth-keep-funding */
test('keep-funding succeeds on the envelope status alone', function () {
    $mock = BitfinexMock::queue([
        Fixtures::notification(null, type: 'fk-req', text: 'Credit updated'),
    ]);

    $response = $mock->authenticated()->funding()->keep('credit', [12345]);

    expect($response->content['notification']->text)->toBe('Credit updated')
        ->and($mock->bodyOf(0))->toMatchArray(['type' => 'credit', 'id' => [12345]]);
});

test('a refused keep-funding raises instead of returning', function () {
    $mock = BitfinexMock::queue([
        Fixtures::notification(null, 'ERROR', 'fk-req', 'Invalid funding id.'),
    ]);

    $mock->authenticated()->funding()->keep('loan', [999]);
})->throws(BitfinexNotificationException::class, 'Invalid funding id.');
