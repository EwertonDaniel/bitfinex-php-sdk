<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Http\Responses\Public\Transformers;

use EwertonDaniel\Bitfinex\Entities\FundingCurrency;
use EwertonDaniel\Bitfinex\Entities\TradingPair;
use EwertonDaniel\Bitfinex\Enums\BitfinexType;
use EwertonDaniel\Bitfinex\Http\Responses\Public\Contracts\PublicTransformer;

/**
 * Maps single ticker array to TradingPair/FundingCurrency.
 */

class TickerTransformer implements PublicTransformer
{
    /**
     * @param array $context Contextual parameters.
     * @param mixed $content Decoded response content.
     * @return mixed Returns array{ticker: TradingPair|FundingCurrency}.
     */
    public function transform(array $context, mixed $content): mixed
    {
        $symbol = (string) ($context['symbol'] ?? '');
        $type = $context['type'] ?? null;

        $ticker = match ($type) {
            BitfinexType::TRADING => new TradingPair($symbol, $content),
            BitfinexType::FUNDING => new FundingCurrency($symbol, $content),
        };

        return ['ticker' => $ticker];
    }
}
