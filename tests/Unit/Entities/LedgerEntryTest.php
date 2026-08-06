<?php

use EwertonDaniel\Bitfinex\Entities\LedgerEntry;

test('it maps the ledger row by index, placeholders skipped', function () {
    // Shape taken from a live v2/auth/r/ledgers/USD/hist response on 2026-08-03.
    // [4] and [7] are placeholders: reading AMOUNT from [4] would give null and
    // shift BALANCE onto AMOUNT.
    $entry = new LedgerEntry([
        2531822314,
        'USD',
        'margin',
        1573521810000,
        null,
        0.01644445,
        1234.5,
        null,
        'Settlement @ 185.79 on wallet margin',
    ]);

    expect($entry->id)->toBe(2531822314)
        ->and($entry->currency)->toBe('USD')
        ->and($entry->wallet)->toBe('margin')
        ->and($entry->mts->timestamp)->toBe(1573521810)
        ->and($entry->amount)->toBe(0.01644445)
        ->and($entry->balance)->toBe(1234.5)
        ->and($entry->description)->toBe('Settlement @ 185.79 on wallet margin');
});

test('a negative amount is preserved, since the sign is the direction', function () {
    $entry = new LedgerEntry([1, 'USD', 'exchange', 1573521810000, null, -50.25, 100.0, null, 'Exchange']);

    expect($entry->amount)->toBe(-50.25)
        ->and($entry->balance)->toBe(100.0);
});

test('a missing wallet or description is null rather than an empty string', function () {
    $entry = new LedgerEntry([1, 'USD', null, 1573521810000, null, 1.0, 2.0, null, null]);

    expect($entry->wallet)->toBeNull()
        ->and($entry->description)->toBeNull();
});

test('an empty row yields empty values instead of fataling', function () {
    $entry = new LedgerEntry([]);

    expect($entry->id)->toBe(0)
        ->and($entry->currency)->toBe('')
        ->and($entry->mts)->toBeNull()
        ->and($entry->amount)->toBe(0.0);
});
