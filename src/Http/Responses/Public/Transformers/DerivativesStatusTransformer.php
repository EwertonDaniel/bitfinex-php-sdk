<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Http\Responses\Public\Transformers;

use EwertonDaniel\Bitfinex\Entities\DerivativeStatus;
use EwertonDaniel\Bitfinex\Http\Responses\Public\Contracts\PublicTransformer;

/**
 * Maps derivatives status list to entities.
 */

class DerivativesStatusTransformer implements PublicTransformer
{
    /**
     * @param array $context Contextual parameters.
     * @param mixed $content Decoded response content.
     * @return mixed Returns array{keys, items: list<DerivativeStatus>}.
     */
    public function transform(array $context, mixed $content): mixed
    {
        return [
            'keys' => $context['keys'] ?? [],
            'items' => array_map(fn ($row) => new DerivativeStatus($row), $content),
        ];
    }
}
