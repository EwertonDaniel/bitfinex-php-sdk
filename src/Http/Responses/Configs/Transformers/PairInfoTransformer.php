<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Http\Responses\Configs\Transformers;

use EwertonDaniel\Bitfinex\Entities\PairInfo;
use EwertonDaniel\Bitfinex\Http\Responses\Configs\Contracts\ConfigTransformer;

/**
 * Maps pair info rows to PairInfo.
 */

class PairInfoTransformer implements ConfigTransformer
{
    public function supports(string $key, mixed $value): bool
    {
        return str_starts_with($key, 'pub:info:pair') && is_array($value) && !empty($value);
    }

    /**
     * @param array $context Contextual parameters.
     * @param mixed $content Decoded response content.
     * @return mixed Returns list<PairInfo>.
     */
    public function transform(string $key, mixed $value): mixed
    {
        return array_map(fn ($row) => new PairInfo($row), $value);
    }
}
