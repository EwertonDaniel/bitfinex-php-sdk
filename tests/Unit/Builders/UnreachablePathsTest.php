<?php

use EwertonDaniel\Bitfinex\Builders\UrlBuilder;
use EwertonDaniel\Bitfinex\Services\BitfinexPublic;
use EwertonDaniel\Bitfinex\Services\Public\BitfinexPublicCandles;
use EwertonDaniel\Bitfinex\Services\Public\BitfinexPublicLeaderboards;
use EwertonDaniel\Bitfinex\Services\Public\BitfinexPublicStats;

/**
 * Four groups of endpoints were unreachable through this SDK, all of them
 * failing the same way: HTTP 200 with a literal `null` or an empty array, never
 * an error. Verified against api-pub.bitfinex.com on 2026-08-03.
 */
function publicPath(string $name, array $params = []): string
{
    return (new UrlBuilder)->setBaseUrl('public')->setPath($name, $params)->getPath();
}

test('stats keys that take three segments build a three segment path', function (string $key, string $symbol) {
    // `funding.size:1m:fUSD:` — note the trailing colon — answers null.
    $path = publicPath('public.stats_one_no_side', [
        'key' => $key,
        'size' => '1m',
        'sym_platform' => $symbol,
        'section' => 'hist',
    ]);

    expect($path)->toBe("v2/stats1/$key:1m:$symbol/hist")
        ->and($path)->not->toContain('::')
        ->and($path)->not->toContain(':/');
})->with([
    'funding size' => ['funding.size', 'fUSD'],
    'credits size' => ['credits.size', 'fUSD'],
    'daily volume' => ['vol.1d', 'BFX'],
    'vwap' => ['vwap', 'tBTCUSD'],
]);

test('the two keys that do take a fourth segment still get one', function (string $key, string $symbol, string $fourth) {
    $path = publicPath('public.stats_one', [
        'key' => $key,
        'size' => '1m',
        'sym_platform' => $symbol,
        'side_pair' => $fourth,
        'section' => 'hist',
    ]);

    expect($path)->toBe("v2/stats1/$key:1m:$symbol:$fourth/hist");
})->with([
    'position size by side' => ['pos.size', 'tBTCUSD', 'long'],
    'credits size by pair' => ['credits.size.sym', 'fUSD', 'tBTCUSD'],
]);

test('sidePair is optional on the stats entry point', function () {
    // It used to be required, so there was no way to ask for a three segment key.
    $sidePair = (new ReflectionMethod(BitfinexPublic::class, 'stats'))->getParameters()[2];

    expect($sidePair->getName())->toBe('sidePair')
        ->and($sidePair->isOptional())->toBeTrue()
        ->and($sidePair->getType()->allowsNull())->toBeTrue();
});

test('funding candles carry a period, since without one the API returns null', function (string $period, string $expected) {
    $candles = new BitfinexPublicCandles(new GuzzleHttp\Client, new UrlBuilder, '1m', 'hist');

    // The period reaches the path through the symbol segment.
    $symbol = (new ReflectionMethod($candles, 'byCurrency'))->getParameters()[1];

    expect($symbol->getName())->toBe('period')
        ->and($symbol->getDefaultValue())->toBe('a30:p2:p30');

    $path = publicPath('public.candles', ['timeframe' => '1m', 'symbol' => "fUSD:$period", 'section' => 'hist']);

    expect($path)->toBe($expected);
})->with([
    'aggregated range' => ['a30:p2:p30', 'v2/candles/trade:1m:fUSD:a30:p2:p30/hist'],
    'single period' => ['p30', 'v2/candles/trade:1m:fUSD:p30/hist'],
]);

test('the period colons survive path encoding', function () {
    // Segment encoding has to leave ':' alone or the period breaks the path,
    // while still containing a '/' so it cannot escape its own segment.
    $path = publicPath('public.candles', [
        'timeframe' => '1m',
        'symbol' => 'fUSD:a30:p2:p30',
        'section' => 'hist',
    ]);

    expect($path)->toContain(':a30:p2:p30')
        ->and(publicPath('public.candles', [
            'timeframe' => '1m',
            'symbol' => 'fUSD/../../v2/auth/w/order/cancel',
            'section' => 'hist',
        ]))->not->toContain('/../');
});

test('rankings by currency address the global board, not a funding symbol', function () {
    // `vol:1M:fUSD` answers an empty array: rankings are not a funding endpoint.
    $leaderboards = new BitfinexPublicLeaderboards(new GuzzleHttp\Client, new UrlBuilder, 'vol', '1M', 'hist');

    $method = new ReflectionMethod($leaderboards, 'byCurrency');

    expect($method->getParameters()[0]->getName())->toBe('currency');

    $path = publicPath('public.leaderboards', [
        'key' => 'vol',
        'timeframe' => '1M',
        'symbol' => 'tGLOBAL:USD',
        'section' => 'hist',
    ]);

    expect($path)->toBe('v2/rankings/vol:1M:tGLOBAL:USD/hist');
});

test('ledgers can be queried without a currency', function () {
    $url = (new UrlBuilder)->setBaseUrl('private');

    expect($url->setPath('private.orders.ledgers', ['currency' => 'USD'])->getPath())
        ->toBe('v2/auth/r/ledgers/USD/hist')
        // Only the {currency} variant existed, so an account-wide query had no path.
        ->and($url->setPath('private.orders.ledgers_all')->getPath())
        ->toBe('v2/auth/r/ledgers/hist');
});

test('the currency argument of ledgers is optional', function () {
    $currency = (new ReflectionMethod(
        EwertonDaniel\Bitfinex\Services\Authenticated\BitfinexAuthenticatedOrder::class,
        'ledgers'
    ))->getParameters()[0];

    expect($currency->getName())->toBe('currency')
        ->and($currency->isOptional())->toBeTrue()
        ->and($currency->getType()->allowsNull())->toBeTrue();
});

test('the stats service accepts a null fourth segment', function () {
    $sidePair = (new ReflectionMethod(BitfinexPublicStats::class, '__construct'))->getParameters()[4];

    expect($sidePair->getName())->toBe('sidePair')
        ->and($sidePair->getType()->allowsNull())->toBeTrue();
});
