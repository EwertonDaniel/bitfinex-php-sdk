<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Http\Responses\Public\Transformers;

use EwertonDaniel\Bitfinex\Entities\BookFunding;
use EwertonDaniel\Bitfinex\Entities\BookTrading;
use EwertonDaniel\Bitfinex\Enums\BitfinexType;
use EwertonDaniel\Bitfinex\Http\Responses\Public\Contracts\PublicTransformer;

/**
 * Maps book rows to BookTrading/BookFunding.
 */

class BookTransformer implements PublicTransformer
{
    /**
     * @param array $context Contextual parameters.
     * @param mixed $content Decoded response content.
     * @return mixed Returns array{symbol: string, books: list<Book*>}.
     */
    public function transform(array $context, mixed $content): mixed
    {
        $symbol = (string) ($context['symbol'] ?? '');
        $type = $context['type'] ?? null;
        return [
            'symbol' => $symbol,
            'books' => array_map(
                fn ($book) => match ($type) {
                    BitfinexType::TRADING => new BookTrading($symbol, $book),
                    BitfinexType::FUNDING => new BookFunding($symbol, $book),
                },
                $content
            ),
        ];
    }
}
