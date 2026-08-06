<?php

use EwertonDaniel\Bitfinex\Builders\RequestBodyBuilder;
use EwertonDaniel\Bitfinex\Exceptions\BitfinexException;
use EwertonDaniel\Bitfinex\Helpers\BitfinexConfig;
use EwertonDaniel\Bitfinex\ValueObjects\BitfinexCredentials;

/**
 * Sets an environment variable across every source BitfinexConfig reads.
 */
function withEnv(string $name, string $value, Closure $body): mixed
{
    $previous = [$_ENV[$name] ?? null, $_SERVER[$name] ?? null, getenv($name)];

    $_ENV[$name] = $value;
    $_SERVER[$name] = $value;
    putenv("$name=$value");

    try {
        return $body();
    } finally {
        [$env, $server, $original] = $previous;

        unset($_ENV[$name], $_SERVER[$name]);
        putenv($name);

        if (! is_null($env)) {
            $_ENV[$name] = $env;
        }

        if (! is_null($server)) {
            $_SERVER[$name] = $server;
        }

        if (is_string($original)) {
            putenv("$name=$original");
        }
    }
}

test('a non-numeric timeout falls back instead of becoming zero', function (string $configured, float $expected) {
    $timeout = withEnv(
        'BITFINEX_PUBLIC_TIMEOUT',
        $configured,
        fn () => BitfinexConfig::float('timeout.public', 'BITFINEX_PUBLIC_TIMEOUT', 30.0)
    );

    expect($timeout)->toBe($expected);
})->with([
    // Guzzle reads a timeout of 0 as "no timeout", so casting a typo to 0.0 hung
    // the caller on a stalled connection rather than failing.
    'plain number' => ['10', 10.0],
    'number with a unit suffix' => ['10s', 30.0],
    'not a number at all' => ['abc', 30.0],
    'a word' => ['none', 30.0],
]);

test('a non-numeric integer setting falls back instead of becoming zero', function () {
    $ttl = withEnv(
        'BITFINEX_TOKEN_TTL',
        'abc',
        fn () => BitfinexConfig::int('token.ttl', 'BITFINEX_TOKEN_TTL', 120)
    );

    expect($ttl)->toBe(120);
});

test('credentials never print the secret', function () {
    $credentials = new BitfinexCredentials('chave-de-api-longa', 'SUPERSECRET-xyz', 'TOKEN-1234567');

    $printed = print_r($credentials, true);

    expect($printed)->not->toContain('SUPERSECRET-xyz')
        ->and($printed)->not->toContain('TOKEN-1234567')
        // Enough is kept to tell one key from another.
        ->and($credentials->__debugInfo()['apiSecret'])->toBe('SUPE…yz');
});

test('short secrets are masked entirely', function () {
    $credentials = new BitfinexCredentials('key', 'short');

    expect($credentials->__debugInfo()['apiSecret'])->toBe('********')
        ->and($credentials->__debugInfo()['token'])->toBeNull();
});

test('a body that cannot be encoded reports which field is at fault', function (array $body) {
    $builder = (new RequestBodyBuilder)->setBody($body);

    // It used to return false and trip the string return type with a TypeError,
    // twice per request: once to sign, once to send.
    expect(fn () => $builder->__toString())->toThrow(BitfinexException::class);
})->with([
    'invalid UTF-8 in a text field' => [['note' => "caf\xE9"]],
    'a non-finite amount' => [['amount' => NAN]],
]);

test('an empty body is still encoded as a JSON object', function () {
    expect((new RequestBodyBuilder)->__toString())->toBe('{}');
});
