<?php

use EwertonDaniel\Bitfinex\Exceptions\BitfinexNotificationException;
use EwertonDaniel\Bitfinex\Http\Responses\AuthenticatedBitfinexResponse;
use GuzzleHttp\Psr7\Response;
use Tests\Support\Fixtures;

/**
 * Structural: every transformer of an endpoint that answers with a notification
 * envelope must apply the status gate. The list is curated on purpose — which
 * endpoints are notifications is wire knowledge confirmed against the official
 * reference, not something the structure of the class can reveal. When a new
 * notification transformer is added, add it here; when a gate is removed by
 * accident, this fails.
 */
$gatedTransformers = [
    'submitOrder',
    'orderNotification',
    'orderMultiOp',
    'orderCancelMulti',
    'fundingOfferSubmitted',
    'cancelFundingOffer',
    'cancelAllFundingOffers',
    'fundingClose',
    'fundingAutoRenew',
    'keepFunding',
    'transferBetweenWallets',
    'withdrawal',
    'positionsClaim',
    'userSettingsWrite',
    'userSettingsDelete',
];

test('every notification transformer raises when the API reports a status other than SUCCESS', function (string $transformer) {
    $envelope = Fixtures::notification(null, 'ERROR', 'test-req', 'refused by the API');

    $response = new AuthenticatedBitfinexResponse(
        new Response(200, ['Content-Type' => 'application/json'], json_encode($envelope))
    );

    $response->{$transformer}();
})->with($gatedTransformers)->throws(BitfinexNotificationException::class, 'refused by the API');
