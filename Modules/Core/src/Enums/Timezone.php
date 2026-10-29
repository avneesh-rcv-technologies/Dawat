<?php

namespace Modules\Core\Enums;

class Timezone
{
    /**
     * Get all available timezones for the application
     */
    public static function all(): array
    {
        return [
            // Americas
            'America/New_York' => 'Eastern Time (ET) - New York',
            'America/Chicago' => 'Central Time (CT) - Chicago',
            'America/Denver' => 'Mountain Time (MT) - Denver',
            'America/Los_Angeles' => 'Pacific Time (PT) - Los Angeles',
            'America/Phoenix' => 'Mountain Time (MT) - Phoenix (No DST)',
            'America/Anchorage' => 'Alaska Time (AKT) - Anchorage',
            'America/Honolulu' => 'Hawaii Time (HST) - Honolulu',
            'America/Toronto' => 'Eastern Time (ET) - Toronto',
            'America/Vancouver' => 'Pacific Time (PT) - Vancouver',
            'America/Mexico_City' => 'Central Time (CT) - Mexico City',
            'America/Sao_Paulo' => 'Brasilia Time (BRT) - São Paulo',
            'America/Buenos_Aires' => 'Argentina Time (ART) - Buenos Aires',

            // Europe
            'Europe/London' => 'Greenwich Mean Time (GMT) - London',
            'Europe/Dublin' => 'Greenwich Mean Time (GMT) - Dublin',
            'Europe/Paris' => 'Central European Time (CET) - Paris',
            'Europe/Berlin' => 'Central European Time (CET) - Berlin',
            'Europe/Rome' => 'Central European Time (CET) - Rome',
            'Europe/Madrid' => 'Central European Time (CET) - Madrid',
            'Europe/Amsterdam' => 'Central European Time (CET) - Amsterdam',
            'Europe/Brussels' => 'Central European Time (CET) - Brussels',
            'Europe/Vienna' => 'Central European Time (CET) - Vienna',
            'Europe/Stockholm' => 'Central European Time (CET) - Stockholm',
            'Europe/Athens' => 'Eastern European Time (EET) - Athens',
            'Europe/Helsinki' => 'Eastern European Time (EET) - Helsinki',
            'Europe/Istanbul' => 'Turkey Time (TRT) - Istanbul',
            'Europe/Moscow' => 'Moscow Standard Time (MSK) - Moscow',

            // Asia
            'Asia/Dubai' => 'Gulf Standard Time (GST) - Dubai',
            'Asia/Kolkata' => 'India Standard Time (IST) - India',
            'Asia/Karachi' => 'Pakistan Standard Time (PKT) - Karachi',
            'Asia/Dhaka' => 'Bangladesh Standard Time (BST) - Dhaka',
            'Asia/Bangkok' => 'Indochina Time (ICT) - Bangkok',
            'Asia/Singapore' => 'Singapore Time (SGT) - Singapore',
            'Asia/Kuala_Lumpur' => 'Malaysia Time (MYT) - Kuala Lumpur',
            'Asia/Jakarta' => 'Western Indonesia Time (WIB) - Jakarta',
            'Asia/Manila' => 'Philippine Time (PHT) - Manila',
            'Asia/Hong_Kong' => 'Hong Kong Time (HKT) - Hong Kong',
            'Asia/Shanghai' => 'China Standard Time (CST) - Shanghai',
            'Asia/Taipei' => 'Taipei Standard Time (TST) - Taipei',
            'Asia/Seoul' => 'Korea Standard Time (KST) - Seoul',
            'Asia/Tokyo' => 'Japan Standard Time (JST) - Tokyo',

            // Australia & Pacific
            'Australia/Perth' => 'Australian Western Time (AWST) - Perth',
            'Australia/Adelaide' => 'Australian Central Time (ACST) - Adelaide',
            'Australia/Darwin' => 'Australian Central Time (ACST) - Darwin',
            'Australia/Brisbane' => 'Australian Eastern Time (AEST) - Brisbane',
            'Australia/Sydney' => 'Australian Eastern Time (AEDT) - Sydney',
            'Australia/Melbourne' => 'Australian Eastern Time (AEDT) - Melbourne',
            'Pacific/Auckland' => 'New Zealand Time (NZDT) - Auckland',
            'Pacific/Fiji' => 'Fiji Time (FJT) - Fiji',

            // Africa
            'Africa/Cairo' => 'Eastern European Time (EET) - Cairo',
            'Africa/Johannesburg' => 'South Africa Standard Time (SAST) - Johannesburg',
            'Africa/Nairobi' => 'East Africa Time (EAT) - Nairobi',
            'Africa/Lagos' => 'West Africa Time (WAT) - Lagos',

            // UTC
            'UTC' => 'Coordinated Universal Time (UTC)',
        ];
    }

