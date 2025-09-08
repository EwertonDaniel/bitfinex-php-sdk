<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Http\Responses\Public\Transformers;

use EwertonDaniel\Bitfinex\Entities\FundingCurrency;
use EwertonDaniel\Bitfinex\Entities\TradingPair;
use EwertonDaniel\Bitfinex\Enums\BitfinexType;
use EwertonDaniel\Bitfinex\Http\Responses\Public\Contracts\PublicTransformer;

/**
 * Maps list of tickers to entities by type.
 */

class TickersTransformer implements PublicTransformer
{
    /**
     * @param array $context Contextual parameters.
     * @param mixed $content Decoded response content.
     * @return mixed Returns array{tickers: list<TradingPair|FundingCurrency>}.
     */
    public function transform(array $context, mixed $content): mixed
    {
        $type = $context['type'] ?? null;
        return [
            'tickers' => array_map(
                fn ($ticker) => match ($type) {
                    BitfinexType::TRADING => new TradingPair($ticker[0], array_slice($ticker, 1)),
                    BitfinexType::FUNDING => new FundingCurrency($ticker[0], array_slice($ticker, 1)),
                },
                $content
            ),
        ];
    }
}
