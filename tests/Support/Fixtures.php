<?php

declare(strict_types=1);

namespace Tests\Support;

/**
 * Response payloads for the authenticated endpoints, in the positional layouts
 * the official reference documents.
 *
 * Provenance, so nobody has to guess later:
 *
 * - The layouts mirror the entity mappings in `src/Entities`, which the audit of
 *   2026-08-02 checked index by index against `docs.bitfinex.com`.
 * - `alertDelete` (`[true]`, a boolean rather than `1`) and `balanceAvailable`
 *   (`[0.8056309]`, a float) were re-confirmed against the reference on
 *   2026-08-03, because their assertions are type sensitive.
 * - `token` follows the SDK's own mapper, which reads the JWT at index 0. It has
 *   no public reference page to check it against.
 * - Indices no mapping reads are filled with `null`, never shifted. Where a value
 *   is arbitrary it is still plausible: prices, amounts and timestamps are real
 *   magnitudes, so an off-by-one in a mapping shows up as a wrong value rather
 *   than as another null.
 */
final class Fixtures
{
    /** `v2/auth/w/token` */
    public static function token(): array
    {
        return ['eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.test-token.signature'];
    }

    /** `v2/auth/r/permissions` */
    public static function keyPermissions(): array
    {
        return [
            ['account', 1, 0],
            ['orders', 1, 1],
            ['funding', 1, 0],
            ['wallets', 1, 0],
            ['withdraw', 0, 0],
        ];
    }

    /** `v2/auth/r/info/user` */
    public static function userInfo(): array
    {
        $row = array_fill(0, 55, null);

        $row[0] = 1234567;                  // ID
        $row[1] = 'trader@example.com';     // EMAIL
        $row[2] = 'trader';                 // USERNAME
        $row[3] = 1483228800000;            // MTS_ACCOUNT_CREATE
        $row[4] = 1;                        // VERIFIED
        $row[5] = 3;                        // VERIFICATION_LEVEL
        $row[7] = 'Europe/Lisbon';          // TIMEZONE
        $row[8] = 'en-US';                  // LOCALE
        $row[9] = 'bitfinex';               // COMPANY
        $row[10] = 1;                       // EMAIL_VERIFIED
        $row[15] = 987654;                  // GROUP_ID
        $row[18] = 1;                       // IS_GROUP_MASTER
        $row[22] = 1;                       // MERCHANT_ENABLED
        $row[26] = ['otp'];                 // 2FA modes
        $row[44] = '2026-08-01 10:00:00';   // TIME_LAST_LOGIN
        $row[47] = 3;                       // VERIFICATION_LEVEL_SUBMITTED

        return $row;
    }

    /** `v2/auth/r/logins/hist` */
    public static function loginHistory(): array
    {
        return [
            [1, null, 1754006400000, null, '203.0.113.10', null, null, '{"user_agent":{"browser":"Firefox"}}'],
            [2, null, 1753920000000, null, '203.0.113.11', null, null, '{"user_agent":{"browser":"Chrome"}}'],
        ];
    }

    /** `v2/auth/r/summary` */
    public static function summary(): array
    {
        $row = array_fill(0, 10, null);

        // [4] fee info: maker row then taker row, each [0][1][2] and [5].
        $row[4] = [
            [0.001, 0.001, 0.001, null, null, 0.0002],
            [0.002, 0.002, 0.002, null, null, 0.00065],
        ];
        // [5] trading volume and fees for the month. The first two are objects
        // keyed by currency, only the third is a scalar.
        $row[5] = [
            [['curr' => 'Total (USD)', 'vol' => 125000.5]],
            [['curr' => 'USD', 'fee' => 250.75]],
            300.10,
        ];
        // [6] funding earnings: per currency, then total.
        $row[6] = [null, ['USD' => 12.5], 12.5];
        // [9] LEO info, an object rather than a positional row.
        $row[9] = ['leo_lev' => 2, 'leo_amount_avg' => 5000.0];

        return $row;
    }

    /** `v2/auth/r/audit/hist` */
    public static function changelog(): array
    {
        return [
            [
                1754006400000, null, 'Password changed', null, null, '203.0.113.10',
                'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0 Safari/537.36',
            ],
        ];
    }

