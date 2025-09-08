<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Http\Responses\Public\Transformers;

use EwertonDaniel\Bitfinex\Entities\MarketAveragePriceResult;
use EwertonDaniel\Bitfinex\Http\Responses\Public\Contracts\PublicTransformer;

/**
 * Maps market average price result.
 */

class MarketAveragePriceTransformer implements PublicTransformer
{
    /**
     * @param array $context Contextual parameters.
     * @param mixed $content Decoded response content.
     * @return mixed Returns array{result: MarketAveragePriceResult}.
     */
    public function transform(array $context, mixed $content): mixed
    {
        return ['result' => new MarketAveragePriceResult($content)];
    }
}
