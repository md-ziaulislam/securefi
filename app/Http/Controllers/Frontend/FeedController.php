<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * FeedController
 *
 * Generates RSS 2.0 and Atom feeds for all published articles,
 * and per-category feeds. Admin-configurable via Settings panel.
 */
class FeedController extends Controller
{
    /**
     * Global RSS/Atom feed — all published articles.
     */
    public function index(Request $request)
    {
        if (Setting::get('feed_enabled', '1') !== '1') {
            abort(404, 'Feed is disabled by the administrator.');
        }

        $format = $request->query('format', 'rss'); // 'rss' or 'atom'
        $limit  = (int) Setting::get('feed_items_count', '20');

        $articles = Article::with(['category', 'author', 'seoMeta'])
            ->published()
            ->latest('published_at')
            ->limit($limit)
            ->get();

        $feedMeta = $this->globalFeedMeta();

        return $format === 'atom'
            ? $this->atomResponse($articles, $feedMeta)
            : $this->rssResponse($articles, $feedMeta);
    }

    /**
     * Per-category RSS/Atom feed.
     */
    public function category(Request $request, string $slug)
    {
        if (Setting::get('feed_enabled', '1') !== '1') {
            abort(404, 'Feed is disabled by the administrator.');
        }

        $category = Category::where('slug', $slug)->where('is_active', true)->firstOrFail();
        $format   = $request->query('format', 'rss');
        $limit    = (int) Setting::get('feed_items_count', '20');

        $articles = Article::with(['category', 'author', 'seoMeta'])
            ->published()
            ->where('category_id', $category->id)
            ->latest('published_at')
            ->limit($limit)
            ->get();

        $feedMeta = [
            'title'       => Setting::get('site_name', 'SecuroFi.Tech') . ' — ' . $category->name,
            'description' => $category->description ?: 'Latest articles in ' . $category->name . ' from ' . Setting::get('site_name', 'SecuroFi.Tech'),
            'link'        => route('category.show', $category->slug),
            'feed_url'    => route('feed.category', $category->slug),
            'language'    => 'en-us',
        ];

        return $format === 'atom'
            ? $this->atomResponse($articles, $feedMeta)
            : $this->rssResponse($articles, $feedMeta);
    }

    // ─── Private Helpers ──────────────────────────────────────────────────────

    private function globalFeedMeta(): array
    {
        $siteName = Setting::get('feed_title', Setting::get('site_name', 'SecuroFi.Tech'));
        $siteDesc = Setting::get('feed_description', Setting::get('site_description', 'Cybersecurity, Tech & Fintech intelligence for digital defenders.'));
        $language = Setting::get('feed_language', 'en-us');

        return [
            'title'       => $siteName,
            'description' => $siteDesc,
            'link'        => url('/'),
            'feed_url'    => route('feed.index'),
            'language'    => $language,
        ];
    }

    /**
     * Build RSS 2.0 XML response.
     */
    private function rssResponse($articles, array $meta)
    {
        $firstArt = $articles->first();
        $firstDate = ($firstArt && $firstArt->published_at) ? Carbon::parse($firstArt->published_at) : now();
        $lastBuildDate = $firstDate->toRfc2822String();

        $siteName    = htmlspecialchars($meta['title'], ENT_XML1, 'UTF-8');
        $description = htmlspecialchars($meta['description'], ENT_XML1, 'UTF-8');
        $link        = htmlspecialchars($meta['link'], ENT_XML1, 'UTF-8');
        $feedUrl     = htmlspecialchars($meta['feed_url'], ENT_XML1, 'UTF-8');
        $language    = htmlspecialchars($meta['language'], ENT_XML1, 'UTF-8');
        $copyright   = htmlspecialchars(Setting::get('feed_copyright', '© ' . date('Y') . ' ' . $meta['title'] . '. All Rights Reserved.'), ENT_XML1, 'UTF-8');

        $showExcerpt  = Setting::get('feed_show_excerpt', '1') === '1';
        $showFulltext = Setting::get('feed_show_fulltext', '0') === '1';

        $items = '';
        foreach ($articles as $article) {
            $articleUrl   = htmlspecialchars(route('article.show', $article->slug), ENT_XML1, 'UTF-8');
            $articleTitle = htmlspecialchars($article->title, ENT_XML1, 'UTF-8');
            $author       = htmlspecialchars($article->author->name ?? 'SecuroFi Editorial Team', ENT_XML1, 'UTF-8');
            $category     = htmlspecialchars($article->category->name ?? 'General', ENT_XML1, 'UTF-8');
            $guid         = $articleUrl;

            $dateObj = $article->published_at ? Carbon::parse($article->published_at) : ($article->created_at ? Carbon::parse($article->created_at) : now());
            $pubDate = $dateObj->toRfc2822String();

            $descriptionTag = '';
            if ($showExcerpt) {
                $excerptText = $article->excerpt ?: Str::limit(strip_tags($article->content), 300);
                $descriptionTag = "<description><![CDATA[{$excerptText}]]></description>";
            }

            $contentTag = '';
            if ($showFulltext && !empty($article->content)) {
                $contentTag = "<content:encoded><![CDATA[{$article->content}]]></content:encoded>";
            }

            $imageTag = '';
            if (!empty($article->featured_image)) {
                $imgUrl = filter_var($article->featured_image, FILTER_VALIDATE_URL)
                    ? $article->featured_image
                    : url($article->featured_image);
                $cleanImg = htmlspecialchars($imgUrl, ENT_XML1, 'UTF-8');
                $imageTag = "<enclosure url=\"{$cleanImg}\" type=\"image/jpeg\" length=\"0\" />";
            }

            $items .= <<<XML

        <item>
            <title><![CDATA[{$article->title}]]></title>
            <link>{$articleUrl}</link>
            {$descriptionTag}
            {$contentTag}
            <pubDate>{$pubDate}</pubDate>
            <dc:creator><![CDATA[{$author}]]></dc:creator>
            <category><![CDATA[{$category}]]></category>
            <guid isPermaLink="true">{$guid}</guid>
            {$imageTag}
        </item>
XML;
        }

        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0"
     xmlns:atom="http://www.w3.org/2005/Atom"
     xmlns:content="http://purl.org/rss/1.0/modules/content/"
     xmlns:dc="http://purl.org/dc/elements/1.1/"
     xmlns:media="http://search.yahoo.com/mrss/">
    <channel>
        <title>{$siteName}</title>
        <link>{$link}</link>
        <description>{$description}</description>
        <language>{$language}</language>
        <copyright>{$copyright}</copyright>
        <lastBuildDate>{$lastBuildDate}</lastBuildDate>
        <atom:link href="{$feedUrl}" rel="self" type="application/rss+xml" />
        <generator>SecuroFi.Tech RSS Engine v2.0</generator>
        {$items}
    </channel>
</rss>
XML;

        return response($xml, 200, [
            'Content-Type'  => 'application/rss+xml; charset=UTF-8',
            'X-Robots-Tag'  => 'noindex',
            'Cache-Control' => 'public, max-age=1800',
        ]);
    }

