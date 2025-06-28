<?php

namespace App\Helpers;

class CurrencyHelper
{
    /**
     * Format amount with Naira currency symbol
     *
     * @param float|int $amount
     * @param int $decimals
     * @return string
     */
    public static function format($amount, $decimals = 2)
    {
        return '₦' . number_format($amount, $decimals);
    }

    /**
     * Get the currency symbol
     *
     * @return string
     */
    public static function symbol()
    {
        return '₦';
    }

    /**
     * Get the currency code
     *
     * @return string
     */
    public static function code()
    {
        return 'NGN';
    }

    /**
     * Get the currency name
     *
     * @return string
     */
    public static function name()
    {
        return 'Nigerian Naira';
    }
}