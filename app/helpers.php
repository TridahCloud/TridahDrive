<?php

use App\Helpers\CurrencyHelper;

if (!function_exists('currency_format')) {
    /**
     * Format currency amount
     *
     * @param float|string $amount
     * @param string|null $currencyCode Currency code (USD, EUR, etc.)
     * @param int $decimals Number of decimal places
     * @return string
     */
    function currency_format(float|string $amount, ?string $currencyCode = null, int $decimals = 2): string
    {
        return CurrencyHelper::format($amount, $currencyCode, $decimals);
    }
}

if (!function_exists('currency_for')) {
    /**
     * Format currency for a specific Drive or User
     * Automatically determines currency from Drive or User
     *
     * @param float|string $amount
     * @param \App\Models\Drive|null $drive
     * @param \App\Models\User|null $user
     * @param int $decimals
     * @return string
     */
    function currency_for(float|string $amount, ?object $drive = null, ?object $user = null, int $decimals = 2): string
    {
        return CurrencyHelper::formatFor($drive, $user, $amount, $decimals);
    }
}

if (!function_exists('currency_symbol')) {
    /**
     * Get currency symbol
     *
     * @param string|null $currencyCode Currency code (USD, EUR, etc.)
     * @return string
     */
    function currency_symbol(?string $currencyCode = null): string
    {
        return CurrencyHelper::getSymbol($currencyCode ?? 'USD');
    }
}

if (!function_exists('currency_code_for')) {
    /**
     * Get currency code from Drive or User
     *
     * @param \App\Models\Drive|null $drive
     * @param \App\Models\User|null $user
     * @return string
     */
    function currency_code_for(?object $drive = null, ?object $user = null): string
    {
        return CurrencyHelper::getCurrencyCode($drive, $user);
    }
}

