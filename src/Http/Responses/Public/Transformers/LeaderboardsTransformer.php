<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Http\Responses\Public\Transformers;

use EwertonDaniel\Bitfinex\Entities\LeaderboardEntry;
use EwertonDaniel\Bitfinex\Http\Responses\Public\Contracts\PublicTransformer;

/**
 * Maps leaderboard rows to LeaderboardEntry.
 */

class LeaderboardsTransformer implements PublicTransformer
{
    /**
     * @param array $context Contextual parameters.
     * @param mixed $content Decoded response content.
     * @return mixed Returns array{key,timeframe,symbol,section,items}.
     */
    public function transform(array $context, mixed $content): mixed
    {
        return [
            'key' => (string) $context['key'],
            'timeframe' => (string) $context['timeframe'],
            'symbol' => (string) $context['symbol'],
            'section' => (string) $context['section'],
            'items' => array_map(fn ($row) => new LeaderboardEntry($row), $content),
        ];
    }
}
