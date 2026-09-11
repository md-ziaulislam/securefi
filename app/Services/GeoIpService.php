<?php

namespace App\Services;

use Stevebauman\Location\Facades\Location;

class GeoIpService
{
    /**
     * Resolve the 2-letter uppercase ISO country code of the visitor.
     * Hierarchy:
     * 1. Preview override (for admin testing via ?country=XX or session)
     * 2. Cloudflare Edge Header ('CF-IPCountry') - Instant 0ms latency in production
     * 3. stevebauman/location GeoIP lookup
     * 4. Fallback ('US')
     */
    public static function getCountryCode(?string $ip = null): string
    {
        $request = request();

        // 1. Admin Preview / Testing parameter
        if ($request && $request->has('country')) {
            $preview = strtoupper(trim($request->get('country')));
            if (strlen($preview) === 2) {
                return $preview;
            }
        }

        if ($request && session()->has('preview_country')) {
            return session('preview_country');
        }

        // 2. Cloudflare Edge Header
        if ($request && $request->hasHeader('CF-IPCountry')) {
            $cfCountry = strtoupper(trim($request->header('CF-IPCountry')));
            if (strlen($cfCountry) === 2 && !in_array($cfCountry, ['XX', 'T1'], true)) {
                return $cfCountry;
            }
        }

        // 3. stevebauman/location IP lookup
        $targetIp = $ip ?: ($request ? $request->ip() : '127.0.0.1');

        if (!in_array($targetIp, ['127.0.0.1', '::1', 'localhost', ''], true)) {
            try {
                $position = Location::get($targetIp);
                if ($position && !empty($position->countryCode)) {
                    return strtoupper($position->countryCode);
                }
            } catch (\Throwable $e) {
                // Silently fallback on network/database timeouts
            }
        }

        // 4. Default Fallback
        return 'US';
    }

    /**
     * 27 European Union member states for GDPR-compliant ad network targeting.
     */
    public static function getEuCountryCodes(): array
    {
        return [
            'AT', 'BE', 'BG', 'HR', 'CY', 'CZ', 'DK', 'EE', 'FI', 'FR',
            'DE', 'GR', 'HU', 'IE', 'IT', 'LV', 'LT', 'LU', 'MT', 'NL',
            'PL', 'PT', 'RO', 'SK', 'SI', 'ES', 'SE'
        ];
    }

    /**
     * Tier-1 High CPM / High Purchasing Power countries.
     */
    public static function getTier1CountryCodes(): array
    {
        return ['US', 'GB', 'CA', 'AU', 'NZ', 'DE', 'FR', 'IE', 'NL', 'CH', 'SE', 'NO', 'DK', 'SG', 'JP'];
    }

    /**
     * Comprehensive list of countries for dropdowns and selectors.
     */
    public static function getAllCountries(): array
    {
        return [
            'US' => 'United States',
            'GB' => 'United Kingdom',
            'CA' => 'Canada',
            'AU' => 'Australia',
            'DE' => 'Germany',
            'FR' => 'France',
            'IN' => 'India',
            'BD' => 'Bangladesh',
            'PK' => 'Pakistan',
            'NL' => 'Netherlands',
            'IT' => 'Italy',
            'ES' => 'Spain',
            'SE' => 'Sweden',
            'NO' => 'Norway',
            'CH' => 'Switzerland',
            'IE' => 'Ireland',
            'SG' => 'Singapore',
            'JP' => 'Japan',
            'BR' => 'Brazil',
            'MX' => 'Mexico',
            'ZA' => 'South Africa',
            'AE' => 'United Arab Emirates',
            'SA' => 'Saudi Arabia',
            'NZ' => 'New Zealand',
            'PH' => 'Philippines',
            'MY' => 'Malaysia',
            'ID' => 'Indonesia',
            'VN' => 'Vietnam',
            'TH' => 'Thailand',
            'NG' => 'Nigeria',
            'KE' => 'Kenya',
            'EG' => 'Egypt',
            'AR' => 'Argentina',
            'CL' => 'Chile',
            'CO' => 'Colombia',
            'PL' => 'Poland',
            'BE' => 'Belgium',
            'AT' => 'Austria',
            'DK' => 'Denmark',
            'FI' => 'Finland',
            'PT' => 'Portugal',
            'GR' => 'Greece',
            'CZ' => 'Czech Republic',
            'RO' => 'Romania',
            'HU' => 'Hungary',
            'IL' => 'Israel',
            'TR' => 'Turkey',
            'KR' => 'South Korea',
            'HK' => 'Hong Kong',
            'TW' => 'Taiwan',
        ];
    }
}
