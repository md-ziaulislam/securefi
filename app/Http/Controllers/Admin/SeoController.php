<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use App\Models\Page;
use App\Models\Redirect;
use App\Models\Setting;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SeoController extends Controller
{
    public function index()
    {
        $settings = Setting::whereIn('group', ['seo', 'general', 'scripts', 'social'])->pluck('value', 'key');

        $redirects = Redirect::latest()->paginate(25);
        $totalRedirects = Redirect::count();
        $totalRedirectHits = (int) Redirect::sum('hit_count');

        // Real-time Content SEO Audit
        $totalArticles = Article::published()->count();
        $articlesWithTitle = Article::published()->whereHas('seoMeta', fn($q) => $q->whereNotNull('meta_title')->where('meta_title', '!=', ''))->count();
        $articlesWithDesc = Article::published()->whereHas('seoMeta', fn($q) => $q->whereNotNull('meta_description')->where('meta_description', '!=', ''))->count();
        $articlesWithOg = Article::published()->whereHas('seoMeta', fn($q) => $q->whereNotNull('og_image')->where('og_image', '!=', ''))->count();

        $articlesNeedingSeo = Article::published()
            ->whereDoesntHave('seoMeta', fn($q) => $q->whereNotNull('meta_title')->where('meta_title', '!=', ''))
            ->latest('published_at')
            ->limit(8)
            ->get();

        $totalPages = Page::published()->count();
        $totalCategories = Category::where('is_active', true)->count();
        $totalTags = Tag::count();

        // Calculate Overall Site SEO Health Score (0 - 100)
        $score = 0;
        if (!empty($settings['site_name'])) $score += 8;
        if (!empty($settings['site_tagline'])) $score += 6;
        if (!empty($settings['site_description'])) $score += 10;
        if (!empty($settings['default_og_image'])) $score += 8;
        if (($settings['sitemap_enabled'] ?? '1') === '1') $score += 10;
        if (!empty($settings['robots_txt'])) $score += 8;
        if (!empty($settings['google_verification'])) $score += 10;

        if ($totalArticles > 0) {
            $titleRatio = $articlesWithTitle / $totalArticles;
            $descRatio  = $articlesWithDesc / $totalArticles;
            $score += (int) round(($titleRatio * 20) + ($descRatio * 20));
        } else {
            $score += 40;
        }

        $seoHealthScore = min(100, max(15, $score));

        return view('admin.seo.index', compact(
            'settings',
            'redirects',
            'totalRedirects',
            'totalRedirectHits',
            'totalArticles',
            'articlesWithTitle',
            'articlesWithDesc',
            'articlesWithOg',
            'articlesNeedingSeo',
            'totalPages',
            'totalCategories',
            'totalTags',
            'seoHealthScore'
        ));
    }

    public function updateGlobal(Request $request)
    {
        $validated = $request->validate([
            // Core
            'site_name'                => 'required|string|max:100',
            'site_tagline'             => 'nullable|string|max:200',
            'site_description'         => 'nullable|string|max:500',
            'title_separator'          => 'nullable|string|max:5',
            'default_og_image'         => 'nullable|string|max:500',
            'meta_robots_default'      => 'nullable|string|max:150',
            'meta_keywords'            => 'nullable|string|max:300',
            'canonical_force_https'    => 'nullable|in:0,1',
            // Webmaster Verifications
            'google_verification'      => 'nullable|string|max:255',
            'bing_verification'        => 'nullable|string|max:255',
            'yandex_verification'      => 'nullable|string|max:255',
            'pinterest_verification'   => 'nullable|string|max:255',
            'baidu_verification'       => 'nullable|string|max:255',
            // Social & Knowledge Graph
            'twitter_handle'           => 'nullable|string|max:100',
            'twitter_card_type'        => 'nullable|in:summary_large_image,summary',
            'facebook_app_id'          => 'nullable|string|max:100',
            'facebook_page_url'        => 'nullable|string|max:255',
            'social_linkedin'          => 'nullable|string|max:255',
            'social_youtube'           => 'nullable|string|max:255',
            'social_github'            => 'nullable|string|max:255',
            // Schema Structured Data
            'schema_organization_type' => 'nullable|string|max:100',
            'schema_article_type'      => 'nullable|string|max:100',
            'schema_searchbox_enabled' => 'nullable|in:0,1',
            'schema_breadcrumbs_enabled'=> 'nullable|in:0,1',
            // Sitemap Controls
            'sitemap_enabled'          => 'nullable|in:0,1',
            'sitemap_include_articles' => 'nullable|in:0,1',
            'sitemap_include_pages'    => 'nullable|in:0,1',
            'sitemap_include_categories'=> 'nullable|in:0,1',
            'sitemap_include_tags'     => 'nullable|in:0,1',
        ]);

        // General
        Setting::set('site_name', $validated['site_name'], 'general');
        Setting::set('site_tagline', $validated['site_tagline'] ?? '', 'general');

        // SEO
        Setting::set('site_description', $validated['site_description'] ?? '', 'seo');
        Setting::set('title_separator', $validated['title_separator'] ?? '—', 'seo');
        Setting::set('default_og_image', $validated['default_og_image'] ?? '', 'seo');
        Setting::set('meta_robots_default', $validated['meta_robots_default'] ?? 'index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1', 'seo');
        Setting::set('meta_keywords', $validated['meta_keywords'] ?? '', 'seo');
        Setting::set('canonical_force_https', $request->has('canonical_force_https') ? '1' : '0', 'seo');

        // Webmasters
        Setting::set('google_verification', $validated['google_verification'] ?? '', 'seo');
        Setting::set('bing_verification', $validated['bing_verification'] ?? '', 'seo');
        Setting::set('yandex_verification', $validated['yandex_verification'] ?? '', 'seo');
        Setting::set('pinterest_verification', $validated['pinterest_verification'] ?? '', 'seo');
        Setting::set('baidu_verification', $validated['baidu_verification'] ?? '', 'seo');

        // Social
        Setting::set('twitter_handle', $validated['twitter_handle'] ?? '', 'social');
        Setting::set('twitter_card_type', $validated['twitter_card_type'] ?? 'summary_large_image', 'social');
        Setting::set('facebook_app_id', $validated['facebook_app_id'] ?? '', 'social');
        Setting::set('facebook_page_url', $validated['facebook_page_url'] ?? '', 'social');
        Setting::set('social_linkedin', $validated['social_linkedin'] ?? '', 'social');
        Setting::set('social_youtube', $validated['social_youtube'] ?? '', 'social');
        Setting::set('social_github', $validated['social_github'] ?? '', 'social');

        // Schema
        Setting::set('schema_organization_type', $validated['schema_organization_type'] ?? 'NewsMediaOrganization', 'seo');
        Setting::set('schema_article_type', $validated['schema_article_type'] ?? 'NewsArticle', 'seo');
        Setting::set('schema_searchbox_enabled', $request->has('schema_searchbox_enabled') ? '1' : '0', 'seo');
        Setting::set('schema_breadcrumbs_enabled', $request->has('schema_breadcrumbs_enabled') ? '1' : '0', 'seo');

        // Sitemap
        Setting::set('sitemap_enabled', $request->has('sitemap_enabled') ? '1' : '0', 'seo');
        Setting::set('sitemap_include_articles', $request->has('sitemap_include_articles') ? '1' : '0', 'seo');
        Setting::set('sitemap_include_pages', $request->has('sitemap_include_pages') ? '1' : '0', 'seo');
        Setting::set('sitemap_include_categories', $request->has('sitemap_include_categories') ? '1' : '0', 'seo');
        Setting::set('sitemap_include_tags', $request->has('sitemap_include_tags') ? '1' : '0', 'seo');

        if (function_exists('activity') && auth()->check()) {
            activity()->causedBy(auth()->user())->log('Updated Global SEO and Structured Data settings.');
        }

        return back()->with('success', 'Global SEO parameters, Social Meta, and Schema.org settings successfully saved.');
    }

    public function updateRobots(Request $request)
    {
        $validated = $request->validate([
            'robots_txt' => 'required|string',
        ]);

        Setting::set('robots_txt', $validated['robots_txt'], 'seo');

        if (function_exists('activity') && auth()->check()) {
            activity()->causedBy(auth()->user())->log('Updated robots.txt directives.');
        }

        return back()->with('success', 'robots.txt directives updated.');
    }

    public function updateScripts(Request $request)
    {
        $validated = $request->validate([
            'header_scripts' => 'nullable|string',
            'body_scripts'   => 'nullable|string',
            'footer_scripts' => 'nullable|string',
        ]);

        Setting::set('header_scripts', $validated['header_scripts'] ?? '', 'scripts');
        Setting::set('body_scripts', $validated['body_scripts'] ?? '', 'scripts');
        Setting::set('footer_scripts', $validated['footer_scripts'] ?? '', 'scripts');

        if (function_exists('activity') && auth()->check()) {
            activity()->causedBy(auth()->user())->log('Updated header, body, and footer injection scripts.');
        }

        return back()->with('success', 'Custom Header, Body opening, and Footer scripts updated.');
    }

    public function pingSitemap()
    {
        $sitemapUrl = url('/sitemap.xml');
        $engines = [
            'Google' => 'https://www.google.com/ping?sitemap=' . urlencode($sitemapUrl),
            'Bing'   => 'https://www.bing.com/ping?sitemap=' . urlencode($sitemapUrl),
        ];

        $results = [];
        foreach ($engines as $name => $url) {
            try {
                $response = Http::timeout(4)->get($url);
                $status = $response->successful() ? 'Notified (200 OK)' : ('Status ' . $response->status());
            } catch (\Throwable $e) {
                $status = 'Ping attempted (Network: ' . Str::limit($e->getMessage(), 40) . ')';
            }
            $results[] = "{$name}: {$status}";
        }

        return back()->with('success', 'Sitemap ping dispatched to search engines: ' . implode(' | ', $results));
    }

    public function storeRedirect(Request $request)
    {
        $validated = $request->validate([
            'from_url' => 'required|string|max:255',
            'to_url'   => 'required|string|max:500',
            'type'     => 'required|in:301,302',
        ]);

        $from = '/' . ltrim(trim($validated['from_url']), '/');

        // Check duplicate
        $existing = Redirect::where('from_url', $from)->first();
        if ($existing) {
            $existing->update([
                'to_url' => trim($validated['to_url']),
                'type'   => (int) $validated['type'],
                'status' => true,
            ]);
            return back()->with('success', "Redirect rule for '{$from}' updated.");
        }

        Redirect::create([
            'from_url' => $from,
            'to_url'   => trim($validated['to_url']),
            'type'     => (int) $validated['type'],
            'status'   => true,
            'hit_count'=> 0,
        ]);

        return back()->with('success', 'Redirect rule added successfully.');
    }

    public function destroyRedirect(Redirect $redirect)
    {
        $redirect->delete();
        return back()->with('success', 'Redirect rule deleted.');
    }

    public function exportRedirects(): StreamedResponse
    {
        $redirects = Redirect::all();

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="seo_redirects_' . date('Y-m-d_His') . '.csv"',
        ];

        return response()->stream(function () use ($redirects) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'From URL', 'To URL', 'Status Code', 'Status', 'Hits', 'Created At']);

            foreach ($redirects as $r) {
                fputcsv($handle, [
                    $r->id,
                    $r->from_url,
                    $r->to_url,
                    $r->type,
                    $r->status ? 'Active' : 'Disabled',
                    $r->hit_count,
                    $r->created_at ? $r->created_at->toDateTimeString() : '',
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}
