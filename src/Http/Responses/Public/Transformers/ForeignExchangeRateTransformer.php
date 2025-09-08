<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Http\Responses\Public\Transformers;

use EwertonDaniel\Bitfinex\Entities\ForeignExchangeRate;
use EwertonDaniel\Bitfinex\Http\Responses\Public\Contracts\PublicTransformer;

/**
 * Maps FX rate payload to ForeignExchangeRate.
 */

class ForeignExchangeRateTransformer implements PublicTransformer
{
    /**
     * @param array $context Contextual parameters.
     * @param mixed $content Decoded response content.
     * @return mixed Returns ForeignExchangeRate.
     */
    public function transform(array $context, mixed $content): mixed
    {
        return new ForeignExchangeRate((string) $context['in'], (string) $context['out'], $content);
    }
}
