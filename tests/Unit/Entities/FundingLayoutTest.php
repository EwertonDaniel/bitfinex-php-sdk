<?php

use EwertonDaniel\Bitfinex\Entities\FundingCredit;
use EwertonDaniel\Bitfinex\Entities\FundingLoan;
use EwertonDaniel\Bitfinex\Entities\FundingOffer;
use EwertonDaniel\Bitfinex\Entities\FundingTrade;

/**
 * Rows in the layouts the reference documents, checked on 2026-08-03. The
 * account used for verification has no funding history, so unlike the ledger and
 * funding-stats mappings these were not confirmed against a live response.
 */
function creditRow(): array
{
    return [
        26222883, 'fUST', 1, 1574013661000, 1574079687000, 350.0, null, 'ACTIVE', 'FIXED',
        null, null, 0.0024, 2, 1574013661000, 1574078487000, 0, null, null, 0, null, 0, 'tBTCUST',
    ];
}

function loanRow(): array
{
    // Same row as a credit, minus [21] POSITION_PAIR.
    return array_slice(creditRow(), 0, 21);
}

test('a funding credit is a funding loan plus POSITION_PAIR', function () {
    // The one structural difference between the two layouts. If a future edit
    // renames or reorders a field in one file and not the other, this fails.
    $credit = new FundingCredit(creditRow());
    $loan = new FundingLoan(loanRow());

    $shared = array_intersect(
        array_keys(get_object_vars($credit)),
        array_keys(get_object_vars($loan))
    );

    foreach ($shared as $field) {
        expect($credit->$field)->toEqual($loan->$field, "campo $field divergiu entre credit e loan");
    }

    $onlyOnCredit = array_diff(array_keys(get_object_vars($credit)), array_keys(get_object_vars($loan)));

    expect(array_values($onlyOnCredit))->toBe(['positionPair'])
        ->and($credit->positionPair)->toBe('tBTCUST');
});

test('a funding credit maps every documented index', function () {
    $credit = new FundingCredit(creditRow());

    expect($credit->id)->toBe(26222883)
        ->and($credit->symbol)->toBe('fUST')
        ->and($credit->currency)->toBe('UST')
        ->and($credit->side)->toBe(1)
        ->and($credit->createdAt->timestamp)->toBe(1574013661)
        ->and($credit->updatedAt->timestamp)->toBe(1574079687)
        ->and($credit->amount)->toBe(350.0)
        ->and($credit->status)->toBe('ACTIVE')
        ->and($credit->rateType)->toBe('FIXED')
        // [11] RATE, not [9] or [10], which are placeholders.
        ->and($credit->rate)->toBe(0.0024)
        ->and($credit->period)->toBe(2)
        ->and($credit->openedAt->timestamp)->toBe(1574013661)
        ->and($credit->lastPayoutAt->timestamp)->toBe(1574078487)
        ->and($credit->notify)->toBeFalse()
        ->and($credit->renew)->toBeFalse()
        ->and($credit->noClose)->toBeFalse();
});

test('a funding offer maps every documented index', function () {
    $offer = new FundingOffer([
        652606505, 'fETH', 1574000611000, 1574000611000, 0.29797676, 0.29797676, 'LIMIT',
        null, null, 0, 'ACTIVE', null, null, null, 0.0002, 2, 0, null, null, 0, null,
    ]);

    expect($offer->id)->toBe(652606505)
        ->and($offer->symbol)->toBe('fETH')
        ->and($offer->currency)->toBe('ETH')
        ->and($offer->amount)->toBe(0.29797676)
        ->and($offer->amountOrig)->toBe(0.29797676)
        ->and($offer->type)->toBe('LIMIT')
        ->and($offer->flags)->toBe(0)
        ->and($offer->status)->toBe('ACTIVE')
        // [14] RATE and [15] PERIOD sit after three placeholders.
        ->and($offer->rate)->toBe(0.0002)
        ->and($offer->period)->toBe(2)
        ->and($offer->renew)->toBeFalse();
});

test('a funding trade maps every documented index', function () {
    $trade = new FundingTrade([636040, 'fUST', 1574077528000, 41237922, -100.0, 0.0024, 2, null]);

    expect($trade->id)->toBe(636040)
        ->and($trade->symbol)->toBe('fUST')
        ->and($trade->currency)->toBe('UST')
        ->and($trade->createdAt->timestamp)->toBe(1574077528)
        ->and($trade->offerId)->toBe(41237922)
        // Negative means funds went out to a borrower.
        ->and($trade->amount)->toBe(-100.0)
        ->and($trade->rate)->toBe(0.0024)
        ->and($trade->period)->toBe(2);
});

test('a non-numeric FLAGS is dropped rather than coerced to zero', function () {
    // The reference calls FLAGS an object. Casting an array to int would yield
    // 1, which reads as a real flag set.
    $credit = new FundingCredit(array_replace(creditRow(), [6 => ['some' => 'object']]));

    expect($credit->flags)->toBeNull();
});

test('an empty row yields empty values instead of fataling', function (string $class) {
    $entity = new $class([]);

    expect($entity->id)->toBe(0)
        ->and($entity->symbol)->toBe('')
        ->and($entity->amount)->toBe(0.0);
})->with([FundingOffer::class, FundingCredit::class, FundingLoan::class, FundingTrade::class]);
