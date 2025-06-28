<?php

namespace App\Helpers;

class CurrencyHelper
{
    /**
     * Format amount with currency symbol
     *
     * @param float|int $amount
     * @param string|null $currency
     * @param int|null $decimals
     * @return string
     */
    public static function format($amount, $currency = null, $decimals = null)
    {
        $currency = $currency ?? config('currency.default', 'NGN');
        $config = config("currency.currencies.{$currency}");
        
        if (!$config) {
            return number_format($amount, 2);
        }
        
        $decimals = $decimals ?? $config['decimals'];
        $symbol = $config['symbol'];
        $thousandsSeparator = $config['thousands_separator'];
        $decimalSeparator = $config['decimal_separator'];
        
        $formattedAmount = number_format($amount, $decimals, $decimalSeparator, $thousandsSeparator);
        
        $displayConfig = config('currency.display');
        $space = $displayConfig['space_between'] ? ' ' : '';
        
        if ($displayConfig['symbol_position'] === 'after') {
            return $formattedAmount . $space . $symbol;
        }
        
        return $symbol . $space . $formattedAmount;
    }

    /**
     * Get the currency symbol
     *
     * @param string|null $currency
     * @return string
     */
    public static function symbol($currency = null)
    {
        $currency = $currency ?? config('currency.default', 'NGN');
        return config("currency.currencies.{$currency}.symbol", '₦');
    }

    /**
     * Get the currency code
     *
     * @param string|null $currency
     * @return string
     */
    public static function code($currency = null)
    {
        $currency = $currency ?? config('currency.default', 'NGN');
        return config("currency.currencies.{$currency}.code", 'NGN');
    }

    /**
     * Get the currency name
     *
     * @param string|null $currency
     * @return string
     */
    public static function name($currency = null)
    {
        $currency = $currency ?? config('currency.default', 'NGN');
        return config("currency.currencies.{$currency}.name", 'Nigerian Naira');
    }

    /**
     * Get all available currencies
     *
     * @return array
     */
    public static function all()
    {
        return config('currency.currencies', []);
    }

    /**
     * Check if a currency is supported
     *
     * @param string $currency
     * @return bool
     */
    public static function isSupported($currency)
    {
        return array_key_exists($currency, config('currency.currencies', []));
    }
}