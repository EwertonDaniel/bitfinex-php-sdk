<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Enums;

/**
 * Enum OrderOfferType
 *
 * Represents the types of orders or offers available on the Bitfinex platform.
 * This enum provides a way to handle and categorize order/offer types consistently.
 *
 * @author  Ewerton Daniel
 *
 * @contact contact@ewertondaniel.work
 */
enum OrderOfferType: string
{
    case EXCHANGE = 'exchange';

    case MARGIN = 'margin';

    case DERIV = 'deriv';

    case FUNDING = 'funding';

    final public function title(): string
    {
        return strtolower($this->value);
    }
}
