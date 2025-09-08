<?php

declare(strict_types=1);

namespace EwertonDaniel\Bitfinex\Services\Authenticated;

use EwertonDaniel\Bitfinex\Builders\RequestBuilder;
use EwertonDaniel\Bitfinex\Builders\UrlBuilder;
use EwertonDaniel\Bitfinex\Exceptions\BitfinexPathNotFoundException;
use EwertonDaniel\Bitfinex\Http\Requests\BitfinexRequest;
use EwertonDaniel\Bitfinex\Http\Responses\AuthenticatedBitfinexResponse;
use EwertonDaniel\Bitfinex\ValueObjects\BitfinexCredentials;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Merchants (Bitfinex Pay) authenticated endpoints.
 *
 * This service wraps the `private.merchants` endpoints defined in resources/paths.json.
 * Methods accept payload arrays to keep flexibility with the API contract.
 */
class BitfinexAuthenticatedMerchants
{
    private readonly string $basePath;

    public function __construct(
        private readonly UrlBuilder $url,
        private readonly BitfinexCredentials $credentials,
        private readonly RequestBuilder $request,
        private readonly Client $client
    ) {
        $this->basePath = 'private.merchants';
    }

    /**
     * Create a Merchant invoice.
     *
     * @throws GuzzleException
     * @throws BitfinexPathNotFoundException
     */
    final public function submitInvoice(array $payload): AuthenticatedBitfinexResponse
    {
        $this->request->setBody($payload);
        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);
        $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.submit_invoice")->getPath());

        return $response->merchantInvoiceCreated();
    }

    /**
     * Create a POS (Point of Sale) invoice.
     *
     * @throws GuzzleException
     * @throws BitfinexPathNotFoundException
     */
    final public function submitPostInvoice(array $payload): AuthenticatedBitfinexResponse
    {
        $this->request->setBody($payload);
        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);
        $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.submit_post_invoice")->getPath());

        return $response->merchantPostInvoiceCreated();
    }

    /**
     * List invoices (non‑paginated, optional filters).
     */
    final public function invoiceList(array $filters = []): AuthenticatedBitfinexResponse
    {
        $this->request->setBody($filters);
        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);
        $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.invoice_list")->getPath());

        return $response->merchantInvoiceList();
    }

    /**
     * Paginated list of invoices (page/pageSize + optional filters).
     */
    final public function invoiceListPaginated(int $page = 1, int $pageSize = 30, array $filters = []): AuthenticatedBitfinexResponse
    {
        $body = array_merge(['page' => $page, 'pageSize' => $pageSize], $filters);
        $this->request->setBody($body);
        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);
        $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.invoice_list_paginated")->getPath());

        return $response->merchantInvoiceListPaginated();
    }

    /**
     * Invoices count statistics (optional filters).
     */
    final public function invoiceCountStats(array $filters = []): AuthenticatedBitfinexResponse
    {
        $this->request->setBody($filters);
        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);
        $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.invoice_count_stats")->getPath());

        return $response->merchantInvoiceCountStats();
    }

    /**
     * Invoices earnings statistics (optional filters).
     */
    final public function invoiceEarningsStats(array $filters = []): AuthenticatedBitfinexResponse
    {
        $this->request->setBody($filters);
        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);
        $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.invoice_earnings_stats")->getPath());

        return $response->merchantInvoiceEarningsStats();
    }

    /**
     * Mark an invoice as completed.
     */
    final public function completeInvoice(array $payload): AuthenticatedBitfinexResponse
    {
        $this->request->setBody($payload);
        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);
        $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.complete_invoice")->getPath());

        return $response->merchantInvoiceCompleted();
    }

    /**
     * Expire an invoice.
     */
    final public function expireInvoice(array $payload): AuthenticatedBitfinexResponse
    {
        $this->request->setBody($payload);
        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);
        $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.expire_invoice")->getPath());

        return $response->merchantInvoiceExpired();
    }

    /**
     * List configured currency conversions.
     */
    final public function currencyConversionList(array $filters = []): AuthenticatedBitfinexResponse
    {
        $this->request->setBody($filters);
        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);
        $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.currency_Conversion_list")->getPath());

        return $response->merchantCurrencyConversionList();
    }

    /**
     * Add a new currency conversion.
     */
    final public function addCurrencyConversion(array $payload): AuthenticatedBitfinexResponse
    {
        $this->request->setBody($payload);
        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);
        $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.add_currency_conversion")->getPath());

        return $response->merchantCurrencyConversionCreated();
    }

    /**
     * Remove an existing currency conversion.
     */
    final public function removeCurrencyConversion(array $payload): AuthenticatedBitfinexResponse
    {
        $this->request->setBody($payload);
        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);
        $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.remove_currency_conversion")->getPath());

        return $response->merchantCurrencyConversionRemoved();
    }

    /**
     * Get merchant daily limit.
     */
    final public function merchantLimit(array $filters = []): AuthenticatedBitfinexResponse
    {
        $this->request->setBody($filters);
        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);
        $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.merchant_limit")->getPath());

        return $response->merchantLimit();
    }

    /**
     * Write merchant settings.
     */
    final public function merchantSettingsWrite(array $settings): AuthenticatedBitfinexResponse
    {
        $this->request->setBody($settings);
        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);
        $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.merchant_settings_write")->getPath());

        return $response->merchantSettingsWrite();
    }

    /**
     * Write merchant settings in batch.
     */
    final public function merchantSettingsWriteBatch(array $settings): AuthenticatedBitfinexResponse
    {
        $this->request->setBody($settings);
        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);
        $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.merchant_settings_write_batch")->getPath());

        return $response->merchantSettingsWriteBatch();
    }

    /**
     * Read merchant settings.
     */
    final public function merchantSettingsRead(array $filters = []): AuthenticatedBitfinexResponse
    {
        $this->request->setBody($filters);
        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);
        $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.merchant_settings_read")->getPath());

        return $response->merchantSettingsRead();
    }

    /**
     * List available merchant settings/keys.
     */
    final public function merchantSettingsList(array $filters = []): AuthenticatedBitfinexResponse
    {
        $this->request->setBody($filters);
        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);
        $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.merchant_settings_list")->getPath());

        return $response->merchantSettingsList();
    }

    /**
     * List deposits linked to merchant invoices.
     */
    final public function depositsList(array $filters = []): AuthenticatedBitfinexResponse
    {
        $this->request->setBody($filters);
        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);
        $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.deposits_list")->getPath());

        return $response->merchantDepositsList();
    }

    /**
     * List deposits not linked to merchant invoices.
     */
    final public function unlinkedDepositsList(array $filters = []): AuthenticatedBitfinexResponse
    {
        $this->request->setBody($filters);
        $request = new BitfinexRequest($this->request, $this->credentials, $this->client);
        $response = $request->execute(apiPath: $this->url->setPath("$this->basePath.unlinked_deposits_list")->getPath());

        return $response->merchantUnlinkedDepositsList();
    }
}

