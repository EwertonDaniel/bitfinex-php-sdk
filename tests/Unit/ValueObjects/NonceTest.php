<?php

use EwertonDaniel\Bitfinex\Contracts\NonceProvider;
use EwertonDaniel\Bitfinex\Exceptions\BitfinexException;
use EwertonDaniel\Bitfinex\ValueObjects\BitfinexSignature;
use EwertonDaniel\Bitfinex\ValueObjects\LockedFileNonceProvider;

afterEach(function () {
    // Static state: leaving a provider installed would leak into the next test.
    BitfinexSignature::useNonceProvider(null);
});

function nonceFile(): string
{
    return sys_get_temp_dir().'/bfx-nonce-'.getmypid().'-'.hrtime(true);
}

test('the signature follows the documented recipe', function () {
    // /api/{apiPath}{nonce}{body}, HMAC-SHA384, hex.
    $signature = new BitfinexSignature(
        apiPath: 'v2/auth/r/wallets',
        body: '{"limit":10}',
        apiSecret: 'test-secret'
    );

    $expected = hash_hmac(
        'sha384',
        '/api/v2/auth/r/wallets'.$signature->nonce.'{"limit":10}',
        'test-secret'
    );

    expect($signature->signature)->toBe($expected)
        ->and($signature->signature)->toHaveLength(96);
});

test('the in-process nonce never repeats or goes backwards', function () {
    // microtime() alone is not fine grained enough for consecutive calls, and
    // the API refuses any nonce that is not strictly greater than the last.
    $nonces = array_map(fn () => (int) BitfinexSignature::nonce(), range(1, 500));

    $sorted = $nonces;
    sort($sorted);

    expect(array_unique($nonces))->toHaveCount(500)
        ->and($nonces)->toBe($sorted)
        ->and(max($nonces))->toBeLessThan(NonceProvider::MAX_NONCE);
});

test('an installed provider takes over', function () {
    $provider = new class implements NonceProvider
    {
        public int $calls = 0;

        public function next(): int
        {
            return 1_000_000 + ++$this->calls;
        }
    };

    BitfinexSignature::useNonceProvider($provider);

    expect(BitfinexSignature::nonce())->toBe('1000001')
        ->and((new BitfinexSignature('v2/auth/r/wallets', '', 's'))->nonce)->toBe('1000002')
        ->and($provider->calls)->toBe(2);
});

test('passing null restores the in-process counter', function () {
    BitfinexSignature::useNonceProvider(new class implements NonceProvider
    {
        public function next(): int
        {
            return 42;
        }
    });

    expect(BitfinexSignature::nonce())->toBe('42');

    BitfinexSignature::useNonceProvider(null);

    expect((int) BitfinexSignature::nonce())->toBeGreaterThan(1_000_000_000_000_000);
});

test('the file provider hands out a strictly increasing sequence', function () {
    $path = nonceFile();
    $provider = new LockedFileNonceProvider($path);

    $nonces = array_map(fn () => $provider->next(), range(1, 100));
    $sorted = $nonces;
    sort($sorted);

    expect(array_unique($nonces))->toHaveCount(100)
        ->and($nonces)->toBe($sorted)
        // The file holds the last value, which is what makes it survive a restart.
        ->and((int) file_get_contents($path))->toBe(end($nonces));

    unlink($path);
});

test('the file provider never rewinds below what a previous run stored', function () {
    // A key already used with the in-process counter must not go backwards when
    // the shared counter takes over, or the exchange refuses everything until
    // clock time catches up.
    $path = nonceFile();
    $ahead = (int) round(microtime(true) * 1_000_000) + 5_000_000;
    file_put_contents($path, (string) $ahead);

    expect((new LockedFileNonceProvider($path))->next())->toBeGreaterThan($ahead);

    unlink($path);
});

test('two separate provider instances on one file stay ordered', function () {
    // Stands in for two processes: distinct objects, no shared memory, one file.
    $path = nonceFile();
    $a = new LockedFileNonceProvider($path);
    $b = new LockedFileNonceProvider($path);

    $nonces = [$a->next(), $b->next(), $a->next(), $b->next()];
    $sorted = $nonces;
    sort($sorted);

    expect($nonces)->toBe($sorted)
        ->and(array_unique($nonces))->toHaveCount(4);

    unlink($path);
});

test('an unwritable path fails loudly rather than silently reusing a nonce', function () {
    // Falling back to an in-process value here would reintroduce the very
    // collision the shared counter exists to prevent.
    (new LockedFileNonceProvider('/proc/nonexistent-directory/nonce'))->next();
})->throws(BitfinexException::class, 'missing or not writable');
