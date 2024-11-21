<?php

namespace EwertonDaniel\Bitfinex\Core\Services\Authenticated;

use EwertonDaniel\Bitfinex\Core\Builders\RequestBuilder;
use EwertonDaniel\Bitfinex\Core\Builders\UrlBuilder;
use EwertonDaniel\Bitfinex\Core\ValueObjects\BitfinexCredentials;
use EwertonDaniel\Bitfinex\Enums\OrderAction;
use EwertonDaniel\Bitfinex\Enums\OrderType;
use EwertonDaniel\Bitfinex\Exceptions\BitfinexPathNotFoundException;
use EwertonDaniel\Bitfinex\Helpers\GetThis;
use EwertonDaniel\Bitfinex\Http\Requests\BitfinexRequest;
use EwertonDaniel\Bitfinex\Http\Responses\BitfinexResponse;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Class BitfinexAuthenticatedOrder
 *
 * Handles authenticated order operations for the Bitfinex API, including retrieving and submitting orders.
 * Provides flexibility in specifying order parameters and supports dynamic paths for API interactions.
 *
 * @author  Ewerton
 *
 * @contact contact@ewertondaniel.work
 */
class BitfinexAuthenticatedOrder
{
    /** @var string Base path for order endpoints */
    private readonly string $basePath;

    /**
     * BitfinexAuthenticatedOrder constructor.
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
     * @throws GuzzleException
     * @throws BitfinexPathNotFoundException
     */
    final public function retrieve(
        ?string $symbol = null,
        ?array $id = null,
        ?int $gid = null,
        ?string $cid = null,
        ?string $cidDate = null
    ): BitfinexResponse {
        $params = [
            'id' => $id,
            'gid' => $gid,
            'cid' => $cid,
            'cid_date' => $cidDate,
        ];

        array_walk($params, fn ($value, $key) => $this->request->addBody($key, $value, true));

        $request = (new BitfinexRequest($this->request, $this->credentials, $this->client));

        $response = GetThis::ifTrueOrFallback(
            boolean: $symbol,
            callback: fn () => $request->execute(
                apiPath: $this->url->setPath("$this->basePath.retrieve_orders_by_symbol", ['symbol' => "t$symbol"])->getPath()
            ),
            fallback: fn () => $request->execute(
                apiPath: $this->url->setPath("$this->basePath.retrieve_orders")->getPath()
            )
        );

        return $response->retrieveOrders();
    }

    /**
     * Submit a new order with specified parameters.
     *
     * @param  OrderType  $type  Type of the order (e.g., LIMIT, MARKET).
     * @param  OrderAction  $action  Action for the order (e.g., BUY, SELL).
     * @param  string  $symbol  Symbol for the order (e.g., BTCUSD).
     * @param  float  $amount  Amount for the order.
     * @param  int  $price  Price for the order.
     * @param  int|null  $leverage  Leverage for margin trading.
     * @param  string|null  $priceTrailing  Trailing price for trailing stop orders.
     * @param  string|null  $priceAuxLimit  Auxiliary limit price for stop-limit orders.
     * @param  string|null  $priceOcoStop  Price for one-cancels-other (OCO) stop orders.
     * @param  int|null  $gid  Group ID for the order.
     * @param  int|null  $cid  Client ID for the order.
     * @param  int|null  $flags  Flags indicating additional functionalities for the order.
     *                           - **Hidden (64)**: Ensures the order does not appear in the order book and does not influence other market participants.
     *                           - To toggle 'visible on hit', add a meta object to your order with `{make_visible: 1}`.
     *                           - **Close (512)**: Closes the position if one is present.
     *                           - **Reduce Only (1024)**: Ensures that the executed order does not flip the opened position.
     *                           - **Post Only (4096)**: Ensures the limit order is added to the order book and does not match with a pre-existing order unless it is hidden.
     *                           - **OCO (16384)**: One Cancels Other – Allows placing a pair of orders where if one is executed fully or partially, the other is automatically canceled.
     *                           - **No Var Rates (524288)**: Excludes variable rate funding offers from matching against this order (for margin).
     * @param  string|null  $tif  Time in force for the order.
     * @param  array|null  $meta  Metadata for the order.
     * @return BitfinexResponse Parsed response containing the order details.
     *
     * @throws GuzzleException If the HTTP request fails.
     * @throws BitfinexPathNotFoundException If the API path cannot be resolved.
     */
    final public function submit(
        OrderType $type,
        OrderAction $action,
        string $symbol,
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
    ): BitfinexResponse {

        $this->request->setBody([
            'type' => $type->value,
            'symbol' => "t$symbol",
            'amount' => (string)GetThis::ifTrueOrFallback($action->isSell(), fn () => $amount * -1, $amount),
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
}