    /**
     * Get timezone label by key
     */
    public static function getLabel(string $timezone): string
    {
        return self::all()[$timezone] ?? $timezone;
    }

    /**
     * Get common timezones (shorter list for quick selection)
     */
    public static function common(): array
    {
        return [
            'UTC' => 'UTC',
            'America/New_York' => 'Eastern Time (ET)',
            'America/Chicago' => 'Central Time (CT)',
            'America/Denver' => 'Mountain Time (MT)',
            'America/Los_Angeles' => 'Pacific Time (PT)',
            'Europe/London' => 'London (GMT)',
            'Europe/Paris' => 'Paris (CET)',
            'Asia/Dubai' => 'Dubai (GST)',
            'Asia/Kolkata' => 'India (IST)',
            'Asia/Singapore' => 'Singapore (SGT)',
            'Asia/Tokyo' => 'Tokyo (JST)',
            'Australia/Sydney' => 'Sydney (AEDT)',
        ];
    }

    /**
     * Get grouped timezones by region
     */
    public static function grouped(): array
    {
        return [
            'Americas' => [
                'America/New_York' => 'Eastern Time (ET) - New York',
                'America/Chicago' => 'Central Time (CT) - Chicago',
                'America/Denver' => 'Mountain Time (MT) - Denver',
                'America/Los_Angeles' => 'Pacific Time (PT) - Los Angeles',
                'America/Toronto' => 'Eastern Time (ET) - Toronto',
                'America/Vancouver' => 'Pacific Time (PT) - Vancouver',
                'America/Mexico_City' => 'Central Time (CT) - Mexico City',
                'America/Sao_Paulo' => 'Brasilia Time (BRT) - São Paulo',
            ],
            'Europe' => [
                'Europe/London' => 'Greenwich Mean Time (GMT) - London',
                'Europe/Paris' => 'Central European Time (CET) - Paris',
                'Europe/Berlin' => 'Central European Time (CET) - Berlin',
                'Europe/Rome' => 'Central European Time (CET) - Rome',
                'Europe/Madrid' => 'Central European Time (CET) - Madrid',
                'Europe/Amsterdam' => 'Central European Time (CET) - Amsterdam',
                'Europe/Moscow' => 'Moscow Standard Time (MSK) - Moscow',
            ],
            'Asia' => [
                'Asia/Dubai' => 'Gulf Standard Time (GST) - Dubai',
                'Asia/Kolkata' => 'India Standard Time (IST) - India',
                'Asia/Singapore' => 'Singapore Time (SGT) - Singapore',
                'Asia/Hong_Kong' => 'Hong Kong Time (HKT) - Hong Kong',
                'Asia/Shanghai' => 'China Standard Time (CST) - Shanghai',
                'Asia/Tokyo' => 'Japan Standard Time (JST) - Tokyo',
                'Asia/Seoul' => 'Korea Standard Time (KST) - Seoul',
            ],
            'Australia & Pacific' => [
                'Australia/Sydney' => 'Australian Eastern Time (AEDT) - Sydney',
                'Australia/Melbourne' => 'Australian Eastern Time (AEDT) - Melbourne',
                'Australia/Brisbane' => 'Australian Eastern Time (AEST) - Brisbane',
                'Australia/Perth' => 'Australian Western Time (AWST) - Perth',
                'Pacific/Auckland' => 'New Zealand Time (NZDT) - Auckland',
            ],
            'Africa' => [
                'Africa/Cairo' => 'Eastern European Time (EET) - Cairo',
                'Africa/Johannesburg' => 'South Africa Standard Time (SAST) - Johannesburg',
                'Africa/Nairobi' => 'East Africa Time (EAT) - Nairobi',
                'Africa/Lagos' => 'West Africa Time (WAT) - Lagos',
            ],
            'Other' => [
                'UTC' => 'Coordinated Universal Time (UTC)',
            ],
        ];
    }
}