    /**
     * Build Atom 1.0 XML response.
     */
    private function atomResponse($articles, array $meta)
    {
        $firstArt = $articles->first();
        $firstDate = ($firstArt && $firstArt->published_at) ? Carbon::parse($firstArt->published_at) : now();
        $updated  = $firstDate->toAtomString();

        $siteName = htmlspecialchars($meta['title'], ENT_XML1, 'UTF-8');
        $desc     = htmlspecialchars($meta['description'], ENT_XML1, 'UTF-8');
        $link     = htmlspecialchars($meta['link'], ENT_XML1, 'UTF-8');
        $feedUrl  = htmlspecialchars($meta['feed_url'], ENT_XML1, 'UTF-8');

        $showExcerpt  = Setting::get('feed_show_excerpt', '1') === '1';
        $showFulltext = Setting::get('feed_show_fulltext', '0') === '1';

        $entries = '';
        foreach ($articles as $article) {
            $articleUrl = htmlspecialchars(route('article.show', $article->slug), ENT_XML1, 'UTF-8');
            $author     = htmlspecialchars($article->author->name ?? 'SecuroFi Editorial Team', ENT_XML1, 'UTF-8');
            $category   = htmlspecialchars($article->category->name ?? 'General', ENT_XML1, 'UTF-8');

            $updDate   = $article->updated_at ? Carbon::parse($article->updated_at) : now();
            $pubDate   = $article->published_at ? Carbon::parse($article->published_at) : ($article->created_at ? Carbon::parse($article->created_at) : now());
            $updated_at = $updDate->toAtomString();
            $published  = $pubDate->toAtomString();

            $summaryTag = '';
            if ($showExcerpt) {
                $excerptText = $article->excerpt ?: Str::limit(strip_tags($article->content), 300);
                $summaryTag = "<summary type=\"html\"><![CDATA[{$excerptText}]]></summary>";
            }

            $contentTag = '';
            if ($showFulltext && !empty($article->content)) {
                $contentTag = "<content type=\"html\"><![CDATA[{$article->content}]]></content>";
            }

            $entries .= <<<XML

    <entry>
        <title><![CDATA[{$article->title}]]></title>
        <link href="{$articleUrl}" rel="alternate" type="text/html" />
        <id>{$articleUrl}</id>
        <updated>{$updated_at}</updated>
        <published>{$published}</published>
        <author><name>{$author}</name></author>
        <category term="{$category}" />
        {$summaryTag}
        {$contentTag}
    </entry>
XML;
        }

        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<feed xmlns="http://www.w3.org/2005/Atom">
    <title>{$siteName}</title>
    <subtitle>{$desc}</subtitle>
    <link href="{$link}" rel="alternate" />
    <link href="{$feedUrl}" rel="self" />
    <updated>{$updated}</updated>
    <id>{$link}/</id>
    <generator uri="https://securofi.tech">SecuroFi.Tech Feed Engine</generator>
    {$entries}
</feed>
XML;

        return response($xml, 200, [
            'Content-Type'  => 'application/atom+xml; charset=UTF-8',
            'X-Robots-Tag'  => 'noindex',
            'Cache-Control' => 'public, max-age=1800',
        ]);
    }
}
