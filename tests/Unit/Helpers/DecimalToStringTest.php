<?php

use EwertonDaniel\Bitfinex\Exceptions\BitfinexException;
use EwertonDaniel\Bitfinex\Helpers\DecimalToString;

test('it renders the value the caller actually passed', function (float $value, string $expected) {
    expect(DecimalToString::convert($value))->toBe($expected);
})->with([
    // These three are the regression: number_format() rendered them as
    // 37204349.70000001 / 40000000.10000001 / 81412329.10000001 on PHP 8.4,
    // so the SDK sent a different number than the caller wrote.
    'eight integer digits, one decimal' => [37204349.7, '37204349.7'],
    'round number plus a tenth' => [40000000.1, '40000000.1'],
    'the value 8.1 already mangled' => [81412329.1, '81412329.1'],
    'plain price' => [90000.0, '90000'],
    'negative amount' => [-1.5, '-1.5'],
    'whole float keeps no decimals' => [1.0, '1'],
    'zero' => [0.0, '0'],
]);

test('it never emits scientific notation', function (float|string $value, string $expected) {
    expect(DecimalToString::convert($value))->toBe($expected);
})->with([
    'float exponent' => [1.0E-7, '0.0000001'],
    'smallest representable amount' => [1.0E-8, '0.00000001'],
    'string exponent is normalized, not passed through' => ['1.0E-5', '0.00001'],
    'large exponent' => [1.0E+21, '1000000000000000000000'],
]);

test('it normalizes numeric strings', function () {
    expect(DecimalToString::convert('12.5'))->toBe('12.5')
        ->and(DecimalToString::convert('  12.5  '))->toBe('12.5')
        ->and(DecimalToString::convert('0.10000000'))->toBe('0.1');
});

test('it rejects a value that would reach the API as zero', function (float|string $value) {
    expect(fn () => DecimalToString::convert($value))->toThrow(BitfinexException::class);
})->with([
    'below the API scale' => [1.0E-9],
    'just under the rounding threshold' => [4.9E-9],
    'string below the API scale' => ['1e-9'],
]);

test('it rejects values that are not decimals', function (float|string $value) {
    expect(fn () => DecimalToString::convert($value))->toThrow(BitfinexException::class);
})->with([
    'not a number' => ['abc'],
    'comma decimal separator' => ['1,5'],
    'empty string' => [''],
    'NAN' => [NAN],
    'INF' => [INF],
    '-INF' => [-INF],
]);

test('it rounds to the eight decimals the API accepts', function () {
    expect(DecimalToString::convert(0.123456789))->toBe('0.12345679')
        ->and(DecimalToString::convert(0.999999999))->toBe('1')
        ->and(DecimalToString::convert('1.005000004'))->toBe('1.005');
});

test('it passes null through', function () {
    expect(DecimalToString::convert(null))->toBeNull();
});

test('every output converts back to the value it came from', function () {
    // 8 integer digits is the band where 8.1 and 8.4 disagreed.
    $values = [37204349.7, 40000000.1, 81412329.1, 12345678.9, 99999999.5, 1.5, 0.0000001];

    foreach ($values as $value) {
        $rendered = DecimalToString::convert($value);
        expect((float) $rendered)->toBe($value, "round-trip failed for $value (rendered as $rendered)");
    }
});
