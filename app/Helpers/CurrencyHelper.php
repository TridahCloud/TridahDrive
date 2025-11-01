<?php

namespace App\Helpers;

class CurrencyHelper
{
    /**
     * Currency symbols and formatting
     */
    protected static array $currencies = [
        'USD' => ['symbol' => '$', 'name' => 'US Dollar', 'position' => 'before'],
        'EUR' => ['symbol' => '€', 'name' => 'Euro', 'position' => 'before'],
        'GBP' => ['symbol' => '£', 'name' => 'British Pound', 'position' => 'before'],
        'JPY' => ['symbol' => '¥', 'name' => 'Japanese Yen', 'position' => 'before'],
        'AUD' => ['symbol' => 'A$', 'name' => 'Australian Dollar', 'position' => 'before'],
        'CAD' => ['symbol' => 'C$', 'name' => 'Canadian Dollar', 'position' => 'before'],
        'CHF' => ['symbol' => 'CHF', 'name' => 'Swiss Franc', 'position' => 'before'],
        'CNY' => ['symbol' => '¥', 'name' => 'Chinese Yuan', 'position' => 'before'],
        'INR' => ['symbol' => '₹', 'name' => 'Indian Rupee', 'position' => 'before'],
        'MXN' => ['symbol' => 'MX$', 'name' => 'Mexican Peso', 'position' => 'before'],
        'BRL' => ['symbol' => 'R$', 'name' => 'Brazilian Real', 'position' => 'before'],
        'ZAR' => ['symbol' => 'R', 'name' => 'South African Rand', 'position' => 'before'],
        'KRW' => ['symbol' => '₩', 'name' => 'South Korean Won', 'position' => 'before'],
        'SGD' => ['symbol' => 'S$', 'name' => 'Singapore Dollar', 'position' => 'before'],
        'HKD' => ['symbol' => 'HK$', 'name' => 'Hong Kong Dollar', 'position' => 'before'],
        'NZD' => ['symbol' => 'NZ$', 'name' => 'New Zealand Dollar', 'position' => 'before'],
        'NOK' => ['symbol' => 'kr', 'name' => 'Norwegian Krone', 'position' => 'after'],
        'SEK' => ['symbol' => 'kr', 'name' => 'Swedish Krona', 'position' => 'after'],
        'DKK' => ['symbol' => 'kr', 'name' => 'Danish Krone', 'position' => 'after'],
        'PLN' => ['symbol' => 'zł', 'name' => 'Polish Zloty', 'position' => 'after'],
        'RUB' => ['symbol' => '₽', 'name' => 'Russian Ruble', 'position' => 'after'],
        'TRY' => ['symbol' => '₺', 'name' => 'Turkish Lira', 'position' => 'before'],
        'AED' => ['symbol' => 'د.إ', 'name' => 'UAE Dirham', 'position' => 'before'],
        'SAR' => ['symbol' => 'ر.س', 'name' => 'Saudi Riyal', 'position' => 'before'],
    ];

    /**
     * Get currency information by code
     */
    public static function getCurrency(string $code = 'USD'): array
    {
        return self::$currencies[strtoupper($code)] ?? self::$currencies['USD'];
    }

    /**
     * Get currency symbol
     */
    public static function getSymbol(string $code = 'USD'): string
    {
        return self::getCurrency($code)['symbol'];
    }

    /**
     * Get currency name
     */
    public static function getCurrencyName(string $code = 'USD'): string
    {
        return self::getCurrency($code)['name'];
    }

    /**
     * Get all available currencies
     */
    public static function getAllCurrencies(): array
    {
        return self::$currencies;
    }

    /**
     * Format currency amount
     *
     * @param float|string $amount
     * @param string|null $currencyCode Currency code, defaults to USD
     * @param int $decimals Number of decimal places
     * @return string
     */
    public static function format(float|string $amount, ?string $currencyCode = null, int $decimals = 2): string
    {
        $currencyCode = strtoupper($currencyCode ?? 'USD');
        $currency = self::getCurrency($currencyCode);
        $amount = (float) $amount;
        $formattedAmount = number_format($amount, $decimals);

        if ($currency['position'] === 'before') {
            return $currency['symbol'] . $formattedAmount;
        } else {
            return $formattedAmount . ' ' . $currency['symbol'];
        }
    }

    /**
     * Get currency code from Drive or User
     * Priority: 
     * - For shared drives: Drive currency > User currency > USD default
     * - For personal drives: User currency > Drive currency > USD default
     *
     * @param \App\Models\Drive|null $drive
     * @param \App\Models\User|null $user
     * @return string
     */
    public static function getCurrencyCode(?object $drive = null, ?object $user = null): string
    {
        // Determine if this is a personal drive
        $isPersonalDrive = false;
        if ($drive && method_exists($drive, 'getAttribute')) {
            $isPersonalDrive = $drive->getAttribute('type') === 'personal';
        }
        
        // Get drive currency if exists
        $driveCurrency = null;
        if ($drive && method_exists($drive, 'getAttribute')) {
            $driveCurrency = $drive->getAttribute('currency');
            if (!empty($driveCurrency)) {
                $driveCurrency = strtoupper($driveCurrency);
            } else {
                $driveCurrency = null;
            }
        }
        
        // Get user currency helper
        $getUserCurrency = function($userObject) {
            if (!$userObject) {
                return null;
            }
            
            if (method_exists($userObject, 'getAttribute')) {
                $currency = $userObject->getAttribute('currency');
            } else {
                $currency = $userObject->currency ?? null;
            }
            
            return !empty($currency) ? strtoupper($currency) : null;
        };
        
        // For personal drives, prioritize user currency (ignore drive currency even if set)
        if ($isPersonalDrive) {
            // Try authenticated user first - reload fresh from DB to get latest currency
            if (auth()->check()) {
                $userId = auth()->id();
                if ($userId) {
                    // Reload user from database to get latest currency
                    $authUser = \App\Models\User::find($userId);
                    if ($authUser) {
                        $userCurrency = $getUserCurrency($authUser);
                        if ($userCurrency) {
                            return $userCurrency;
                        }
                    }
                }
            }
            
            // Then provided user
            if ($user) {
                $userCurrency = $getUserCurrency($user);
                if ($userCurrency) {
                    return $userCurrency;
                }
            }
            
            // Fall back to drive currency only if user currency not available
            if ($driveCurrency) {
                return $driveCurrency;
            }
        } else {
            // For shared drives, prioritize drive currency
            if ($driveCurrency) {
                return $driveCurrency;
            }
            
            // Fall back to user currency
            if ($user) {
                $userCurrency = $getUserCurrency($user);
                if ($userCurrency) {
                    return $userCurrency;
                }
            }
            
            // Try authenticated user - reload fresh from DB to get latest currency
            if (auth()->check()) {
                $userId = auth()->id();
                if ($userId) {
                    // Reload user from database to get latest currency
                    $authUser = \App\Models\User::find($userId);
                    if ($authUser) {
                        $userCurrency = $getUserCurrency($authUser);
                        if ($userCurrency) {
                            return $userCurrency;
                        }
                    }
                }
            }
        }

        // Default to USD
        return 'USD';
    }

    /**
     * Format currency for a specific Drive or User
     *
     * @param float|string $amount
     * @param \App\Models\Drive|null $drive
     * @param \App\Models\User|null $user
     * @param int $decimals
     * @return string
     */
    public static function formatFor(?object $drive = null, ?object $user = null, float|string $amount = 0, int $decimals = 2): string
    {
        $currencyCode = self::getCurrencyCode($drive, $user);
        return self::format($amount, $currencyCode, $decimals);
    }
}

