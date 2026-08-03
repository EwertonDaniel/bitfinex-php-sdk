<?php

use EwertonDaniel\Bitfinex\Builders\RequestBuilder;
use EwertonDaniel\Bitfinex\Builders\UrlBuilder;
use EwertonDaniel\Bitfinex\Enums\BitfinexAction;
use EwertonDaniel\Bitfinex\Enums\OrderType;
use EwertonDaniel\Bitfinex\Exceptions\BitfinexException;
use EwertonDaniel\Bitfinex\Services\BitfinexAuthenticated;
use EwertonDaniel\Bitfinex\ValueObjects\BitfinexCredentials;

/**
 * Reads the RequestBuilder shared by every authenticated sub-service.
 */
function sharedRequestBuilder(BitfinexAuthenticated $authenticated): RequestBuilder
{
    $property = (new ReflectionClass($authenticated))->getProperty('request');
    $property->setAccessible(true);

    return $property->getValue($authenticated);
}

/**
 * Builds credentials that cannot sign, whatever the ambient environment holds.
 */
function credentialsWithoutSecret(): BitfinexCredentials
{
    $previous = [
        $_ENV['BITFINEX_API_SECRET'] ?? null,
        $_SERVER['BITFINEX_API_SECRET'] ?? null,
        getenv('BITFINEX_API_SECRET'),
    ];

    unset($_ENV['BITFINEX_API_SECRET'], $_SERVER['BITFINEX_API_SECRET']);
    putenv('BITFINEX_API_SECRET');

    try {
        return new BitfinexCredentials('key');
    } finally {
        [$env, $server, $getenv] = $previous;

        if (! is_null($env)) {
            $_ENV['BITFINEX_API_SECRET'] = $env;
        }

        if (! is_null($server)) {
            $_SERVER['BITFINEX_API_SECRET'] = $server;
        }

        if (is_string($getenv)) {
            putenv("BITFINEX_API_SECRET=$getenv");
        }
    }
}

test('a call that fails after building the body leaves no residue behind', function () {
    // Without a secret, signing throws once the body is already populated.
    $authenticated = new BitfinexAuthenticated(new UrlBuilder, credentialsWithoutSecret());

    expect(fn () => $authenticated->orders()->submit(
        type: OrderType::LIMIT,
        action: BitfinexAction::SELL,
        pair: 'BTCUSD',
        amount: 1.5,
        price: 90000
    ))->toThrow(BitfinexException::class)
        ->and(sharedRequestBuilder($authenticated)->getBody())->toBeEmpty();
});

test('reset clears body, query and custom headers but keeps the defaults', function () {
    $builder = (new RequestBuilder)
        ->setBody(['type' => 'LIMIT', 'amount' => '-1.5'])
        ->setQuery(['start' => 1])
        ->addHeader('bfx-nonce', '123');

    $builder->reset();

    expect($builder->getBody())->toBeEmpty()
        ->and($builder->getQuery())->toBeEmpty()
        ->and($builder->getHeaders())->toBe([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ]);
});

test('every public method of an authenticated service resets the shared builder', function () {
    $services = glob(__DIR__.'/../../../src/Services/Authenticated/*.php');

    expect($services)->not->toBeEmpty();

    foreach ($services as $file) {
        $source = file_get_contents($file);
        $methods = preg_match_all(
            '/^\s+(?:final\s+)?public\s+function\s+(?!__construct)\w+\s*\(/m',
            $source
        );
        $resets = substr_count($source, '$this->request->reset();');

        expect($resets)->toBe(
            $methods,
            sprintf('%s: %d public methods but %d reset() calls', basename($file), $methods, $resets)
        );
    }
});
