<?php

use EwertonDaniel\Bitfinex\Entities\FundingStat;
use EwertonDaniel\Bitfinex\Http\Responses\PublicBitfinexResponse;
use GuzzleHttp\Psr7\Response;

test('maps funding stats to FundingStat entities', function () {
    // The row starts with MTS, not with the symbol: the symbol is a path
    // segment, never a field.
    $payload = [
        [1785787500000, null, null, 0.00000093, 87.32, null, null, 6052202237.59, 5989881192.94, null, null, 642092743.35],
        [1785787200000, null, null, 0.00000187, 68.88, null, null, 3230660371.44, 2988430006.46, null, null, 1447938775.57],
    ];

    $resp = (new PublicBitfinexResponse(new Response(200, [], json_encode($payload))))
        ->fundingStats('fUSD');

    expect($resp->content['items'])
        ->toBeArray()
        ->toHaveCount(2)
        ->and($resp->content['items'][0])->toBeInstanceOf(FundingStat::class)
        ->and($resp->content['items'][0]->mts->timestamp)->toBe(1785787500)
        ->and($resp->content['items'][0]->frr)->toBe(0.00000093)
        ->and($resp->content['items'][0]->averagePeriod)->toBe(87.32)
        ->and($resp->content['items'][0]->fundingAmount)->toBe(6052202237.59)
        ->and($resp->content['items'][0]->fundingAmountUsed)->toBe(5989881192.94)
        ->and($resp->content['items'][0]->fundingBelowThreshold)->toBe(642092743.35);
});

test('FRR is scaled from the 1/365th the API sends', function () {
    // Reading [3] as a daily rate understates it by a factor of 365.
    $stat = new FundingStat([1785787500000, null, null, 0.00000093, 87.32, null, null, 1.0, 1.0, null, null, 1.0]);

    expect($stat->frr)->toBe(0.00000093)
        ->and(round($stat->dailyRate(), 8))->toBe(0.00033945)
        ->and(round($stat->annualRate(), 6))->toBe(0.123899);
});

test('a row without an FRR yields null rather than zero', function () {
    $stat = new FundingStat([1785787500000]);

    expect($stat->frr)->toBeNull()
        ->and($stat->dailyRate())->toBeNull()
        ->and($stat->annualRate())->toBeNull();
});
