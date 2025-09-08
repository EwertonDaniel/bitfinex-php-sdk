<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Http\Responses\Public\Transformers;

use EwertonDaniel\Bitfinex\Entities\Liquidation;
use EwertonDaniel\Bitfinex\Http\Responses\Public\Contracts\PublicTransformer;

/**
 * Normalizes liquidation rows and maps to Liquidation.
 */

class LiquidationsTransformer implements PublicTransformer
{
    /**
     * @param array $context Contextual parameters.
     * @param mixed $content Decoded response content.
     * @return mixed Returns array{liquidations: list<Liquidation>}.
     */
    public function transform(array $context, mixed $content): mixed
    {
        $normalize = function ($row) {
            if (is_array($row) && isset($row[0]) && is_array($row[0]) && count($row) === 1) {
                $row = $row[0];
            }
            if (is_array($row) && isset($row[0]) && $row[0] === 'pos') {
                return new Liquidation($row);
            }
            if (is_array($row) && count($row) >= 4) {
                $posRow = ['pos', $row[0], $row[1], null, null, $row[2], $row[3], null, null, null, null, null];
                return new Liquidation($posRow);
            }
            return new Liquidation($row);
        };
        return ['liquidations' => array_map($normalize, $content)];
    }
}
