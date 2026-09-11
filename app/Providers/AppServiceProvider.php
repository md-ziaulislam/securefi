<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Implicitly grant "Super Admin" role all permissions
        Gate::before(function ($user, $ability) {
            return $user->hasRole(['Super Admin', 'super-admin']) ? true : null;
        });

        // Configure Rate Limiters
        $this->configureRateLimiting();

        // Apply dynamic SMTP settings from database if configured
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
                \App\Http\Controllers\Admin\SmtpController::applySmtpConfig();
            }
        } catch (\Throwable $e) {
            // Fallback gracefully during migrations / cache building
        }

        // Custom Timezone Blade Directives (PRD-ADDNEW 5.5)
        \Illuminate\Support\Facades\Blade::directive('localtime', function ($expression) {
            return "<?php echo app(\\App\\Services\\TimezoneService::class)->format({$expression}); ?>";
        });
        \Illuminate\Support\Facades\Blade::directive('relativetime', function ($expression) {
            return "<?php echo app(\\App\\Services\\TimezoneService::class)->diffForHumans({$expression}); ?>";
        });
    }

    /**
     * Configure rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        // API rate limiting: 60 requests per minute
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip())->response(function () {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Too many requests. Please try again later.',
                ], 429);
            });
        });

        // Contact form submission rate limiting: 5 requests per minute
        RateLimiter::for('contact', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        // Newsletter subscription: 5 requests per minute
        RateLimiter::for('newsletter', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        // Reader comments: 5 requests per minute
        RateLimiter::for('comment', function (Request $request) {
            return Limit::perMinute(5)->by($request->user()?->id ?: $request->ip());
        });

        // Site search: 30 requests per minute
        RateLimiter::for('search', function (Request $request) {
            return Limit::perMinute(30)->by($request->ip());
        });

        // Authentication attempts: 10 per minute per IP
        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });
    }
}
