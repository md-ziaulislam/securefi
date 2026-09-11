<?php

namespace App\Services;

use App\Models\Setting;
use Carbon\Carbon;
use DateTimeZone;
use Illuminate\Support\Facades\Auth;

class TimezoneService
{
    /**
     * Resolve the active timezone for the current request.
     * Priority:
     * 1. Authenticated user's profile timezone.
     * 2. Visitor's detected browser/cookie timezone (if auto-detection enabled).
     * 3. Platform default timezone setting.
     * 4. System default ('UTC').
     */
    public function getCurrentTimezone(): string
    {
        // 1. Authenticated User Profile
        if (Auth::check() && !empty(Auth::user()->timezone)) {
            return Auth::user()->timezone;
        }

        // 2. Visitor auto-detection via cookie/session if enabled
        if ($this->isAutoDetectEnabled()) {
            $cookieTz = request()->cookie('visitor_timezone') ?? request()->header('X-Visitor-Timezone');
            if (!empty($cookieTz) && in_array($cookieTz, DateTimeZone::listIdentifiers(), true)) {
                return $cookieTz;
            }
        }

        // 3. Platform Default Setting
        $defaultTz = Setting::get('site_timezone', config('app.timezone', 'UTC'));
        if (in_array($defaultTz, DateTimeZone::listIdentifiers(), true)) {
            return $defaultTz;
        }

        return 'UTC';
    }

    /**
     * Check whether visitor timezone auto-detection is enabled in platform settings.
     */
    public function isAutoDetectEnabled(): bool
    {
        return Setting::get('auto_detect_timezone', '1') === '1';
    }

    /**
     * Get platform default timezone setting.
     */
    public function getPlatformDefaultTimezone(): string
    {
        return Setting::get('site_timezone', 'UTC');
    }

    /**
     * Convert any Carbon / DateTime instance to the active timezone and format it.
     */
    public function format($date, ?string $format = null): string
    {
        if (empty($date)) {
            return '';
        }

        if (!$date instanceof Carbon) {
            $date = Carbon::parse($date);
        }

        $tz = $this->getCurrentTimezone();
        $converted = $date->copy()->setTimezone($tz);

        if ($format === null) {
            $dateFormat = Setting::get('date_format', 'M d, Y');
            $timeFormat = Setting::get('time_format', 'g:i A');
            $format = "{$dateFormat} {$timeFormat}";
        }

        return $converted->format($format);
    }

    /**
     * Return relative localized time string (e.g. "3 hours ago").
     */
    public function diffForHumans($date): string
    {
        if (empty($date)) {
            return '';
        }

        if (!$date instanceof Carbon) {
            $date = Carbon::parse($date);
        }

        return $date->diffForHumans();
    }

    /**
     * Get an organized list of all standard timezones with UTC offset for dropdown select options.
     */
    public function getAllTimezones(): array
    {
        $timezones = [];
        $identifiers = DateTimeZone::listIdentifiers();
        $now = Carbon::now('UTC');

        foreach ($identifiers as $id) {
            $dtz = new DateTimeZone($id);
            $offset = $dtz->getOffset($now);
            $hours = intdiv($offset, 3600);
            $minutes = abs(intdiv($offset % 3600, 60));
            $sign = $hours >= 0 ? '+' : '-';
            $offsetFormatted = sprintf('UTC%s%02d:%02d', $sign, abs($hours), $minutes);

            $parts = explode('/', $id, 2);
            $region = $parts[0];
            $city = isset($parts[1]) ? str_replace('_', ' ', $parts[1]) : $id;

            $timezones[$region][] = [
                'id' => $id,
                'offset' => $offsetFormatted,
                'label' => "({$offsetFormatted}) {$city}",
                'raw_offset' => $offset,
            ];
        }

        // Sort each region by raw offset
        foreach ($timezones as $region => &$list) {
            usort($list, function ($a, $b) {
                return $a['raw_offset'] <=> $b['raw_offset'];
            });
        }

        return $timezones;
    }
}
