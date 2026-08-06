<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Http\Responses\Configs\Transformers;

use EwertonDaniel\Bitfinex\Entities\TxStatus;
use EwertonDaniel\Bitfinex\Http\Responses\Configs\Contracts\ConfigTransformer;

/**
 * Maps tx status rows to TxStatus.
 */
class TxStatusTransformer implements ConfigTransformer
{
    public function supports(string $key, mixed $value): bool
    {
        return $key === 'pub:info:tx:status' && is_array($value) && ! empty($value);
    }

    /**
     * @param  string  $key  Config key the response came from (e.g. pub:map:currency:sym).
     * @param  mixed  $value  Decoded payload for that key.
     * @return mixed Returns list<TxStatus>.
     */
    public function transform(string $key, mixed $value): mixed
    {
        return array_map(fn ($row) => new TxStatus($row), $value);
    }
}
