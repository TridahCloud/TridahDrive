<?php

namespace App\Helpers;

use Carbon\Carbon;
use App\Models\Drive;
use App\Models\User;

class TimezoneHelper
{
    /**
     * Get the timezone for a Drive
     */
    public static function getDriveTimezone(?Drive $drive = null): string
    {
        if ($drive) {
            return $drive->getEffectiveTimezone();
        }
        
        return config('app.timezone', 'UTC');
    }

    /**
     * Get the timezone for a User (fallback to drive or UTC if not set)
     */
    public static function getUserTimezone(?User $user = null, ?Drive $drive = null): string
    {
        if (!$user) {
            $user = auth()->user();
        }
        
        if ($user && $user->timezone) {
            return $user->timezone;
        }
        
        // Fallback to drive timezone if user doesn't have one set
        if ($drive) {
            return self::getDriveTimezone($drive);
        }
        
        return config('app.timezone', 'UTC');
    }

    /**
     * Convert a Carbon date/time from UTC (database) to User timezone for display
     * Database stores times in UTC, so we convert directly from UTC to user timezone
     */
    public static function toUserTimezone(Carbon $date, ?Drive $drive = null, ?User $user = null): Carbon
    {
        $userTimezone = self::getUserTimezone($user, $drive);
        
        // Database stores times in UTC, so ensure the Carbon instance is explicitly set to UTC
        // Laravel datetime casts should preserve UTC, but we'll ensure it
        $utcDate = $date->timezoneName === 'UTC' ? $date : $date->setTimezone('UTC');
        
        // Convert from UTC to user timezone
        return $utcDate->copy()->setTimezone($userTimezone);
    }

    /**
     * Convert a Carbon date/time from User timezone to Drive timezone for storage
     */
    public static function toDriveTimezone(Carbon $date, Drive $drive, ?User $user = null): Carbon
    {
        $driveTimezone = self::getDriveTimezone($drive);
        $userTimezone = self::getUserTimezone($user);
        
        // If timezones are the same, no conversion needed
        if ($driveTimezone === $userTimezone) {
            return $date->copy();
        }
        
        // Convert from user timezone to drive timezone
        return $date->copy()->setTimezone($userTimezone)->setTimezone($driveTimezone);
    }

    /**
     * Format a date/time in the user's timezone
     * Database stores times in UTC, so we convert from UTC to user timezone
     */
    public static function formatForUser(Carbon $date, ?Drive $drive = null, string $format = 'Y-m-d H:i:s', ?User $user = null): string
    {
        $converted = self::toUserTimezone($date, $drive, $user);
        return $converted->format($format);
    }

    /**
     * Get a list of common timezones
     */
    public static function getCommonTimezones(): array
    {
        return [
            'UTC' => 'UTC (Coordinated Universal Time)',
            'America/New_York' => 'Eastern Time (US & Canada)',
            'America/Chicago' => 'Central Time (US & Canada)',
            'America/Denver' => 'Mountain Time (US & Canada)',
            'America/Los_Angeles' => 'Pacific Time (US & Canada)',
            'America/Phoenix' => 'Arizona',
            'America/Anchorage' => 'Alaska',
            'America/Honolulu' => 'Hawaii',
            'Europe/London' => 'London',
            'Europe/Paris' => 'Paris',
            'Europe/Berlin' => 'Berlin',
            'Europe/Rome' => 'Rome',
            'Europe/Madrid' => 'Madrid',
            'Europe/Amsterdam' => 'Amsterdam',
            'Europe/Stockholm' => 'Stockholm',
            'Europe/Vienna' => 'Vienna',
            'Europe/Zurich' => 'Zurich',
            'Asia/Tokyo' => 'Tokyo',
            'Asia/Shanghai' => 'Shanghai',
            'Asia/Hong_Kong' => 'Hong Kong',
            'Asia/Singapore' => 'Singapore',
            'Asia/Dubai' => 'Dubai',
            'Asia/Kolkata' => 'Mumbai, Kolkata, New Delhi',
            'Australia/Sydney' => 'Sydney',
            'Australia/Melbourne' => 'Melbourne',
            'Australia/Brisbane' => 'Brisbane',
            'Australia/Perth' => 'Perth',
            'Pacific/Auckland' => 'Auckland',
            'America/Toronto' => 'Toronto',
            'America/Vancouver' => 'Vancouver',
            'America/Mexico_City' => 'Mexico City',
            'America/Sao_Paulo' => 'São Paulo',
            'America/Buenos_Aires' => 'Buenos Aires',
        ];
    }

    /**
     * Get all available timezones
     */
    public static function getAllTimezones(): array
    {
        return \DateTimeZone::listIdentifiers();
    }

    /**
     * Create a Carbon instance in the Drive's timezone
     */
    public static function createInDriveTimezone(Drive $drive, string $dateTime = 'now'): Carbon
    {
        $timezone = self::getDriveTimezone($drive);
        return Carbon::parse($dateTime, $timezone);
    }

    /**
     * Create a Carbon instance from user input (assumed to be in user's timezone)
     * and convert it to UTC for database storage
     */
    public static function parseFromUserInput(string $dateTime, ?Drive $drive = null, ?User $user = null): Carbon
    {
        $userTimezone = self::getUserTimezone($user, $drive);
        
        // Parse the date in user's timezone
        $carbon = Carbon::parse($dateTime, $userTimezone);
        
        // Convert to UTC for database storage
        return $carbon->setTimezone('UTC');
    }
}

