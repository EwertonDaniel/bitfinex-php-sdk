<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Http\Responses\Configs\Transformers;

use EwertonDaniel\Bitfinex\Http\Responses\Configs\Contracts\ConfigTransformer;

/**
 * Maps [[k,v],...] to associative dict.
 */

class MapTransformer implements ConfigTransformer
{
    public function supports(string $key, mixed $value): bool
    {
        return str_starts_with($key, 'pub:map:') && is_array($value);
    }

    /**
     * @param array $context Contextual parameters.
     * @param mixed $content Decoded response content.
     * @return mixed Returns dict<string,mixed>.
     */
    public function transform(string $key, mixed $value): mixed
    {
        $map = [];
        foreach ($value as $pair) {
            if (is_array($pair) && count($pair) >= 2) {
                $map[(string) $pair[0]] = $pair[1];
            }
        }
        return $map;
    }
}
