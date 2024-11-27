<?php

namespace EwertonDaniel\Bitfinex\Services\Authenticated;

use EwertonDaniel\Bitfinex\Builders\RequestBuilder;
use EwertonDaniel\Bitfinex\Builders\UrlBuilder;
use EwertonDaniel\Bitfinex\Enums\BitfinexAction;
use EwertonDaniel\Bitfinex\Enums\BitfinexType;
use EwertonDaniel\Bitfinex\Enums\BitfinexWalletType;
use EwertonDaniel\Bitfinex\Enums\OrderOfferType;
use EwertonDaniel\Bitfinex\Exceptions\BitfinexException;
use EwertonDaniel\Bitfinex\Exceptions\BitfinexPathNotFoundException;
use EwertonDaniel\Bitfinex\Http\Requests\BitfinexRequest;
use EwertonDaniel\Bitfinex\Http\Responses\AuthenticatedBitfinexResponse;
use EwertonDaniel\Bitfinex\Http\Responses\BitfinexResponse;
use EwertonDaniel\Bitfinex\ValueObjects\BitfinexCredentials;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Class BitfinexAuthenticatedAccountAction
 *
 * Provides authenticated account actions for the Bitfinex API.
 * This class handles various endpoints under the `private.account_actions` path,
 * including user information, login history, key permissions, and wallet movements.
 * Additionally, methods for managing alerts, balances, and user settings are provided.
 *
 * @author  Ewerton Daniel
 *
 * @contact contact@ewertondaniel.work
 */
class BitfinexAuthenticatedAccountAction
{
    /**
     * Base path for all account action API endpoints.
     */
    private readonly string $basePath;

    /**
     * Constructor for initializing dependencies required for API interactions.
     *
     * @param  UrlBuilder  $url  The URL builder for constructing API paths.
     * @param  BitfinexCredentials  $credentials  The API credentials for authentication.
     * @param  RequestBuilder  $request  The request builder for configuring HTTP requests.
     * @param  Client  $client  The HTTP client for sending requests.
     */
    public function __construct(
        private readonly UrlBuilder $url,
        private readonly BitfinexCredentials $credentials,
        private readonly RequestBuilder $request,
        private readonly Client $client
    ) {
        $this->basePath = 'private.account_actions';
    }

    /**
     * Retrieves user information associated with the API key.
     *
     * @return AuthenticatedBitfinexResponse The response containing user information.
     *
     * @throws GuzzleException If an HTTP request error occurs.
     * @throws BitfinexPathNotFoundException If the API path cannot be resolved.
     *
     * @link https://docs.bitfinex.com/reference/rest-auth-info-user
     */
    final public function userInfo(): AuthenticatedBitfinexResponse
    {
        try {
            $request = new BitfinexRequest($this->request, $this->credentials, $this->client);
            $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.user_info")->getPath());

            return $response->userInfo();
        } catch (GuzzleException $e) {
            throw new BitfinexException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Retrieves a summary of the account's status and balances.
     *
     * @return AuthenticatedBitfinexResponse The response containing the account summary.
     *
     * @throws GuzzleException If an HTTP request error occurs.
     * @throws BitfinexPathNotFoundException If the API path cannot be resolved.
     *
     * @link https://docs.bitfinex.com/reference/rest-auth-summary
     */
    final public function summary(): AuthenticatedBitfinexResponse
    {
        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);
        $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.summary")->getPath());

        return $response->summary();
    }

    /**
     * Retrieves the login history for the account.
     *
     * @return AuthenticatedBitfinexResponse The response containing login history.
     *
     * @throws GuzzleException If an HTTP request error occurs.
     * @throws BitfinexPathNotFoundException If the API path cannot be resolved.
     *
     * @link https://docs.bitfinex.com/reference/rest-auth-logins-hist
     */
    final public function loginHistory(): AuthenticatedBitfinexResponse
    {
        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);
        $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.login_history")->getPath());

        return $response->loginHistory();
    }

    /**
     * Retrieves the key permissions for the API key.
     *
     * @return AuthenticatedBitfinexResponse The response containing key permissions.
     *
     * @throws GuzzleException If an HTTP request error occurs.
     * @throws BitfinexPathNotFoundException If the API path cannot be resolved.
     *
     * @link https://docs.bitfinex.com/reference/key-permissions
     */
    final public function keyPermissions(): AuthenticatedBitfinexResponse
    {
        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);
        $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.key_permissions")->getPath());

