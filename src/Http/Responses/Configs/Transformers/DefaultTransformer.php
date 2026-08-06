<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Http\Responses\Configs\Transformers;

use EwertonDaniel\Bitfinex\Http\Responses\Configs\Contracts\ConfigTransformer;

/**
 * Fallback transformer (identity).
 */
class DefaultTransformer implements ConfigTransformer
{
    public function supports(string $key, mixed $value): bool
    {
        return true; // Fallback
    }

    /**
     * @param  string  $key  Config key the response came from (e.g. pub:map:currency:sym).
     * @param  mixed  $value  Decoded payload for that key.
     * @return mixed Returns original value.
     */
    public function transform(string $key, mixed $value): mixed
    {
        return $value;
    }
}
