<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Http\Responses\Public\Transformers;

use EwertonDaniel\Bitfinex\Entities\CurrencyTrade;
use EwertonDaniel\Bitfinex\Entities\PairTrade;
use EwertonDaniel\Bitfinex\Enums\BitfinexType;
use EwertonDaniel\Bitfinex\Http\Responses\Public\Contracts\PublicTransformer;

/**
 * Maps trade rows to PairTrade/CurrencyTrade.
 */

class TradesTransformer implements PublicTransformer
{
    /**
     * @param array $context Contextual parameters.
     * @param mixed $content Decoded response content.
     * @return mixed Returns array{trades: list<PairTrade|CurrencyTrade>}.
     */
    public function transform(array $context, mixed $content): mixed
    {
        $symbol = (string) ($context['symbol'] ?? '');
        $type = $context['type'] ?? null;
        return [
            'trades' => array_map(
                fn ($trade) => match ($type) {
                    BitfinexType::TRADING => new PairTrade($symbol, $trade),
                    BitfinexType::FUNDING => new CurrencyTrade($symbol, $trade),
                },
                $content
            ),
        ];
    }
}
