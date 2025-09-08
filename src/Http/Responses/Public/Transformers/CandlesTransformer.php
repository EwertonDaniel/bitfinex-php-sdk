<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Http\Responses\Public\Transformers;

use EwertonDaniel\Bitfinex\Entities\Candle;
use EwertonDaniel\Bitfinex\Http\Responses\Public\Contracts\PublicTransformer;

/**
 * Maps candles payload to Candle list with metadata.
 */

class CandlesTransformer implements PublicTransformer
{
    /**
     * @param array $context Contextual parameters.
     * @param mixed $content Decoded response content.
     * @return mixed Returns array{symbol,timeframe,section,candles}.
     */
    public function transform(array $context, mixed $content): mixed
    {
        $candles = [];
        if (is_array($content)) {
            if (isset($content[0]) && is_array($content[0])) {
                $candles = array_map(fn ($row) => new Candle($row), $content);
            } elseif (!empty($content)) {
                $candles = [new Candle($content)];
            }
        }
        return [
            'symbol' => (string) $context['symbol'],
            'timeframe' => (string) $context['timeframe'],
            'section' => (string) $context['section'],
            'candles' => $candles,
        ];
    }
}
