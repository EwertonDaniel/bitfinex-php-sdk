<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Http\Responses\Configs\Transformers;

use EwertonDaniel\Bitfinex\Helpers\GetThis;
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
     * @param  string  $key  Configuration key being normalized.
     * @param  mixed  $value  Decoded value for that key.
     * @return mixed Returns list<string>.
     */
    public function transform(string $key, mixed $value): mixed
    {
        $normalize = fn ($item) => GetThis::ifTrueOrFallback(
            boolean: is_array($item),
            callback: fn () => (string) ($item[0] ?? ''),
            fallback: fn () => (string) $item
        );

        return array_values(array_map($normalize, $value));
    }
}
