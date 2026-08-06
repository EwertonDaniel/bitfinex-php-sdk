<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Entities;

use Carbon\Carbon;
use EwertonDaniel\Bitfinex\Helpers\GetThis;

/**
 * Class LeaderboardEntry
 *
 * One row of a public rankings board.
 *
 * This is the one entity where the reference and the wire disagree, checked on
 * 2026-08-03 against `v2/rankings/vol:1M:tGLOBAL:USD/hist`:
 *
 * - the reference documents **10** fields, the API returns **13**;
 * - [8] is documented as a placeholder but carries an integer;
 * - [9] TWITTER_HANDLE is documented as a string and comes back null;
 * - [10] through [12] are not documented at all.
 *
 * So only the four fields that are documented *and* confirmed on the wire get
 * named accessors. The untouched row is kept in `$raw`, which is what makes the
 * undocumented tail reachable without this class pretending to know what it
 * means. That is the modelled variant the constitution asks for, not a
 * "schema varies" shrug.
 *
 * @author Ewerton Daniel
 *
 * @contact contact@ewertondaniel.work
 *
 * @link https://docs.bitfinex.com/reference/rest-public-rankings
 */
class LeaderboardEntry
{
    /** When the ranking was calculated. */
    public readonly ?Carbon $mts;

    /** Display name of the ranked account. */
    public readonly ?string $username;

    /** Position on the board, 1 being first. */
    public readonly ?int $ranking;

    /**
     * The metric the board ranks by.
     *
     * What it measures depends on the key that was requested: unrealised profit,
     * realised profit, or volume.
     */
    public readonly ?float $value;

    /**
     * Twitter handle, when the account published one.
     *
     * Documented as a string; every live row checked returned null.
     */
    public readonly ?string $twitterHandle;

    /**
     * The row exactly as the API sent it.
     *
     * Three fields past [9] are undocumented and unnamed here. Read them from
     * this array if you need them, and expect them to move.
     *
     * @var array<int,mixed>
     */
    public readonly array $raw;

    /**
     * @param  array  $data  Rankings row:
     *                       - [0]: Timestamp (milliseconds).
     *                       - [1]: PLACEHOLDER.
     *                       - [2]: Username.
     *                       - [3]: Ranking.
     *                       - [4]: PLACEHOLDER.
     *                       - [5]: PLACEHOLDER.
     *                       - [6]: Value of the ranked metric.
     *                       - [7]: PLACEHOLDER.
     *                       - [8]: Documented as PLACEHOLDER; an integer on the wire.
     *                       - [9]: Twitter handle.
     *                       - [10]-[12]: Undocumented; reachable through `$raw`.
     */
    public function __construct(array $data)
    {
        $this->raw = $data;
        $this->mts = GetThis::ifTrueOrFallback(isset($data[0]), fn () => Carbon::createFromTimestampMs((int) $data[0]));
        $this->username = GetThis::ifTrueOrFallback(isset($data[2]), fn () => (string) $data[2]);
        $this->ranking = GetThis::ifTrueOrFallback(is_numeric($data[3] ?? null), fn () => (int) $data[3]);
        $this->value = GetThis::ifTrueOrFallback(is_numeric($data[6] ?? null), fn () => (float) $data[6]);
        $this->twitterHandle = GetThis::ifTrueOrFallback(isset($data[9]), fn () => (string) $data[9]);
    }
}
