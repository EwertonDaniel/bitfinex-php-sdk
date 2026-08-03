<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Http\Responses\Public\Transformers;

use EwertonDaniel\Bitfinex\Entities\DerivativeStatus;
use EwertonDaniel\Bitfinex\Entities\DerivativeStatusHistory;
use EwertonDaniel\Bitfinex\Helpers\GetThis;
use EwertonDaniel\Bitfinex\Http\Responses\Public\Contracts\PublicTransformer;

/**
 * Maps derivatives status rows to entities.
 *
 * History rows carry the symbol in the path rather than in the row, so they have
 * no leading KEY field and every index sits one position lower than in the
 * snapshot. The two layouts therefore need distinct entities.
 */
class DerivativesStatusTransformer implements PublicTransformer
{
    /**
     * @param  array  $context  Contextual parameters (keys, history).
     * @param  mixed  $content  Decoded response content.
     * @return mixed Returns array{keys, items: list<DerivativeStatus|DerivativeStatusHistory>}.
     */
    public function transform(array $context, mixed $content): mixed
    {
        $isHistory = (bool) ($context['history'] ?? false);

        $map = fn ($row) => GetThis::ifTrueOrFallback(
            boolean: $isHistory,
            callback: fn () => new DerivativeStatusHistory($row),
            fallback: fn () => new DerivativeStatus($row)
        );

        return [
            'keys' => $context['keys'] ?? [],
            'items' => array_map($map, $content),
        ];
    }
}
