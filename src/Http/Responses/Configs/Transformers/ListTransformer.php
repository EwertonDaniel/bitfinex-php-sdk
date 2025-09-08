<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Http\Responses\Configs\Transformers;

use EwertonDaniel\Bitfinex\Http\Responses\Configs\Contracts\ConfigTransformer;

/**
 * Normalizes list mode to list of strings.
 */

class ListTransformer implements ConfigTransformer
{
    public function supports(string $key, mixed $value): bool
    {
        return str_starts_with($key, 'pub:list:') && is_array($value);
    }

    /**
     * @param array $context Contextual parameters.
     * @param mixed $content Decoded response content.
     * @return mixed Returns list<string>.
     */
    public function transform(string $key, mixed $value): mixed
    {
        return array_values(array_map(fn ($v) => is_array($v) ? (string) ($v[0] ?? '') : (string) $v, $value));
    }
}
