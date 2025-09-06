<?php

namespace EwertonDaniel\Bitfinex\Services\Authenticated;

use EwertonDaniel\Bitfinex\Builders\RequestBuilder;
use EwertonDaniel\Bitfinex\Builders\UrlBuilder;
use EwertonDaniel\Bitfinex\Enums\BitfinexAction;
use EwertonDaniel\Bitfinex\Enums\BitfinexType;
use EwertonDaniel\Bitfinex\Enums\OrderType;
use EwertonDaniel\Bitfinex\Exceptions\BitfinexPathNotFoundException;
use EwertonDaniel\Bitfinex\Helpers\GetThis;
use EwertonDaniel\Bitfinex\Http\Requests\BitfinexRequest;
use EwertonDaniel\Bitfinex\Http\Responses\AuthenticatedBitfinexResponse;
use EwertonDaniel\Bitfinex\Http\Responses\BitfinexResponse;
use EwertonDaniel\Bitfinex\ValueObjects\BitfinexCredentials;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Class BitfinexAuthenticatedOrder
 *
 * Handles authenticated order operations for the Bitfinex API, including retrieving and submitting orders.
 * Provides flexibility in specifying order parameters and supports dynamic paths for API interactions.
 *
 * @author Ewerton Daniel
 *
 * @contact contact@ewertondaniel.work
 */
class BitfinexAuthenticatedOrder
{
    /** @var string Base path for order endpoints */
    private readonly string $basePath;

    /**
     * Constructor initializes the Bitfinex order service with necessary dependencies.
     *
     * @param  UrlBuilder  $url  URL builder for constructing API paths.
     * @param  BitfinexCredentials  $credentials  API credentials for authentication.
     * @param  RequestBuilder  $request  Builder for HTTP requests.
     * @param  Client  $client  HTTP client for executing requests.
     */
    public function __construct(
        private readonly UrlBuilder $url,
        private readonly BitfinexCredentials $credentials,
        private readonly RequestBuilder $request,
        private readonly Client $client
    ) {
        $this->basePath = 'private.orders';
    }

    /**
     * Retrieve existing orders based on filters such as symbol, ID, group ID, and client order ID.
     *
     * @throws GuzzleException
     * @throws BitfinexPathNotFoundException
     *
     * @link https://docs.bitfinex.com/reference/rest-auth-retrieve-orders
     *       https://docs.bitfinex.com/reference/rest-auth-retrieve-orders-by-symbol
     */
    final public function retrieve(
        ?string $pair = null,
        ?array $id = null,
        ?int $gid = null,
        ?string $cid = null,
        ?string $cidDate = null
    ): BitfinexResponse {
        $params = ['id' => $id, 'gid' => $gid, 'cid' => $cid, 'cid_date' => $cidDate];
        array_walk($params, fn ($value, $key) => $this->request->addBody($key, $value, true));

        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);

        $response = GetThis::ifTrueOrFallback(
            boolean: $pair,
            callback: fn () => $request->execute(
                $this->url->setPath("$this->basePath.retrieve_orders_by_symbol", ['symbol' => BitfinexType::TRADING->symbol($pair)])->getPath()
            ),
            fallback: fn () => $request->execute($this->url->setPath("$this->basePath.retrieve_orders")->getPath())
        );

