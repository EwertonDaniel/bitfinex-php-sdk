<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Http\Responses\Configs\Contracts;

/**
 * Contract for configs transformers.
 */
interface ConfigTransformer
{
    public function supports(string $key, mixed $value): bool;

    /**
     * @param  string  $key  Config key the response came from (e.g. pub:map:currency:sym).
     * @param  mixed  $value  Decoded payload for that key.
     * @return mixed Transformed payload.
     */
    public function transform(string $key, mixed $value): mixed;
}
