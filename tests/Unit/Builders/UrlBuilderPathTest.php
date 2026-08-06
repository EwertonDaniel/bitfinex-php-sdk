<?php

use EwertonDaniel\Bitfinex\Builders\UrlBuilder;

test('a path parameter cannot escape its own segment', function (string $value, string $expected) {
    $path = (new UrlBuilder)->setPath('private.orders.ledgers', ['currency' => $value])->getPath();

    expect($path)->toBe($expected);
})->with([
    // A raw "?" truncated the path, so the URI on the wire stopped matching the
    // one that was signed and the request came back as "10100 digest invalid".
    'query separator' => ['USD?evil=1', 'v2/auth/r/ledgers/USD%3Fevil=1/hist'],
    // A raw "#" dropped everything after it: the request landed on
    // /v2/auth/r/ledgers/USD, an endpoint the caller never asked for.
    'fragment separator' => ['USD#frag', 'v2/auth/r/ledgers/USD%23frag/hist'],
    // Guzzle encoded the space on send while the signature covered the raw character.
    'whitespace' => ['US D', 'v2/auth/r/ledgers/US%20D/hist'],
    // Traversal turned a read into /v2/auth/w/order/cancel, a write endpoint.
    'path traversal' => ['USD/../../../w/order/cancel', 'v2/auth/r/ledgers/USD%2F..%2F..%2F..%2Fw%2Forder%2Fcancel/hist'],
]);

test('characters a path segment allows are left alone', function (string $path, array $params, string $expected) {
    expect((new UrlBuilder)->setPath($path, $params)->getPath())->toBe($expected);
})->with([
    // ":" is legal in a path segment per RFC 3986 and is load-bearing for
    // derivative symbols and configuration keys. Encoding it would break them.
    'derivative symbol' => [
        'public.derivatives_status_history',
        ['symbol' => 'tBTCF0:USTF0'],
        'v2/status/deriv/tBTCF0:USTF0/hist',
    ],
    'configuration key' => [
        'public.configs',
        ['keys' => 'pub:list:pair:exchange'],
        'v2/conf/pub:list:pair:exchange',
    ],
    'stats key carries a dot' => [
        'public.stats_one',
        ['key' => 'pos.size', 'size' => '1m', 'sym_platform' => 'tBTCUSD', 'side_pair' => 'long', 'section' => 'hist'],
        'v2/stats1/pos.size:1m:tBTCUSD:long/hist',
    ],
    'plain symbol' => [
        'private.orders.ledgers',
        ['currency' => 'USD'],
        'v2/auth/r/ledgers/USD/hist',
    ],
]);