        return $response->keyPermissions();
    }

    /**
     * Retrieves the changelog for account actions.
     *
     * This method fetches the history of changes related to the account, providing
     * detailed information about updates or modifications performed on the account.
     *
     * @return AuthenticatedBitfinexResponse The response containing the changelog data.
     *
     * @throws GuzzleException If an HTTP request error occurs.
     * @throws BitfinexPathNotFoundException If the API path cannot be resolved.
     *
     * @link https://docs.bitfinex.com/reference/rest-auth-audit-hist
     */
    final public function changelog(): AuthenticatedBitfinexResponse
    {
        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);
        $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.changelog")->getPath());

        return $response->changelog();
    }

    /**
     * @throws GuzzleException
     * @throws BitfinexPathNotFoundException
     *
     * @link https://docs.bitfinex.com/reference/rest-auth-transfer
     *
     * @todo Implement method for transferring between wallets.
     */
    final public function transferBetweenWallets(): AuthenticatedBitfinexResponse
    {
        /** @todo */
    }

    /**
     * Fetches a deposit address for the specified wallet and method.
     *
     * @param  BitfinexWalletType  $walletType  Type of the wallet (e.g., exchange, margin, funding).
     * @param  string  $method  Deposit method (e.g., crypto, bank wire).
     * @param  int  $opRenew  Optional flag to renew the deposit address (default: 0).
     * @return AuthenticatedBitfinexResponse Response containing the deposit address details.
     *
     * @throws GuzzleException
     * @throws BitfinexPathNotFoundException
     *
     * @link https://docs.bitfinex.com/reference/rest-auth-deposit-address
     */
    final public function depositAddress(BitfinexWalletType $walletType, string $method, int $opRenew = 0): AuthenticatedBitfinexResponse
    {
        $this->request->setBody([
            'wallet' => $walletType->value,
            'method' => $method,
            'op_renew' => $opRenew,
        ]);

        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);

        $apiPath = $this->url->setPath("$this->basePath.deposit_address")->getPath();

        $response = $request->execute(apiPath: $apiPath);

        return $response->depositAddress($walletType);
    }

    /**
     * Retrieves a paginated list of all deposit addresses for the given method.
     *
     * @param  string  $method  Deposit method (e.g., crypto, bank wire).
     * @param  int  $page  Page number for paginated results (default: 1).
     * @param  int  $pageSize  Number of results per page (default: 20).
     * @return AuthenticatedBitfinexResponse Response containing the list of deposit addresses.
     *
     * @throws BitfinexPathNotFoundException
     * @throws GuzzleException
     *
     * @link https://docs.bitfinex.com/reference/deposit-address-all
     */
    final public function depositAddressList(string $method, int $page = 1, int $pageSize = 20): AuthenticatedBitfinexResponse
    {
        $this->request->setBody([
            'method' => $method,
            'page' => $page,
            'pageSize' => $pageSize,
        ]);

        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);

        $apiPath = $this->url->setPath("$this->basePath.deposit_addresses")->getPath();

        $response = $request->execute(apiPath: $apiPath);

        return $response->depositAddressList($method);
    }

    /**
     * @throws GuzzleException
     * @throws BitfinexPathNotFoundException
     *
     * @link https://docs.bitfinex.com/reference/rest-auth-deposit-invoice
     *
     * @todo Implement method for generating invoices.
     */
    final public function generateInvoice(): AuthenticatedBitfinexResponse
    {
        /** @todo */
    }

    /**
     * @throws GuzzleException
     * @throws BitfinexPathNotFoundException
     *
     * @link https://docs.bitfinex.com/reference/rest-auth-withdraw
     *
     * @todo Implement method for processing withdrawals.
     */
    final public function withdrawal(): AuthenticatedBitfinexResponse
    {
        /** @todo */
    }

    /**
     * Retrieves wallet movements for a given currency.
     *
     * @param  string  $currency  The currency to filter movements.
     * @return AuthenticatedBitfinexResponse The response containing wallet movements.
     *
     * @throws GuzzleException If an HTTP request error occurs.
     * @throws BitfinexPathNotFoundException If the API path cannot be resolved.
     *
     * @link https://docs.bitfinex.com/reference/rest-auth-movements
     */
    final public function movements(string $currency): AuthenticatedBitfinexResponse
    {
        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);

        $apiPath = $this->url->setPath("$this->basePath.movements", ['currency' => $currency])->getPath();

        $response = $request->execute(apiPath: $apiPath);

        return $response->movements();
    }

    /**
     * Retrieves detailed information about a specific wallet movement.
     *
     * @param  string|int  $id  The unique identifier of the movement.
     * @return BitfinexResponse The response containing detailed movement information.
     *
     * @throws GuzzleException If an HTTP request error occurs.
     * @throws BitfinexPathNotFoundException If the API path cannot be resolved.
     *
     * @link https://docs.bitfinex.com/reference/movement-info
     */
    final public function movementInfo(string|int $id): BitfinexResponse
    {
        $request = (new BitfinexRequest($this->request->setBody(['id' => $id]), $this->credentials, $this->client));

        $apiPath = $this->url->setPath("$this->basePath.movement_info")->getPath();

        $response = $request->execute(apiPath: $apiPath);

        return $response->movementInfo();
    }

    /**
     * Retrieves a list of active alerts for the account.
     *
     * @param  string  $type  The type of alerts to retrieve.
     * @return AuthenticatedBitfinexResponse The response containing the list of alerts.
     *
     * @throws GuzzleException If an HTTP request error occurs.
     * @throws BitfinexPathNotFoundException If the API path cannot be resolved.
     *
     * @link https://docs.bitfinex.com/reference/rest-auth-alerts
     */
    final public function alertList(string $type): AuthenticatedBitfinexResponse
    {
        $request = new BitfinexRequest($this->request->addBody('type', $type), $this->credentials, $this->client);
        $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.alert_list")->getPath());

        return $response->alertList();
    }

    /**
     * Sets a new alert for a given pair and price.
     *
     * @param  string  $pair  The trading pair.
     * @param  int  $price  The target price for the alert.
     * @param  string  $type  The type of alert (default: 'price').
     * @param  int  $count  The count of alerts (default: 100).
     * @return AuthenticatedBitfinexResponse The response confirming the alert creation.
     *
     * @throws GuzzleException If an HTTP request error occurs.
     * @throws BitfinexPathNotFoundException If the API path cannot be resolved.
     *
     * @link https://docs.bitfinex.com/reference/rest-auth-alert-set
     */
    final public function alertSet(string $pair, int $price, string $type = 'price', int $count = 100): AuthenticatedBitfinexResponse
    {
        $this->request->setBody(['type' => $type, 'symbol' => BitfinexType::TRADING->symbol($pair), 'price' => $price, 'count' => $count]);
        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);
        $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.alert_set")->getPath());

        return $response->alertSet();
    }

    /**
     * Deletes an active alert for a given symbol and price.
     *
     * This method allows the deletion of a price-based alert associated with a specific
     * trading pair pair. The alert is identified by the ṕair and target price.
     *
     * @param  string  $pair  The trading pair (e.g., BTCUSD).
     * @param  int  $price  The target price of the alert to be deleted.
     * @return AuthenticatedBitfinexResponse The response confirming the alert deletion.
     *
     * @throws GuzzleException If an HTTP request error occurs.
     * @throws BitfinexPathNotFoundException If the API path cannot be resolved.
     *
     * @link https://docs.bitfinex.com/reference/rest-auth-alert-del
     */
    final public function alertDelete(string $pair, int $price): AuthenticatedBitfinexResponse
    {
        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);
        $response = $request->execute(
            apiPath: $this->url->setPath("$this->basePath.alert_delete", ['pair' => BitfinexType::TRADING->symbol($pair), 'price' => $price]
            )->getPath()
        );

        return $response->alertDelete();
    }

    /**
     * Retrieves the available balance for placing orders or offers.
     *
     * This method calculates and returns the available balance for a specific
     * trading pair or funding currency. It considers the direction (buy/sell), type
     * of action, and other parameters like rate and leverage.
     *
     * @param  BitfinexType  $type  The type of market (e.g., trading or funding).
     * @param  string  $pairOrCurrency  The trading pair or funding currency (e.g., BTCUSD or USD).
     * @param  BitfinexAction  $action  The action type (e.g., buy/sell).
     * @param  OrderOfferType  $orderOfferType  The type of order or offer.
     * @param  string|null  $rate  [Optional] The rate for funding or trading.
     * @param  string|null  $lev  [Optional] The leverage to be applied.
     * @return AuthenticatedBitfinexResponse The response containing the available balance data.
     *
     * @throws GuzzleException If an HTTP request error occurs.
     * @throws BitfinexPathNotFoundException If the API path cannot be resolved.
     *
     * @link https://docs.bitfinex.com/reference/rest-auth-calc-order-avail
     */
    final public function balanceAvailableForOrdersOffers(
        BitfinexType $type,
        string $pairOrCurrency,
        BitfinexAction $action,
        OrderOfferType $orderOfferType,
        ?string $rate = null,
        ?string $lev = null
    ): AuthenticatedBitfinexResponse {
        $params = [
            'dir' => $action->dir(),
            'symbol' => $type->symbol($pairOrCurrency),
            'type' => $orderOfferType->name,
            'rate' => $rate,
            'lev' => $lev,
        ];

        array_walk($params, fn ($value, $key) => $this->request->addBody($key, $value, true));
        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);
        $response = $request->execute($this->url->setPath("$this->basePath.balance_available_for_orders_offers")->getPath());

        return $response->balanceAvailableForOrdersOffers();
    }

    /**
     * @throws GuzzleException
     * @throws BitfinexPathNotFoundException
     *
     * @link https://docs.bitfinex.com/reference/rest-auth-settings-set
     *
     * @todo Implement method for writing user settings.
     */
    final public function userSettingsWrite(): AuthenticatedBitfinexResponse
    {
        /** @todo */
    }

    /**
     * @throws GuzzleException
     * @throws BitfinexPathNotFoundException
     *
     * @link https://api.bitfinex.com/v2/auth/r/settings
     *
     * @todo Implement method for reading user settings.
     */
    final public function userSettingsRead(): AuthenticatedBitfinexResponse
    {
        /** @todo */
    }

    /**
     * @throws GuzzleException
     * @throws BitfinexPathNotFoundException
     *
     * @link https://docs.bitfinex.com/reference/rest-auth-settings-del
     *
     * @todo Implement method for deleting user settings.
     */
    final public function userSettingsDelete(): AuthenticatedBitfinexResponse
    {
        /** @todo */
    }
}
