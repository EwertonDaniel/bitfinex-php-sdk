<?php

namespace EwertonDaniel\Bitfinex\Helpers;

class AssetToCurrencyConverter
{
    /**
     * @var float The conversion rate of the asset (e.g., BTC, ETH) to USD.
     */
    private float $assetUsdRate;

    /**
     * @var float The conversion rate of USD to the target currency (e.g., GBP, EUR).
     */
    private float $usdCurrencyRate;

    /**
     * Class constructor.
     *
     * @param  float  $assetUsdRate  The conversion rate of the asset to USD.
     * @param  float  $usdCurrencyRate  The conversion rate of USD to the target currency.
     */
    public function __construct(float $assetUsdRate, float $usdCurrencyRate)
    {
        $this->assetUsdRate = $assetUsdRate;
        $this->usdCurrencyRate = $usdCurrencyRate;
    }

    /**
     * Converts an asset directly to the target currency (e.g., BTC to EUR).
     *
     * @param  float  $assetAmount  The amount of the asset to be converted.
     * @return float The equivalent value of the asset in the target currency.
     */
    public function convertAssetToCurrency(float $assetAmount): float
    {
        $usdAmount = $this->convertAssetToUsd($assetAmount);

        return $this->convertUsdToCurrency($usdAmount);
    }

    /**
     * Converts an asset (e.g., BTC, ETH) to USD.
     *
     * @param  float  $assetAmount  The amount of the asset to be converted.
     * @return float The equivalent value of the asset in USD.
     */
    private function convertAssetToUsd(float $assetAmount): float
    {
        return $assetAmount * $this->assetUsdRate;
    }

    /**
     * Converts USD to another currency (e.g., GBP, EUR).
     *
     * @param  float  $usdAmount  The amount in USD to be converted.
     * @return float The equivalent value in the target currency.
     */
    public function convertUsdToCurrency(float $usdAmount): float
    {
        return $usdAmount * $this->usdCurrencyRate;
    }
}
