<?php

namespace App\Services;

use App\Models\Article;
use App\Models\Category;
use App\Models\Page;
use App\Models\Setting;
use App\Models\Tag;
use Illuminate\Support\Carbon;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class SeoService
{
    /**
     * Generate complete SEO metadata and structured data tags for the current view.
     */
    public static function renderMeta($model = null): HtmlString
    {
        $siteName = Setting::get('site_name', 'SecuroFi.Tech');
        $siteTagline = Setting::get('site_tagline', 'Smart Insights on AI, Hosting, Cybersecurity & Finance');
        $siteDesc = Setting::get('site_description', 'In-depth, objective analysis on AI tools, web hosting, cybersecurity, and personal finance.');
        $defaultOg = Setting::get('default_og_image', url('/images/default-og.jpg'));
        $separator = Setting::get('title_separator', '—');
        $defaultRobots = Setting::get('meta_robots_default', 'index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1');

        $title = $siteName . ' ' . $separator . ' ' . $siteTagline;
        $description = $siteDesc;
        $ogImage = $defaultOg;
        $canonical = url()->current();
        $robots = $defaultRobots;
        $type = 'website';
        $schemas = [];

        if ($model instanceof Article) {
            $seo = $model->seoMeta;
            $title = ($seo && $seo->meta_title) ? $seo->meta_title : ($model->title . ' ' . $separator . ' ' . $siteName);
            $description = ($seo && $seo->meta_description) ? $seo->meta_description : ($model->excerpt ?: Str::limit(strip_tags($model->content), 160));
            $ogImage = ($seo && $seo->og_image) ? $seo->og_image : ($model->featured_image ?: $defaultOg);
            $canonical = ($seo && $seo->canonical_url) ? $seo->canonical_url : url('/article/' . $model->slug);
            $robots = ($seo && $seo->robots) ? $seo->robots : $defaultRobots;
            $type = 'article';

            $pubDate = ($model->published_at instanceof Carbon)
                ? $model->published_at
                : ($model->published_at ? Carbon::parse($model->published_at) : ($model->created_at ? Carbon::parse($model->created_at) : now()));

            $updDate = ($model->updated_at instanceof Carbon)
                ? $model->updated_at
                : ($model->updated_at ? Carbon::parse($model->updated_at) : now());

            $articleSchema = [
                '@context' => 'https://schema.org',
                '@type' => Setting::get('schema_article_type', 'NewsArticle'),
                'headline' => $model->title,
                'description' => $description,
                'image' => $ogImage ? [$ogImage] : [],
                'datePublished' => $pubDate->toIso8601String(),
                'dateModified' => $updDate->toIso8601String(),
                'author' => [
                    '@type' => 'Person',
                    'name' => $model->author ? $model->author->name : 'SecuroFi Editorial Team',
                ],
                'publisher' => [
                    '@type' => Setting::get('schema_organization_type', 'NewsMediaOrganization'),
                    'name' => $siteName,
                    'logo' => [
                        '@type' => 'ImageObject',
                        'url' => Setting::get('site_logo_light', url('/logo.png')),
                    ],
                ],
                'mainEntityOfPage' => [
                    '@type' => 'WebPage',
                    '@id' => $canonical,
                ],
            ];

            if ($model->category) {
                $articleSchema['articleSection'] = $model->category->name;
            }

            $schemas[] = $articleSchema;

            // BreadcrumbList Schema
            if (Setting::get('schema_breadcrumbs_enabled', '1') === '1') {
                $breadcrumbItems = [
                    [
                        '@type' => 'ListItem',
                        'position' => 1,
                        'name' => 'Home',
                        'item' => url('/'),
                    ],
                ];

                $pos = 2;
                if ($model->category) {
                    $breadcrumbItems[] = [
                        '@type' => 'ListItem',
                        'position' => $pos++,
                        'name' => $model->category->name,
                        'item' => route('category.show', $model->category->slug),
                    ];
                }

                $breadcrumbItems[] = [
                    '@type' => 'ListItem',
                    'position' => $pos,
                    'name' => $model->title,
                    'item' => $canonical,
                ];

                $schemas[] = [
                    '@context' => 'https://schema.org',
                    '@type' => 'BreadcrumbList',
                    'itemListElement' => $breadcrumbItems,
                ];
            }
        } elseif ($model instanceof Page) {
            $seo = $model->seoMeta;
            $title = ($seo && $seo->meta_title) ? $seo->meta_title : ($model->title . ' ' . $separator . ' ' . $siteName);
            $description = ($seo && $seo->meta_description) ? $seo->meta_description : ($model->meta_description ?: $siteDesc);
            $ogImage = ($seo && $seo->og_image) ? $seo->og_image : ($model->og_image ?: $defaultOg);
            $canonical = ($seo && $seo->canonical_url) ? $seo->canonical_url : url('/page/' . $model->slug);
            $robots = ($seo && $seo->robots) ? $seo->robots : $defaultRobots;
        } elseif ($model instanceof Category) {
            $title = $model->name . ' ' . $separator . ' ' . $siteName;
            $description = $model->description ?: ('Latest cybersecurity, fintech & tech guides in ' . $model->name . ' on ' . $siteName);
            $canonical = route('category.show', $model->slug);
            $robots = $defaultRobots;
        } elseif ($model instanceof Tag) {
            $title = '#' . $model->name . ' ' . $separator . ' ' . $siteName;
            $description = 'Browse all articles, intelligence, and guides tagged with ' . $model->name . ' on ' . $siteName;
            $canonical = route('tag.show', $model->slug);
            $robots = $defaultRobots;
        } else {
            // Homepage / General Global Structured Data
            $sameAs = array_filter([
                Setting::get('twitter_handle') ? ('https://x.com/' . ltrim(Setting::get('twitter_handle'), '@')) : null,
                Setting::get('facebook_page_url'),
                Setting::get('social_linkedin'),
                Setting::get('social_youtube'),
                Setting::get('social_github'),
            ]);

            $orgSchema = [
                '@context' => 'https://schema.org',
                '@type' => Setting::get('schema_organization_type', 'NewsMediaOrganization'),
                'name' => $siteName,
                'url' => url('/'),
                'logo' => Setting::get('site_logo_light', url('/logo.png')),
                'description' => $siteDesc,
            ];

            if (!empty($sameAs)) {
                $orgSchema['sameAs'] = array_values($sameAs);
            }

            $schemas[] = $orgSchema;

            // Sitelinks SearchBox Schema
            if (Setting::get('schema_searchbox_enabled', '1') === '1') {
                $schemas[] = [
                    '@context' => 'https://schema.org',
                    '@type' => 'WebSite',
                    'name' => $siteName,
                    'url' => url('/'),
                    'potentialAction' => [
                        '@type' => 'SearchAction',
                        'target' => [
                            '@type' => 'EntryPoint',
                            'urlTemplate' => url('/search') . '?q={search_term_string}',
                        ],
                        'query-input' => 'required name=search_term_string',
                    ],
                ];
            }
        }

        // Canonical URL adjustments
        if (Setting::get('canonical_force_https', '1') === '1' && str_starts_with($canonical, 'http://')) {
            $canonical = 'https://' . substr($canonical, 7);
        }

        // Favicon & App Icons
        $favicon = Setting::get('site_favicon');
        if ($favicon) {
            $ext = strtolower(pathinfo(parse_url($favicon, PHP_URL_PATH), PATHINFO_EXTENSION));
            $mimeMap = ['ico' => 'image/x-icon', 'png' => 'image/png', 'svg' => 'image/svg+xml', 'gif' => 'image/gif'];
            $mime    = $mimeMap[$ext] ?? 'image/png';
            $html  = '<link rel="icon" type="'.$mime.'" href="'.e($favicon).'" />' . "\n";
            $html .= '<link rel="shortcut icon" href="'.e($favicon).'" />' . "\n";
            $html .= '<link rel="apple-touch-icon" href="'.e($favicon).'" />' . "\n";
        } else {
            $html  = '';
        }

        $html .= '<title>'.e($title).'</title>' . "\n";
        $html .= '<meta name="description" content="'.e($description).'" />' . "\n";

        $metaKeywords = Setting::get('meta_keywords');
        if ($metaKeywords) {
            $html .= '<meta name="keywords" content="'.e($metaKeywords).'" />' . "\n";
        }

        $html .= '<meta name="robots" content="'.e($robots).'" />' . "\n";
        $html .= '<link rel="canonical" href="'.e($canonical).'" />' . "\n";

        // Multi-region / Country-Specific Variants (hreflang tags - PRD-ADDNEW 5.1)
        if ($model instanceof Article && ($model->master_article_id || $model->variants()->exists())) {
            $allVariants = $model->allVariants();
            if ($allVariants->count() > 1) {
                foreach ($allVariants as $var) {
                    $countryCode = strtoupper(trim((string)$var->variant_country));
                    $langTag = $countryCode ? 'en-' . $countryCode : 'x-default';
                    $varUrl = url('/article/' . $var->slug);
                    $html .= '<link rel="alternate" hreflang="'.e($langTag).'" href="'.e($varUrl).'" />' . "\n";
                }
            }
        }

        // Search Console & Webmaster Verification
        $googleVerification = Setting::get('google_verification');
        if ($googleVerification) {
            $html .= '<meta name="google-site-verification" content="'.e($googleVerification).'" />' . "\n";
        }
        $bingVerification = Setting::get('bing_verification');
        if ($bingVerification) {
            $html .= '<meta name="msvalidate.01" content="'.e($bingVerification).'" />' . "\n";
        }
        $yandexVerification = Setting::get('yandex_verification');
        if ($yandexVerification) {
            $html .= '<meta name="yandex-verification" content="'.e($yandexVerification).'" />' . "\n";
        }
        $pinterestVerification = Setting::get('pinterest_verification');
        if ($pinterestVerification) {
            $html .= '<meta name="p:domain_verify" content="'.e($pinterestVerification).'" />' . "\n";
        }
        $baiduVerification = Setting::get('baidu_verification');
        if ($baiduVerification) {
            $html .= '<meta name="baidu-site-verification" content="'.e($baiduVerification).'" />' . "\n";
        }

        // Open Graph
        $html .= '<meta property="og:site_name" content="'.e($siteName).'" />' . "\n";
        $html .= '<meta property="og:type" content="'.e($type).'" />' . "\n";
        $html .= '<meta property="og:title" content="'.e($title).'" />' . "\n";
        $html .= '<meta property="og:description" content="'.e($description).'" />' . "\n";
        $html .= '<meta property="og:url" content="'.e($canonical).'" />' . "\n";
        if ($ogImage) {
            $html .= '<meta property="og:image" content="'.e($ogImage).'" />' . "\n";
        }

        $fbAppId = Setting::get('facebook_app_id');
        if ($fbAppId) {
            $html .= '<meta property="fb:app_id" content="'.e($fbAppId).'" />' . "\n";
        }

        // Twitter Card
        $twitterCardType = Setting::get('twitter_card_type', 'summary_large_image');
        $html .= '<meta name="twitter:card" content="'.e($twitterCardType).'" />' . "\n";
        $html .= '<meta name="twitter:title" content="'.e($title).'" />' . "\n";
        $html .= '<meta name="twitter:description" content="'.e($description).'" />' . "\n";
        if ($ogImage) {
            $html .= '<meta name="twitter:image" content="'.e($ogImage).'" />' . "\n";
        }

        $twitterHandle = Setting::get('twitter_handle');
        if ($twitterHandle) {
            $cleanHandle = '@' . ltrim(trim($twitterHandle), '@');
            $html .= '<meta name="twitter:site" content="'.e($cleanHandle).'" />' . "\n";
            $html .= '<meta name="twitter:creator" content="'.e($cleanHandle).'" />' . "\n";
        }

        // JSON-LD Structured Data
        if (!empty($schemas)) {
            foreach ($schemas as $schema) {
                $html .= '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . '</script>' . "\n";
            }
        }

        // Header custom scripts from Admin
        $headerScripts = Setting::get('header_scripts');
        if ($headerScripts) {
            $html .= $headerScripts . "\n";
        }

        return new HtmlString($html);
    }

    /**
     * Render opening body scripts configured from Admin Settings (e.g. GTM noscript).
     */
    public static function renderBodyScripts(): HtmlString
    {
        $bodyScripts = Setting::get('body_scripts', '');
        return new HtmlString($bodyScripts);
    }

    /**
     * Render footer scripts configured from Admin Settings.
     */
    public static function renderFooterScripts(): HtmlString
    {
        $footerScripts = Setting::get('footer_scripts', '');
        return new HtmlString($footerScripts);
    }
}
