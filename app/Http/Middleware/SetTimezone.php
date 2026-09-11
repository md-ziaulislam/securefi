<?php

namespace App\Http\Middleware;

use App\Services\TimezoneService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class SetTimezone
{
    /**
     * Handle an incoming request and bind active timezone to view and environment.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $timezoneService = app(TimezoneService::class);
        $activeTimezone = $timezoneService->getCurrentTimezone();

        // Share active timezone with all Blade views
        View::share('currentTimezone', $activeTimezone);
        View::share('isTimezoneAutoDetect', $timezoneService->isAutoDetectEnabled());

        $response = $next($request);

        // If client sends a header or cookie is missing but resolved, ensure cookie header
        if ($timezoneService->isAutoDetectEnabled() && $request->hasHeader('X-Visitor-Timezone')) {
            $response->headers->setCookie(cookie('visitor_timezone', $request->header('X-Visitor-Timezone'), 60 * 24 * 365, null, null, false, false));
        }

        return $response;
    }
}
