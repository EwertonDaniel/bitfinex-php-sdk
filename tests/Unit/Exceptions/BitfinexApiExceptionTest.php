<?php

use EwertonDaniel\Bitfinex\Exceptions\BitfinexApiException;

test('it reads the code and the message the API sent', function () {
    // A rejected order arrives as HTTP 500 carrying the reason in the body.
    $error = BitfinexApiException::fromBody(
        ['error', 10001, 'Invalid order: not enough exchange balance for 0.001 BTC'],
        500
    );

    expect($error)->toBeInstanceOf(BitfinexApiException::class)
        ->and($error->apiCode)->toBe(10001)
        ->and($error->httpStatus)->toBe(500)
        ->and($error->getMessage())->toBe('Invalid order: not enough exchange balance for 0.001 BTC')
        ->and($error->isRetryable())->toBeFalse();
});

test('it survives an error envelope with no code', function () {
    $error = BitfinexApiException::fromBody(['error', null, 'ERR_WRONG_AMOUNT'], 500);

    expect($error->apiCode)->toBeNull()
        ->and($error->getMessage())->toBe('ERR_WRONG_AMOUNT');
});

test('it recognises the rate limit body, which is an object rather than an array', function () {
    $error = BitfinexApiException::fromBody(['error' => 'ERR_RATE_LIMIT'], 429);

    expect($error)->toBeInstanceOf(BitfinexApiException::class)
        ->and($error->httpStatus)->toBe(429)
        ->and($error->isRetryable())->toBeTrue();
});

test('it survives a body that is not JSON at all', function () {
    // An unknown path answers with an HTML page, not an array.
    $error = BitfinexApiException::fromBody(null, 404, '<html><body><pre>Cannot POST /api/v2/nope</pre></body></html>');

    expect($error)->toBeInstanceOf(BitfinexApiException::class)
        ->and($error->httpStatus)->toBe(404)
        ->and($error->getMessage())->toContain('Cannot POST /api/v2/nope');
});

test('an error envelope is caught even when the status says success', function () {
    $error = BitfinexApiException::fromBody(['error', 10001, 'ERR_UNK: unknown error'], 200);

    expect($error)->toBeInstanceOf(BitfinexApiException::class)
        ->and($error->apiCode)->toBe(10001);
});

test('a successful body produces no exception', function (mixed $body) {
    expect(BitfinexApiException::fromBody($body, 200))->toBeNull();
})->with([
    'an order row' => [[[1234, null, 5678, 'tBTCUSD']]],
    'a flat row' => [[1234, null, 5678, 'tBTCUSD']],
    'an empty list' => [[]],
    // "error" as a value rather than the marker at index 0.
    'a row whose second field is the word error' => [['ok', 'error', 1]],
]);

test('retryable failures are the ones worth retrying', function (?int $code, int $status, bool $retryable) {
    $error = new BitfinexApiException('x', apiCode: $code, httpStatus: $status);

    expect($error->isRetryable())->toBe($retryable);
})->with([
    'nonce out of order' => [BitfinexApiException::ERR_AUTH_NONCE, 500, true],
    'platform in maintenance' => [BitfinexApiException::ERR_MAINTENANCE, 500, true],
    'platform not ready' => [BitfinexApiException::ERR_READY, 500, true],
    'rate limited' => [null, 429, true],
    'bad credentials' => [BitfinexApiException::ERR_AUTH_FAIL, 500, false],
    'rejected order' => [BitfinexApiException::ERR_GENERIC, 500, false],
    'bad parameters' => [BitfinexApiException::ERR_PARAMS, 500, false],
]);
