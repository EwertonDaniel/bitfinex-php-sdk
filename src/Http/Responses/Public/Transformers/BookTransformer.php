<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Http\Responses\Public\Transformers;

use EwertonDaniel\Bitfinex\Entities\BookFunding;
use EwertonDaniel\Bitfinex\Entities\BookFundingRaw;
use EwertonDaniel\Bitfinex\Entities\BookTrading;
use EwertonDaniel\Bitfinex\Entities\BookTradingRaw;
use EwertonDaniel\Bitfinex\Enums\BitfinexType;
use EwertonDaniel\Bitfinex\Enums\BookPrecision;
use EwertonDaniel\Bitfinex\Exceptions\BitfinexException;
use EwertonDaniel\Bitfinex\Http\Responses\Public\Contracts\PublicTransformer;

/**
 * Maps book rows to BookTrading/BookFunding, or to their raw counterparts.
 *
 * Precision `R0` returns a different layout from `P0`-`P4`: rows are individual
 * orders carrying an id, not aggregated price levels carrying a count. Mapping
 * a raw row with the aggregated entity silently reads the order id as the price.
 */
class BookTransformer implements PublicTransformer
{
    /**
     * @param  array  $context  Contextual parameters (symbol, type, precision).
     * @param  mixed  $content  Decoded response content.
     * @return mixed Returns array{symbol: string, books: list<Book*>}.
     */
    public function transform(array $context, mixed $content): mixed
    {
        $symbol = (string) ($context['symbol'] ?? '');
        $type = $context['type'] ?? null;
        $isRaw = ($context['precision'] ?? null) === BookPrecision::R0;

        return [
            'symbol' => $symbol,
            'books' => array_map(
                fn ($book) => match (true) {
                    $type === BitfinexType::TRADING && $isRaw => new BookTradingRaw($symbol, $book),
                    $type === BitfinexType::TRADING => new BookTrading($symbol, $book),
                    $type === BitfinexType::FUNDING && $isRaw => new BookFundingRaw($symbol, $book),
                    $type === BitfinexType::FUNDING => new BookFunding($symbol, $book),
                    default => throw new BitfinexException('Unknown Bitfinex type for a book: '.get_debug_type($type).'.'),
                },
                $content
            ),
        ];
    }
}
