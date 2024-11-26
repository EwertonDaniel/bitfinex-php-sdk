<?php

namespace EwertonDaniel\Bitfinex\Entities;

use EwertonDaniel\Bitfinex\Enums\BitfinexWalletType;
use EwertonDaniel\Bitfinex\Helpers\GetThis;
use Illuminate\Support\Carbon;

/**
 * Class DepositAddress
 *
 * Represents a single deposit address notification retrieved from the Bitfinex API.
 * Provides structured data for timestamp, type, message ID, deposit address details,
 * status, and additional notification information.
 *
 * This entity encapsulates the deposit address notification details, including the
 * deposit method, currency code, address, and pool address (if applicable).
 *
 * @author  Ewerton Daniel
 * @contact contact@ewertondaniel.work
 */
class DepositAddress
{
    public readonly BitfinexWalletType $walletType;

    /** @note Seconds epoch timestamp of the notification */
    public readonly ?Carbon $createdAt;

    /** @note Type of the notification (e.g., "on-req") */
    public readonly ?string $type;

    /** @note Unique ID of the notification */
    public readonly ?int $messageId;

    /** @note Method used for the deposit */
    public readonly string $method;

    /** @note Currency code for the deposit address */
    public readonly ?string $currencyCode;

    /** @note Deposit address (or Tag/Memo/Payment_ID for specific currencies) */
    public readonly string $address;

    /** @note Pool address for deposits requiring Tag/Memo/Payment_ID */
    public readonly ?string $poolAddress;

    /** @note Status of the notification (e.g., SUCCESS, ERROR) */
    public readonly ?string $status;

    /** @note Additional description or information related to the notification */
    public readonly ?string $text;

    /**
     * Constructs a DepositAddress entity from the provided data.
     *
     * @param  array  $data  Array containing deposit address notification details from the Bitfinex API response.
     */
    public function __construct(string|BitfinexWalletType $walletType, array $data)
    {
        $this->walletType = GetThis::ifTrueOrFallback(is_string($walletType), fn () => BitfinexWalletType::from($walletType), $walletType);
        $this->createdAt = GetThis::ifTrueOrFallback(isset($data[0]), fn () => Carbon::createFromTimestamp($data[0]));
        $this->type = GetThis::ifTrueOrFallback(isset($data[1]), fn () => $data[1]);
        $this->messageId = GetThis::ifTrueOrFallback(isset($data[2]), fn () => $data[2]);
        $this->method = GetThis::ifTrueOrFallback(isset($data[4][1]), fn () => $data[4][1]);
        $this->currencyCode = GetThis::ifTrueOrFallback(isset($data[4][2]), fn () => $data[4][2]);
        $this->address = GetThis::ifTrueOrFallback(isset($data[4][4]), fn () => $data[4][4]);
        $this->poolAddress = GetThis::ifTrueOrFallback(isset($data[4][5]), fn () => $data[4][5]);
        $this->status = GetThis::ifTrueOrFallback(isset($data[6]), fn () => $data[6]);
        $this->text = GetThis::ifTrueOrFallback(isset($data[7]), fn () => $data[7]);
    }
}
