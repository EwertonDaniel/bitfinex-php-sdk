<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Http\Responses\Public\Transformers;

use EwertonDaniel\Bitfinex\Entities\Stat;
use EwertonDaniel\Bitfinex\Http\Responses\Public\Contracts\PublicTransformer;

/**
 * Maps public stats payload to Stat entities with metadata.
 */

class StatsTransformer implements PublicTransformer
{
    /**
     * @param array $context Contextual parameters.
     * @param mixed $content Decoded response content.
     * @return mixed Returns array{key,size,symPlatform,sidePair,section,stats}.
     */
    public function transform(array $context, mixed $content): mixed
    {
        return [
            'key' => (string) $context['key'],
            'size' => (string) $context['size'],
            'symPlatform' => (string) $context['symPlatform'],
            'sidePair' => (string) $context['sidePair'],
            'section' => (string) $context['section'],
            'stats' => array_map(fn ($data) => new Stat($data), $content),
        ];
    }
}
