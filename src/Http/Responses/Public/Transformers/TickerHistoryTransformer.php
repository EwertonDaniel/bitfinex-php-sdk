<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Http\Responses\Public\Transformers;

use EwertonDaniel\Bitfinex\Entities\TickerHistory;
use EwertonDaniel\Bitfinex\Http\Responses\Public\Contracts\PublicTransformer;

/**
 * Groups ticker history by pair.
 */

class TickerHistoryTransformer implements PublicTransformer
{
    /**
     * @param array $context Contextual parameters.
     * @param mixed $content Decoded response content.
     * @return mixed Returns array<string, list<TickerHistory>>.
     */
    public function transform(array $context, mixed $content): mixed
    {
        return collect($content)
            ->map(fn ($history) => new TickerHistory($history))
            ->groupBy('pair')
            ->toArray();
    }
}
