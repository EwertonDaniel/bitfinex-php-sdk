<?php

namespace EwertonDaniel\Bitfinex\Entities;

/**
 * Class LeoInfo
 *
 * Represents information about the LEO holdings on the Bitfinex platform.
 * Provides structured data for:
 * - Current LEO level.
 * - Average LEO amount held over the past 30 days.
 *
 * @author Ewerton Daniel
 * @contact contact@ewertondaniel.work
 */
class LeoInfo
{
    /** Current LEO level. */
    public readonly float $leoLevel;

    /** Average LEO amount held over the past 30 days. */
    public readonly float $leoAmountAvg;

    /**
     * Constructs a LeoInfo entity using provided data.
     *
     * @param array $data Array containing:
     *                       - ['leo_lev']: Current LEO level.
     *                       - ['leo_amount_avg']: Average LEO amount over the past 30 days.
     */
    public function __construct(array $data)
    {
        $this->leoLevel = $data['leo_lev'];
        $this->leoAmountAvg = $data['leo_amount_avg'];
    }
}
