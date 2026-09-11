<?php

namespace App\Http\Middleware;

use App\Models\Redirect;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectMiddleware
{
    /**
     * Handle an incoming request and apply 301/302 redirects if configured.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Ignore admin, livewire, api, or storage routes
        if ($request->is('admin*') || $request->is('storage*') || $request->is('api*')) {
            return $next($request);
        }

        $path = '/' . ltrim($request->path(), '/');

        try {
            $redirect = Redirect::active()
                ->where(function ($q) use ($path) {
                    $q->where('from_url', $path)
                      ->orWhere('from_url', ltrim($path, '/'));
                })
                ->first();

            if ($redirect) {
                $redirect->increment('hit_count');
                return redirect($redirect->to_url, $redirect->type ?: 301);
            }
        } catch (\Throwable $e) {
            // Fail gracefully if database is not reachable
        }

        return $next($request);
    }
}
