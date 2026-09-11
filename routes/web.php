<?php

use App\Http\Controllers\Admin\ArticleController as AdminArticleController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MediaController as AdminMediaController;
use App\Http\Controllers\Admin\ModuleController;
use App\Http\Controllers\Admin\TagController as AdminTagController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Frontend\AffiliateRedirectController;
use App\Http\Controllers\Frontend\ArticleController;
use App\Http\Controllers\Frontend\CategoryController;
use App\Http\Controllers\Frontend\ContactController;
use App\Http\Controllers\Frontend\FeedController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\NewsletterController;
use App\Http\Controllers\Frontend\PageController;
use App\Http\Controllers\Frontend\SearchController;
use App\Http\Controllers\Frontend\TagController;
use App\Models\Article;
use App\Models\Page;
use App\Models\Setting;
use Illuminate\Support\Facades\Route;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

/*
|--------------------------------------------------------------------------
| Public Frontend Routes
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/article/{slug}', [ArticleController::class, 'show'])->name('article.show');
Route::get('/category/{slug}', [CategoryController::class, 'show'])->name('category.show');
Route::get('/tag/{slug}', [TagController::class, 'show'])->name('tag.show');
Route::get('/search', [SearchController::class, 'index'])->middleware('throttle:search')->name('search');
Route::get('/page/{slug}', [PageController::class, 'show'])->name('page.show');
Route::get('/dev-info', fn() => view('frontend.dev-info'))->name('dev.info');

Route::post('/contact', [ContactController::class, 'submit'])->middleware('throttle:contact')->name('contact.submit');
Route::post('/newsletter', [NewsletterController::class, 'subscribe'])->middleware('throttle:newsletter')->name('newsletter.subscribe');
Route::post('/article/{slug}/comment', [\App\Http\Controllers\Frontend\CommentController::class, 'store'])->middleware('throttle:comment')->name('article.comment.store');

Route::get('/go/{identifier}', [AffiliateRedirectController::class, 'redirect'])->name('affiliate.redirect');
Route::get('/ad/click/{ad}', [\App\Http\Controllers\Frontend\AdClickController::class, 'click'])->name('ad.click');

// RSS & Atom Feeds
Route::get('/feed', [FeedController::class, 'index'])->name('feed.index');
Route::get('/category/{slug}/feed', [FeedController::class, 'category'])->name('feed.category');

// Public Health Check & Heartbeat Endpoint (UptimeRobot, Better Stack)
Route::get('/health', [\App\Http\Controllers\Frontend\HealthController::class, 'check'])->name('health');

// Dynamic robots.txt
Route::get('/robots.txt', function () {
    $content = Setting::get('robots_txt', "User-agent: *\nAllow: /\n\nSitemap: " . url('/sitemap.xml'));
    return response($content, 200, ['Content-Type' => 'text/plain']);
});

// llms.txt — AI/LLM Visibility File (https://llmstxt.org/)
Route::get('/llms.txt', function () {
    if (Setting::get('llms_enabled', '1') !== '1') {
        abort(404, 'llms.txt is disabled.');
    }

    // Auto-update from live data if enabled
    if (Setting::get('llms_auto_update', '0') === '1') {
        $controller = app(\App\Http\Controllers\Admin\LlmsController::class);
        $content    = $controller->generateDefault();
    } else {
        $content = Setting::get('llms_txt_content', '');
        if (empty($content)) {
            $controller = app(\App\Http\Controllers\Admin\LlmsController::class);
            $content    = $controller->generateDefault();
        }
    }

    return response($content, 200, [
        'Content-Type'  => 'text/plain; charset=UTF-8',
        'Cache-Control' => 'public, max-age=3600',
    ]);
})->name('llms.txt');

// Dynamic XML Sitemap
Route::get('/sitemap.xml', function () {
    if (Setting::get('sitemap_enabled', '1') !== '1') {
        abort(404, 'Sitemap is disabled by administrator.');
    }

    $sitemap = Sitemap::create();

    $sitemap->add(Url::create('/')->setPriority(1.0)->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY));

    if (Setting::get('sitemap_include_articles', '1') === '1') {
        foreach (Article::published()->latest('updated_at')->get() as $article) {
            $sitemap->add(Url::create('/article/' . $article->slug)
                ->setLastModificationDate($article->updated_at)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                ->setPriority(0.8));
        }
    }

    if (Setting::get('sitemap_include_pages', '1') === '1') {
        foreach (Page::published()->get() as $page) {
            $sitemap->add(Url::create('/page/' . $page->slug)
                ->setLastModificationDate($page->updated_at)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                ->setPriority(0.6));
        }
    }

    if (Setting::get('sitemap_include_categories', '1') === '1') {
        foreach (\App\Models\Category::where('is_active', true)->get() as $category) {
            $sitemap->add(Url::create('/category/' . $category->slug)
                ->setLastModificationDate($category->updated_at)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                ->setPriority(0.7));
        }
    }

    if (Setting::get('sitemap_include_tags', '0') === '1') {
        foreach (\App\Models\Tag::all() as $tag) {
            $sitemap->add(Url::create('/tag/' . $tag->slug)
                ->setLastModificationDate($tag->updated_at)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                ->setPriority(0.5));
        }
    }

    return $sitemap->toResponse(request());
});

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:auth')->name('login.submit');
Route::get('/2fa', [LoginController::class, 'showTwoFactorForm'])->name('auth.2fa');
Route::post('/2fa', [LoginController::class, 'verifyTwoFactor'])->middleware('throttle:auth')->name('auth.2fa.verify');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Admin Management Routes (Protected by Auth)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:Super Admin|super-admin|Admin|Editor|Author'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Staff Profile & Security / Two-Factor Authentication (2FA) (All Staff Roles)
    Route::get('/profile', [\App\Http\Controllers\Admin\ProfileController::class, 'index'])->name('profile.index');
    Route::get('/users/profile', [\App\Http\Controllers\Admin\ProfileController::class, 'index'])->name('users.profile');
    Route::put('/profile', [\App\Http\Controllers\Admin\ProfileController::class, 'update'])->name('profile.update');
    Route::put('/users/profile', [\App\Http\Controllers\Admin\ProfileController::class, 'update'])->name('users.profile.update');
    Route::post('/profile/password', [\App\Http\Controllers\Admin\ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::post('/profile/2fa/confirm', [\App\Http\Controllers\Admin\ProfileController::class, 'confirmTwoFactor'])->name('profile.2fa.confirm');
    Route::post('/profile/2fa/disable', [\App\Http\Controllers\Admin\ProfileController::class, 'disableTwoFactor'])->name('profile.2fa.disable');
    Route::post('/profile/2fa/recovery-codes', [\App\Http\Controllers\Admin\ProfileController::class, 'regenerateRecoveryCodes'])->name('profile.2fa.recovery_codes');

    // Content Management (Articles, Categories, Tags, Media)
    Route::middleware(['permission:manage articles'])->group(function () {
        Route::post('articles/bulk-action', [AdminArticleController::class, 'bulkAction'])->name('articles.bulk');
        Route::resource('articles', AdminArticleController::class);
        // Inline tag quick-create from article editor (AJAX, no page reload)
        Route::post('tags/quick-create', [AdminTagController::class, 'quickCreate'])->name('tags.quick-create');
    });

    Route::middleware(['permission:manage categories'])->group(function () {
        Route::resource('categories', AdminCategoryController::class)->except(['create', 'show', 'edit']);
    });

    Route::middleware(['permission:manage tags'])->group(function () {
        Route::resource('tags', AdminTagController::class)->except(['create', 'show', 'edit']);
    });

    Route::middleware(['permission:manage media'])->group(function () {
        Route::resource('media', AdminMediaController::class)->only(['index', 'store', 'destroy']);
    });

    Route::middleware(['permission:manage comments'])->group(function () {
        Route::get('comments', [\App\Http\Controllers\Admin\CommentController::class, 'index'])->name('comments.index');
        Route::post('comments/{comment}/approve', [\App\Http\Controllers\Admin\CommentController::class, 'approve'])->name('comments.approve');
        Route::post('comments/{comment}/reject', [\App\Http\Controllers\Admin\CommentController::class, 'reject'])->name('comments.reject');
        Route::post('comments/{comment}/spam', [\App\Http\Controllers\Admin\CommentController::class, 'markSpam'])->name('comments.spam');
        Route::delete('comments/{comment}', [\App\Http\Controllers\Admin\CommentController::class, 'destroy'])->name('comments.destroy');
        Route::post('comments/{comment}/reply', [\App\Http\Controllers\Admin\CommentController::class, 'reply'])->name('comments.reply');
    });

    // Ads Management Module
    Route::middleware(['permission:manage ads'])->group(function () {
        Route::get('/ads', [\App\Http\Controllers\Admin\AdSlotController::class, 'index'])->name('ads.index');
        Route::post('/ads', [\App\Http\Controllers\Admin\AdSlotController::class, 'store'])->name('ads.store');
        Route::put('/ads/{ad}', [\App\Http\Controllers\Admin\AdSlotController::class, 'update'])->name('ads.update');
        Route::post('/ads/{ad}/toggle', [\App\Http\Controllers\Admin\AdSlotController::class, 'toggle'])->name('ads.toggle');
        Route::post('/ads/{ad}/reset-stats', [\App\Http\Controllers\Admin\AdSlotController::class, 'resetStats'])->name('ads.reset_stats');
        Route::delete('/ads/{ad}', [\App\Http\Controllers\Admin\AdSlotController::class, 'destroy'])->name('ads.destroy');
    });

    // Affiliate Monetization Module
    Route::middleware(['permission:manage affiliates'])->group(function () {
        Route::get('/affiliates', [\App\Http\Controllers\Admin\AffiliateController::class, 'index'])->name('affiliates.index');
        Route::post('/affiliates', [\App\Http\Controllers\Admin\AffiliateController::class, 'store'])->name('affiliates.store');
        Route::put('/affiliates/{affiliate}', [\App\Http\Controllers\Admin\AffiliateController::class, 'update'])->name('affiliates.update');
        Route::post('/affiliates/{affiliate}/toggle', [\App\Http\Controllers\Admin\AffiliateController::class, 'toggle'])->name('affiliates.toggle');
        Route::post('/affiliates/{affiliate}/reset-clicks', [\App\Http\Controllers\Admin\AffiliateController::class, 'resetClicks'])->name('affiliates.reset_clicks');
        Route::delete('/affiliates/{affiliate}', [\App\Http\Controllers\Admin\AffiliateController::class, 'destroy'])->name('affiliates.destroy');
        Route::post('/affiliates/settings/disclosure', [\App\Http\Controllers\Admin\AffiliateController::class, 'updateSettings'])->name('affiliates.disclosure');
    });

    // SEO Suite Management
    Route::middleware(['permission:manage seo'])->group(function () {
        Route::get('/seo', [\App\Http\Controllers\Admin\SeoController::class, 'index'])->name('seo.index');
        Route::post('/seo/global', [\App\Http\Controllers\Admin\SeoController::class, 'updateGlobal'])->name('seo.global');
        Route::post('/seo/robots', [\App\Http\Controllers\Admin\SeoController::class, 'updateRobots'])->name('seo.robots');
        Route::post('/seo/scripts', [\App\Http\Controllers\Admin\SeoController::class, 'updateScripts'])->name('seo.scripts');
        Route::post('/seo/ping', [\App\Http\Controllers\Admin\SeoController::class, 'pingSitemap'])->name('seo.ping');
        Route::get('/seo/redirects/export', [\App\Http\Controllers\Admin\SeoController::class, 'exportRedirects'])->name('seo.redirects.export');
        Route::post('/seo/redirects', [\App\Http\Controllers\Admin\SeoController::class, 'storeRedirect'])->name('seo.redirects.store');
        Route::delete('/seo/redirects/{redirect}', [\App\Http\Controllers\Admin\SeoController::class, 'destroyRedirect'])->name('seo.redirects.destroy');
    });

    // Dynamic Pages (CMS) & Contact Inquiries
    Route::middleware(['permission:manage pages'])->group(function () {
        Route::resource('pages', \App\Http\Controllers\Admin\PageController::class);
        Route::post('pages/contact/{contact}/toggle', [\App\Http\Controllers\Admin\PageController::class, 'toggleContact'])->name('pages.contact.toggle');
        Route::delete('pages/contact/{contact}', [\App\Http\Controllers\Admin\PageController::class, 'destroyContact'])->name('pages.contact.destroy');
    });

    // Theme Customizer & Design Tokens (Super Admin)
    Route::middleware(['permission:manage theme'])->group(function () {
        Route::get('/theme', [\App\Http\Controllers\Admin\ThemeController::class, 'index'])->name('theme.index');
        Route::post('/theme', [\App\Http\Controllers\Admin\ThemeController::class, 'update'])->name('theme.update');
        Route::post('/theme/reset', [\App\Http\Controllers\Admin\ThemeController::class, 'reset'])->name('theme.reset');
    });

    // Live Traffic Monitor & Analytics
    Route::middleware(['permission:view analytics'])->group(function () {
        Route::get('/analytics', [\App\Http\Controllers\Admin\AnalyticsController::class, 'index'])->name('analytics.index');
        Route::get('/analytics/live-stats', [\App\Http\Controllers\Admin\AnalyticsController::class, 'liveStats'])->name('analytics.live_stats');
        Route::get('/analytics/export', [\App\Http\Controllers\Admin\AnalyticsController::class, 'exportCsv'])->name('analytics.export');
    });

    // Users & Access Control (Super Admin only)
    Route::middleware(['permission:manage users'])->group(function () {
        Route::resource('users', \App\Http\Controllers\Admin\UserController::class)->except(['create', 'show', 'edit']);
    });

    // Security & Audit Hardening (Super Admin only)
    Route::middleware(['permission:manage security|view logs'])->group(function () {
        Route::get('/security', [\App\Http\Controllers\Admin\SecurityController::class, 'index'])->name('security.index');
        Route::post('/security/backup', [\App\Http\Controllers\Admin\SecurityController::class, 'createBackup'])->name('security.backup');
        Route::post('/security/backup/cloud', [\App\Http\Controllers\Admin\SecurityController::class, 'createCloudBackup'])->name('security.backup_cloud');
        Route::post('/security/backup/clean', [\App\Http\Controllers\Admin\SecurityController::class, 'cleanBackups'])->name('security.backup_clean');
        Route::post('/security/backup/cloud-settings', [\App\Http\Controllers\Admin\SecurityController::class, 'updateCloudSettings'])->name('security.cloud_settings');
        Route::post('/security/backup/test-connection', [\App\Http\Controllers\Admin\SecurityController::class, 'testCloudConnection'])->name('security.test_connection');
        Route::get('/security/backup/download/{filename}', [\App\Http\Controllers\Admin\SecurityController::class, 'downloadBackup'])->name('security.download');
        Route::delete('/security/backup/{filename}', [\App\Http\Controllers\Admin\SecurityController::class, 'destroyBackup'])->name('security.delete_backup');
        Route::post('/security/clear-logins', [\App\Http\Controllers\Admin\SecurityController::class, 'clearLoginLogs'])->name('security.clear_logins');
    });

    // General & Branding Settings (Super Admin only)
    Route::middleware(['permission:manage settings'])->group(function () {
        Route::get('/settings', [\App\Http\Controllers\Admin\SettingsController::class, 'index'])->name('settings.index');
        Route::post('/settings', [\App\Http\Controllers\Admin\SettingsController::class, 'update'])->name('settings.update');
    });

    // RSS / Atom Feed Management
    Route::middleware(['permission:manage settings'])->group(function () {
        Route::get('/feed', [\App\Http\Controllers\Admin\FeedController::class, 'index'])->name('feed.index');
        Route::post('/feed', [\App\Http\Controllers\Admin\FeedController::class, 'update'])->name('feed.update');
    });

    // llms.txt — AI/LLM Visibility Management
    Route::middleware(['permission:manage settings'])->group(function () {
        Route::get('/llms', [\App\Http\Controllers\Admin\LlmsController::class, 'index'])->name('llms.index');
        Route::post('/llms', [\App\Http\Controllers\Admin\LlmsController::class, 'update'])->name('llms.update');
        Route::post('/llms/regenerate', [\App\Http\Controllers\Admin\LlmsController::class, 'regenerate'])->name('llms.regenerate');
    });

    // CDN Integration & Cloudflare Cache Purge
    Route::middleware(['permission:manage settings'])->group(function () {
        Route::get('/cdn', [\App\Http\Controllers\Admin\CdnController::class, 'index'])->name('cdn.index');
        Route::post('/cdn/settings', [\App\Http\Controllers\Admin\CdnController::class, 'updateSettings'])->name('cdn.settings');
        Route::post('/cdn/purge-all', [\App\Http\Controllers\Admin\CdnController::class, 'purgeAll'])->name('cdn.purge-all');
        Route::post('/cdn/purge-urls', [\App\Http\Controllers\Admin\CdnController::class, 'purgeUrls'])->name('cdn.purge-urls');
        Route::post('/cdn/test', [\App\Http\Controllers\Admin\CdnController::class, 'testConnection'])->name('cdn.test');
    });

    // Uptime Monitoring & Health Telemetry
    Route::middleware(['permission:manage settings'])->group(function () {
        Route::get('/uptime', [\App\Http\Controllers\Admin\UptimeController::class, 'index'])->name('uptime.index');
        Route::post('/uptime/settings', [\App\Http\Controllers\Admin\UptimeController::class, 'updateSettings'])->name('uptime.settings');
        Route::post('/uptime/refresh', [\App\Http\Controllers\Admin\UptimeController::class, 'refresh'])->name('uptime.refresh');
    });

    // Error Monitoring (Sentry)
    Route::middleware(['permission:manage settings'])->group(function () {
        Route::get('/errors', [\App\Http\Controllers\Admin\ErrorMonitoringController::class, 'index'])->name('errors.index');
        Route::post('/errors/settings', [\App\Http\Controllers\Admin\ErrorMonitoringController::class, 'updateSettings'])->name('errors.settings');
        Route::post('/errors/test', [\App\Http\Controllers\Admin\ErrorMonitoringController::class, 'test'])->name('errors.test');
    });




    // ── Email Marketing Suite ────────────────────────────────────────────
    // Direct Mail & Multi-Target Composer
    Route::get('/email/compose', [\App\Http\Controllers\Admin\DirectMailController::class, 'index'])->name('email.compose.index');
    Route::post('/email/compose', [\App\Http\Controllers\Admin\DirectMailController::class, 'send'])->name('email.compose.send');
    Route::post('/email/compose/test', [\App\Http\Controllers\Admin\DirectMailController::class, 'testSend'])->name('email.compose.test');

    // Subscribers
    Route::get('/email/subscribers', [\App\Http\Controllers\Admin\SubscriberController::class, 'index'])->name('email.subscribers.index');
    Route::delete('/email/subscribers/{subscriber}', [\App\Http\Controllers\Admin\SubscriberController::class, 'destroy'])->name('email.subscribers.destroy');
    Route::post('/email/subscribers/bulk-delete', [\App\Http\Controllers\Admin\SubscriberController::class, 'bulkDestroy'])->name('email.subscribers.bulk-destroy');
    Route::get('/email/subscribers/export', [\App\Http\Controllers\Admin\SubscriberController::class, 'export'])->name('email.subscribers.export');

    // SMTP Configuration
    Route::get('/email/smtp', [\App\Http\Controllers\Admin\SmtpController::class, 'index'])->name('email.smtp.index');
    Route::post('/email/smtp', [\App\Http\Controllers\Admin\SmtpController::class, 'update'])->name('email.smtp.update');
    Route::post('/email/smtp/test', [\App\Http\Controllers\Admin\SmtpController::class, 'test'])->name('email.smtp.test');

    // Email Templates
    Route::get('/email/templates', [\App\Http\Controllers\Admin\EmailTemplateController::class, 'index'])->name('email.templates.index');
    Route::get('/email/templates/create', [\App\Http\Controllers\Admin\EmailTemplateController::class, 'create'])->name('email.templates.create');
    Route::post('/email/templates', [\App\Http\Controllers\Admin\EmailTemplateController::class, 'store'])->name('email.templates.store');
    Route::get('/email/templates/{template}/edit', [\App\Http\Controllers\Admin\EmailTemplateController::class, 'edit'])->name('email.templates.edit');
    Route::put('/email/templates/{template}', [\App\Http\Controllers\Admin\EmailTemplateController::class, 'update'])->name('email.templates.update');
    Route::delete('/email/templates/{template}', [\App\Http\Controllers\Admin\EmailTemplateController::class, 'destroy'])->name('email.templates.destroy');
    Route::get('/email/templates/{template}/preview', [\App\Http\Controllers\Admin\EmailTemplateController::class, 'preview'])->name('email.templates.preview');

    // Email Campaigns
    Route::get('/email/campaigns', [\App\Http\Controllers\Admin\EmailCampaignController::class, 'index'])->name('email.campaigns.index');
    Route::get('/email/campaigns/create', [\App\Http\Controllers\Admin\EmailCampaignController::class, 'create'])->name('email.campaigns.create');
    Route::post('/email/campaigns', [\App\Http\Controllers\Admin\EmailCampaignController::class, 'store'])->name('email.campaigns.store');
    Route::post('/email/campaigns/{campaign}/send', [\App\Http\Controllers\Admin\EmailCampaignController::class, 'send'])->name('email.campaigns.send');
    Route::post('/email/campaigns/{campaign}/test', [\App\Http\Controllers\Admin\EmailCampaignController::class, 'testSend'])->name('email.campaigns.test');
    Route::delete('/email/campaigns/{campaign}', [\App\Http\Controllers\Admin\EmailCampaignController::class, 'destroy'])->name('email.campaigns.destroy');

    // Delivery & Audit Logs
    Route::get('/email/logs', [\App\Http\Controllers\Admin\EmailLogController::class, 'index'])->name('email.logs.index');
    Route::delete('/email/logs/{log}', [\App\Http\Controllers\Admin\EmailLogController::class, 'destroy'])->name('email.logs.destroy');
    Route::post('/email/logs/clear', [\App\Http\Controllers\Admin\EmailLogController::class, 'clear'])->name('email.logs.clear');
});

// Public unsubscribe route
Route::get('/newsletter/unsubscribe/{token}', function (string $token) {
    $subscriber = \App\Models\NewsletterSubscriber::where('unsubscribe_token', $token)->firstOrFail();
    $subscriber->update(['status' => 'unsubscribed', 'unsubscribed_at' => now()]);
    return view('frontend.unsubscribed');
})->name('newsletter.unsubscribe');

