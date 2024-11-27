<?php

namespace EwertonDaniel\Bitfinex\Enums;

/**
 * Enum BitfinexWalletType
 *
 * Represents the different wallet types available on the Bitfinex platform.
 * These types are used to specify the category of wallet for trading, margin,
 * or funding operations.
 *
 * Available types:
 * - `exchange`: Represents the exchange wallet, used for spot trading.
 * - `margin`: Represents the margin wallet, used for leveraged trading.
 * - `funding`: Represents the funding wallet, used for providing or receiving funding.
 *
 * @author  Ewerton Daniel
 *
 * @contact contact@ewertondaniel.work
 */
enum BitfinexWalletType: string
{
    /** Represents the exchange wallet (used for spot trading). */
    case EXCHANGE = 'exchange';

    /** Represents the margin wallet (used for leveraged trading). */
    case MARGIN = 'margin';

    /** Represents the funding wallet (used for providing or receiving funding). */
    case FUNDING = 'funding';
}
