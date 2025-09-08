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
     * @param array $context Contextual parameters (e.g., symbol, type).
     * @param mixed $content Decoded response content.
     * @return mixed Transformed payload.
     */
    public function transform(string $key, mixed $value): mixed;
}