        return $response->retrieveOrders();
    }

    /**
     * Submit a new order to the Bitfinex API with flexible parameters.
     *
     * @param  OrderType  $type  Type of the order (e.g., LIMIT, MARKET).
     * @param  BitfinexAction  $action  Action for the order (e.g., BUY, SELL).
     * @param  float  $amount  Amount for the order.
     * @param  int  $price  Price for the order.
     * @param  int|null  $leverage  Leverage for margin trading.
     * @param  string|null  $priceTrailing  Trailing price for trailing stop orders.
     * @param  string|null  $priceAuxLimit  Auxiliary limit price for stop-limit orders.
     * @param  string|null  $priceOcoStop  Price for one-cancels-other (OCO) stop orders.
     * @param  int|null  $gid  Group ID for the order.
     * @param  int|null  $cid  Client ID for the order.
     * @param  int|null  $flags  Flags indicating additional functionalities for the order.
     * @param  string|null  $tif  Time in force for the order.
     * @param  array|null  $meta  Metadata for the order.
     * @return AuthenticatedBitfinexResponse The parsed response containing order details.
     *
     * @throws BitfinexPathNotFoundException
     * @throws GuzzleException
     *
     * @link https://docs.bitfinex.com/reference/rest-auth-submit-order
     */
    final public function submit(
        OrderType $type,
        BitfinexAction $action,
        string $pair,
        float $amount,
        int $price,
        ?int $leverage = null,
        ?string $priceTrailing = null,
        ?string $priceAuxLimit = null,
        ?string $priceOcoStop = null,
        ?int $gid = null,
        ?int $cid = null,
        ?int $flags = null,
        ?string $tif = null,
        ?array $meta = null
    ): AuthenticatedBitfinexResponse {
        $this->request->setBody([
            'type' => $type->value,
            'symbol' => BitfinexType::TRADING->symbol($pair),
            'amount' => (string) GetThis::ifTrueOrFallback($action->isSell(), fn () => $amount * -1, $amount),
            'price' => (string) $price,
        ]);

        $optionalParams = [
            'lev' => $leverage,
            'price_trailing' => $priceTrailing,
            'price_aux_limit' => $priceAuxLimit,
            'price_oco_stop' => $priceOcoStop,
            'gid' => $gid,
            'cid' => $cid,
            'flags' => $flags,
            'tif' => $tif,
            'meta' => $meta,
        ];

        array_walk($optionalParams, fn ($value, $key) => $this->request->addBody($key, $value, true));

        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);

        $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.submit_order")->getPath());

        return $response->submitOrder();
    }

    /**
     * Update an existing order.
     *
     * Accepts flexible identifiers: id, gid or cid+cid_date; optional price/amount and price helpers.
     *
     * @throws BitfinexPathNotFoundException
     * @throws GuzzleException
     *
     * @link https://docs.bitfinex.com/reference/rest-auth-update-order
     */
    final public function update(
        ?int $id = null,
        ?int $gid = null,
        ?int $cid = null,
        ?string $cidDate = null,
        ?float $amount = null,
        ?int $price = null,
        ?string $priceTrailing = null,
        ?string $priceAuxLimit = null,
        ?string $priceOcoStop = null,
        ?int $flags = null,
        ?string $tif = null
    ): AuthenticatedBitfinexResponse {
        $params = compact('id', 'gid', 'cid', 'cidDate', 'amount', 'price', 'priceTrailing', 'priceAuxLimit', 'priceOcoStop', 'flags', 'tif');
        array_walk($params, fn ($value, $key) => $this->request->addBody($key === 'cidDate' ? 'cid_date' : $key, $value, true));

        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);
        $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.order_update")->getPath());

        return $response->submitOrder();
    }

    /**
     * Cancel an order by id, gid or cid+cid_date.
     *
     * @throws BitfinexPathNotFoundException
     * @throws GuzzleException
     *
     * @link https://docs.bitfinex.com/reference/rest-auth-cancel-order
     */
    final public function cancel(
        ?int $id = null,
        ?int $gid = null,
        ?int $cid = null,
        ?string $cidDate = null
    ): AuthenticatedBitfinexResponse {
        $params = compact('id', 'gid', 'cid', 'cidDate');
        array_walk($params, fn ($value, $key) => $this->request->addBody($key === 'cidDate' ? 'cid_date' : $key, $value, true));

        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);
        $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.cancel_order")->getPath());

        return $response->submitOrder();
    }

    /**
     * Multiple order operations in a single request.
     * The $ops array should follow Bitfinex API structure.
     *
     * @throws BitfinexPathNotFoundException
     * @throws GuzzleException
     *
     * @link https://docs.bitfinex.com/reference/rest-auth-order-multi
     */
    final public function multi(array $ops): AuthenticatedBitfinexResponse
    {
        $this->request->setBody(['ops' => $ops]);
        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);
        $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.order_multi_op")->getPath());

        return $response->orderMultiOp();
    }

    /**
     * Cancel multiple orders by ids.
     *
     * @param  array<int>  $ids
     *
     * @throws BitfinexPathNotFoundException
     * @throws GuzzleException
     *
     * @link https://docs.bitfinex.com/reference/rest-auth-cancel-multiple-orders
     */
    final public function cancelMultiple(array $ids): AuthenticatedBitfinexResponse
    {
        $this->request->setBody(['id' => $ids]);
        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);
        $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.cancel_order_multi")->getPath());

        return $response->orderCancelMulti();
    }

    /**
     * Orders history.
     *
     * @throws BitfinexPathNotFoundException
     * @throws GuzzleException
     *
     * @link https://docs.bitfinex.com/reference/rest-auth-orders-history
     */
    final public function history(?int $limit = null, ?int $start = null, ?int $end = null, ?int $sort = null): AuthenticatedBitfinexResponse
    {
        array_walk(compact('start', 'end', 'limit', 'sort'), fn ($v, $k) => $this->request->addBody($k, $v, true));
        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);
        $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.orders_history")->getPath());

        return $response->ordersHistory();
    }

    /**
     * Trades for a given order.
     *
     * @throws BitfinexPathNotFoundException
     * @throws GuzzleException
     *
     * @link https://docs.bitfinex.com/reference/rest-auth-order-trades
     */
    final public function orderTrades(BitfinexType $type, string $pairOrCurrency, int $orderId): AuthenticatedBitfinexResponse
    {
        $symbol = $type->symbol($pairOrCurrency);
        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);
        $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.order_trades", ['symbol' => $symbol, 'id' => $orderId])->getPath());

        return $response->orderTrades($symbol, $type);
    }

    /**
     * Trades history for a symbol.
     *
     * @throws BitfinexPathNotFoundException
     * @throws GuzzleException
     *
     * @link https://docs.bitfinex.com/reference/rest-auth-trades-history
     */
    final public function tradesHistory(BitfinexType $type, string $pairOrCurrency, ?int $start = null, ?int $end = null, ?int $limit = null, ?int $sort = null): AuthenticatedBitfinexResponse
    {
        $symbol = $type->symbol($pairOrCurrency);
        array_walk(compact('start', 'end', 'limit', 'sort'), fn ($v, $k) => $this->request->addBody($k, $v, true));
        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);
        $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.trades")->getPath());

        return $response->tradesHistory($symbol, $type);
    }

    /**
     * Ledgers history for a currency.
     *
     * @throws BitfinexPathNotFoundException
     * @throws GuzzleException
     *
     * @link https://docs.bitfinex.com/reference/rest-auth-ledgers
     */
    final public function ledgers(string $currency, ?int $start = null, ?int $end = null, ?int $limit = null, ?int $sort = null): AuthenticatedBitfinexResponse
    {
        array_walk(compact('start', 'end', 'limit', 'sort'), fn ($v, $k) => $this->request->addBody($k, $v, true));
        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);
        $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.ledgers", ['currency' => $currency])->getPath());

        return $response->ledgers();
    }
}