    /**
     * `v2/auth/w/deposit/address` — a notification whose DATA holds the address
     * block at [4], itself positional: method at [1], currency at [2], address at
     * [4], pool address at [5].
     */
    public static function depositAddress(): array
    {
        return [
            1754006400000,
            'acc_dep',
            null,
            null,
            [null, 'monero', 'XMR', null, '44AFFq5kSiGBoZ4NMDwYtN18obc8AemS33DBLWs3H7otXft3XjrpDtQGv7SqSsaBYBb98uNbr2VBBEt7f2wfn3RVGQBEP3A', null],
            null,
            'SUCCESS',
            'Success',
        ];
    }

    /** `v2/auth/r/deposit/address/list` — pairs of [address, wallet type]. */
    public static function depositAddressList(): array
    {
        return [
            ['44AFFq5kSiGBoZ4NMDwYtN18obc8AemS33DBLWs3H7otXft3XjrpDtQGv7SqSsaBYBb98uNbr2VBBEt7f2wfn3RVGQBEP3A', 'exchange'],
            ['48daf1rG3hE1Txapcsxh6WXNe9MLNKtu7W7tKTivtSoVLHErYzvdcpea2nSTgGkz66RFP4GKVAsTV14v6G3oddBTHfxP6tQ', 'margin'],
        ];
    }

    /** `v2/auth/r/movements/{currency}/hist` */
    public static function movements(): array
    {
        $row = array_fill(0, 32, null);

        $row[0] = 987654321;            // ID
        $row[1] = 'UST';                // CURRENCY
        $row[2] = 'TETHERUSE';          // CURRENCY_NAME
        $row[4] = 'remark';             // REMARK
        $row[5] = 1754006400000;        // MTS_STARTED
        $row[6] = 1754006460000;        // MTS_UPDATED
        $row[9] = 'COMPLETED';          // STATUS
        $row[12] = -250.0;              // AMOUNT (negative: a withdrawal)
        $row[13] = -1.5;                // FEES
        $row[16] = '0x0000000000000000000000000000000000dEaD';
        $row[20] = '0xdeadbeef';        // TRANSACTION_ID

        $deposit = $row;
        $deposit[0] = 987654322;
        $deposit[12] = 500.0;           // AMOUNT (positive: a deposit)
        $deposit[13] = 0.0;

        return [$row, $deposit];
    }

    /** `v2/auth/w/alert/set` and the rows of `v2/auth/r/alerts` */
    public static function alert(float $price = 250.0): array
    {
        return ['price', 'price', 'tXMRUSD', $price, 100];
    }

    /** `v2/auth/r/alerts` */
    public static function alertList(): array
    {
        return [self::alert(250.0), self::alert(300.0)];
    }

    /** `v2/auth/w/alert/price:{symbol}:{price}/del` — a boolean, not a 1. */
    public static function alertDelete(): array
    {
        return [true];
    }

    /** `v2/auth/calc/order/avail` */
    public static function balanceAvailable(): array
    {
        return [0.8056309];
    }

    /** `v2/auth/r/wallets` */
    public static function wallets(): array
    {
        return [
            ['exchange', 'UST', 1500.25, 0, 1500.25, 'Trading fees for 0.02 XMR', null],
            ['margin', 'USD', 300.0, 0, 250.0, null, null],
            ['funding', 'USD', 1000.0, 0.5, 1000.0, null, null],
        ];
    }

    /** A full order row, [0]-[31], as the order endpoints return it. */
    public static function order(int $id = 1234567, string $status = 'ACTIVE', string $symbol = 'tXMRUST'): array
    {
        return [
            $id, null, 1568124312, $symbol, 1754006400000, 1754006400000,
            0.02, 0.02, 'EXCHANGE LIMIT', null, null, null,
            0, $status, null, null,
            140.0, 0.0, 0.0, 0.0,
            null, null, null, 0, 0, null, null, null,
            'API>BFX', null, null, [],
        ];
    }

    /** `v2/auth/r/orders` */
    public static function orders(): array
    {
        return [self::order(1234567), self::order(1234568, 'PARTIALLY FILLED')];
    }

    /**
     * A notification envelope, [0]-[7]. DATA at [4] is endpoint specific, which is
     * exactly why the mappers no longer guess it.
     */
    public static function notification(mixed $data, string $status = 'SUCCESS', string $type = 'on-req', string $text = 'Success', int|float $mts = 1754006400000): array
    {
        return [$mts, $type, null, null, $data, null, $status, $text];
    }

    /** An error envelope, which the API sends with HTTP 500. */
    public static function error(int $code = 10001, string $message = 'Invalid order: not enough exchange balance'): array
    {
        return ['error', $code, $message];
    }
}
