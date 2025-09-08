<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Http\Responses\Public\Transformers;

use EwertonDaniel\Bitfinex\Entities\FundingStat;
use EwertonDaniel\Bitfinex\Http\Responses\Public\Contracts\PublicTransformer;

/**
 * Maps funding stats rows to FundingStat.
 */

class FundingStatsTransformer implements PublicTransformer
{
    /**
     * @param array $context Contextual parameters.
     * @param mixed $content Decoded response content.
     * @return mixed Returns array{symbol, items: list<FundingStat>}.
     */
    public function transform(array $context, mixed $content): mixed
    {
        return [
            'symbol' => (string) $context['symbol'],
            'items' => array_map(fn ($row) => new FundingStat($row), $content),
        ];
    }
}
