<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use App\Models\VisitorLog;
use Closure;
use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;
use Stevebauman\Location\Facades\Location;
use Symfony\Component\HttpFoundation\Response;

class VisitorTrackingMiddleware
{
    /**
     * Handle an incoming request and track visitor analytics.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only track successful GET requests on public frontend
        if ($request->method() !== 'GET' || $response->getStatusCode() >= 400) {
            return $response;
        }

        $path = $request->path();

        // Skip internal/admin/assets/api paths
        if ($request->is('admin*') || 
            $request->is('storage*') || 
            $request->is('build*') || 
            $request->is('vendor*') || 
            $request->is('up') || 
            $request->is('livewire*') ||
            $request->is('api*') ||
            $request->ajax()) {
            return $response;
        }

        try {
            $this->trackVisitor($request);
        } catch (\Throwable $e) {
            // Fail silently so tracking never breaks the user experience
        }

        return $response;
    }

    protected function trackVisitor(Request $request): void
    {
        $ip = $request->ip();
        $sessionId = session()->getId();
        $userAgent = (string) $request->userAgent();
        $url = '/' . ltrim($request->path(), '/');

        // Bot / AI Crawler Detection
        $botName = null;
        $isBot = false;

        $bots = [
            'Googlebot' => 'Googlebot',
            'Bingbot' => 'Bingbot',
            'GPTBot' => 'GPTBot (OpenAI)',
            'ChatGPT-User' => 'ChatGPT (OpenAI)',
            'ClaudeBot' => 'ClaudeBot (Anthropic)',
            'anthropic-ai' => 'Anthropic AI',
            'PerplexityBot' => 'PerplexityBot',
            'Applebot' => 'Applebot',
            'Bytespider' => 'Bytespider',
            'CCBot' => 'Common Crawl',
            'facebookexternalhit' => 'Facebook Crawler',
            'Twitterbot' => 'Twitterbot',
            'DuckDuckBot' => 'DuckDuckBot',
            'Baiduspider' => 'Baidu Spider',
            'YandexBot' => 'Yandex Bot',
            'AhrefsBot' => 'AhrefsBot',
            'SemrushBot' => 'SemrushBot',
        ];

        foreach ($bots as $signature => $name) {
            if (stripos($userAgent, $signature) !== false) {
                $isBot = true;
                $botName = $name;
                break;
            }
        }

        $agent = new Agent();

        if (!$isBot && $agent->isRobot()) {
            $isBot = true;
            $botName = $agent->robot() ?: 'Web Crawler';
        }

        // Device, OS, Browser
        $deviceType = $isBot ? 'Bot' : ($agent->isMobile() ? 'Mobile' : ($agent->isTablet() ? 'Tablet' : 'Desktop'));
        $os = $agent->platform() ?: ($isBot ? 'Crawler' : 'Unknown');
        $browser = $agent->browser() ?: ($isBot ? 'Bot Client' : 'Unknown');

        // GeoIP Location
        $country = 'Unknown';
        $city = 'Unknown';

        if (in_array($ip, ['127.0.0.1', '::1', 'localhost'])) {
            $country = 'Localhost';
            $city = 'Internal Network';
        } else {
            try {
                $position = Location::get($ip);
                if ($position) {
                    $country = $position->countryName ?: ($position->countryCode ?: 'Unknown');
                    $city = $position->cityName ?: 'Unknown';
                }
            } catch (\Throwable $e) {}
        }

        // Referrer Classification
        $rawReferer = $request->headers->get('referer');
        $referrerSource = 'Direct';

        if ($rawReferer) {
            $refHost = parse_url($rawReferer, PHP_URL_HOST);
            $appHost = parse_url(config('app.url'), PHP_URL_HOST);

            if ($refHost && $refHost !== $appHost) {
                if (stripos($refHost, 'google.') !== false) {
                    $referrerSource = 'Google Search';
                } elseif (stripos($refHost, 'bing.') !== false) {
                    $referrerSource = 'Bing Search';
                } elseif (stripos($refHost, 't.co') !== false || stripos($refHost, 'twitter.') !== false || stripos($refHost, 'x.com') !== false) {
                    $referrerSource = 'Twitter / X';
                } elseif (stripos($refHost, 'linkedin.') !== false) {
                    $referrerSource = 'LinkedIn';
                } elseif (stripos($refHost, 'reddit.') !== false) {
                    $referrerSource = 'Reddit';
                } elseif (stripos($refHost, 'facebook.') !== false) {
                    $referrerSource = 'Facebook';
                } else {
                    $referrerSource = $refHost;
                }
            }
        }

        // IP Anonymization (GDPR compliance setting)
        $loggedIp = $ip;
        if (Setting::get('anonymize_ip', '0') === '1' && $ip && $ip !== '127.0.0.1') {
            $parts = explode('.', $ip);
            if (count($parts) === 4) {
                $parts[3] = '0';
                $loggedIp = implode('.', $parts);
            }
        }

        // Returning visitor check
        $isReturning = VisitorLog::where(function ($q) use ($sessionId, $loggedIp) {
            $q->where('session_id', $sessionId)
              ->orWhere('ip_address', $loggedIp);
        })->where('created_at', '<', now()->subHours(2))->exists();

        // Throttle rapid repeated visits to the exact same page within 60s
        $recentLog = VisitorLog::where('session_id', $sessionId)
            ->where('current_page', $url)
            ->where('last_seen_at', '>=', now()->subSeconds(60))
            ->latest('id')
            ->first();

        if ($recentLog) {
            $recentLog->update([
                'last_seen_at' => now(),
            ]);
            return;
        }

        VisitorLog::create([
            'ip_address' => $loggedIp,
            'session_id' => $sessionId,
            'country' => $country,
            'city' => $city,
            'device_type' => $deviceType,
            'os' => $os,
            'browser' => $browser,
            'referrer_source' => $referrerSource,
            'current_page' => $url,
            'is_bot' => $isBot,
            'bot_name' => $botName,
            'is_returning' => $isReturning,
            'last_seen_at' => now(),
        ]);
    }
}
