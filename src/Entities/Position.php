<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Entities;

use EwertonDaniel\Bitfinex\Helpers\GetThis;

/**
 * Position entity for the authenticated position endpoints.
 *
 * Documented layout (20 elements):
 * [0] SYMBOL (string)
 * [1] STATUS (string)
 * [2] AMOUNT (float)
 * [3] BASE_PRICE (float)
 * [4] FUNDING (float)
 * [5] FUNDING_TYPE (int) 0 = daily, 1 = term
 * [6] PL (float)
 * [7] PL_PERC (float)
 * [8] PRICE_LIQ (float)
 * [9] LEVERAGE (float)
 * [10] _PLACEHOLDER
 * [11] POSITION_ID (int)
 * [12] MTS_CREATE (int, ms)
 * [13] MTS_UPDATE (int, ms)
 * [14] _PLACEHOLDER
 * [15] TYPE (int) 0 = margin, 1 = derivatives
 * [16] _PLACEHOLDER
 * [17] COLLATERAL (float)
 * [18] COLLATERAL_MIN (float)
 * [19] META (JSON string)
 *
 * `positionId` in particular used to be unmapped, which made `retrieve()` unable
 * to feed `claim()` or `increase()` — both take the id this entity now exposes.
 *
 * The `/hist` and `/snap` endpoints share indices [0]-[13] but document [6]-[10]
 * as placeholders, so `pl`, `plPerc`, `priceLiq` and `leverage` are null there.
 *
 * @author  Ewerton Daniel
 *
 * @contact contact@ewertondaniel.work
 *
 * @link https://docs.bitfinex.com/reference/rest-auth-positions
 */
class Position
{
    public readonly ?string $symbol;

    public readonly ?string $status;

    public readonly ?float $amount;

    public readonly ?float $basePrice;

    public readonly ?float $funding;

    public readonly ?int $fundingType;

    public readonly ?float $pl;

    public readonly ?float $plPerc;

    public readonly ?float $priceLiq;

    public readonly ?float $leverage;

    public readonly ?int $positionId;

    public readonly ?int $mtsCreate;

    public readonly ?int $mtsUpdate;

    public readonly ?int $type;

    public readonly ?float $collateral;

    public readonly ?float $collateralMin;

    public readonly ?string $meta;

    public function __construct(array $data)
    {
        $this->symbol = GetThis::ifTrueOrFallback(isset($data[0]), fn () => (string) $data[0]);
        $this->status = GetThis::ifTrueOrFallback(isset($data[1]), fn () => (string) $data[1]);
        $this->amount = GetThis::ifTrueOrFallback(isset($data[2]), fn () => (float) $data[2]);
        $this->basePrice = GetThis::ifTrueOrFallback(isset($data[3]), fn () => (float) $data[3]);
        $this->funding = GetThis::ifTrueOrFallback(isset($data[4]), fn () => (float) $data[4]);
        $this->fundingType = GetThis::ifTrueOrFallback(isset($data[5]), fn () => (int) $data[5]);
        $this->pl = GetThis::ifTrueOrFallback(isset($data[6]), fn () => (float) $data[6]);
        $this->plPerc = GetThis::ifTrueOrFallback(isset($data[7]), fn () => (float) $data[7]);
        $this->priceLiq = GetThis::ifTrueOrFallback(isset($data[8]), fn () => (float) $data[8]);
        $this->leverage = GetThis::ifTrueOrFallback(isset($data[9]), fn () => (float) $data[9]);
        $this->positionId = GetThis::ifTrueOrFallback(isset($data[11]), fn () => (int) $data[11]);
        $this->mtsCreate = GetThis::ifTrueOrFallback(isset($data[12]), fn () => (int) $data[12]);
        $this->mtsUpdate = GetThis::ifTrueOrFallback(isset($data[13]), fn () => (int) $data[13]);
        $this->type = GetThis::ifTrueOrFallback(isset($data[15]), fn () => (int) $data[15]);
        $this->collateral = GetThis::ifTrueOrFallback(isset($data[17]), fn () => (float) $data[17]);
        $this->collateralMin = GetThis::ifTrueOrFallback(isset($data[18]), fn () => (float) $data[18]);
        $this->meta = GetThis::ifTrueOrFallback(isset($data[19]), fn () => (string) $data[19]);
    }
}
