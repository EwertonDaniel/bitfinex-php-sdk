<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Http\Responses\Public\Transformers;

use EwertonDaniel\Bitfinex\Entities\PlatformStatus;
use EwertonDaniel\Bitfinex\Http\Responses\Public\Contracts\PublicTransformer;

/**
 * Maps platform status [1]/[0] to entity.
 */

class PlatformStatusTransformer implements PublicTransformer
{
    /**
     * @param array $context Contextual parameters.
     * @param mixed $content Decoded response content.
     * @return mixed Returns PlatformStatus.
     */
    public function transform(array $context, mixed $content): mixed
    {
        return new PlatformStatus($content);
    }
}
