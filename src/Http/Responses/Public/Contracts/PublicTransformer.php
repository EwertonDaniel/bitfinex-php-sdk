<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Http\Responses\Public\Contracts;

/**
 * Contract for public response transformers.
 */
interface PublicTransformer
{
    /**
     * @param array $context Extra params needed by the transformer (e.g., symbol, type).
     * @param mixed $content Raw decoded response content.
     * @return mixed Transformed payload.
     */
    /**
     * @param array $context Contextual parameters (e.g., symbol, type).
     * @param mixed $content Decoded response content.
     * @return mixed Transformed payload.
     */
    public function transform(array $context, mixed $content): mixed;
}
