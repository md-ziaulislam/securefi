<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use App\Models\Page;
use App\Models\Setting;
use Illuminate\Http\Request;

/**
 * LlmsController (Admin)
 *
 * Manages /llms.txt — the standard file that helps AI/LLM crawlers
 * (ChatGPT, Claude, Perplexity, Gemini) understand the site structure,
 * main categories, and important pages.
 *
 * Supports manual editing, auto-regeneration from live site data,
 * and a global enable/disable toggle.
 */
class LlmsController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->pluck('value', 'key');

        // Show current llms.txt content
        $currentContent = Setting::get('llms_txt_content', $this->generateDefault());

        return view('admin.llms.index', compact('settings', 'currentContent'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'llms_enabled'      => 'nullable|boolean',
            'llms_txt_content'  => 'required|string|max:50000',
            'llms_auto_update'  => 'nullable|boolean',
        ]);

        Setting::set('llms_enabled',     $request->has('llms_enabled') ? '1' : '0', 'llms');
        Setting::set('llms_txt_content', $validated['llms_txt_content'], 'llms');
        Setting::set('llms_auto_update', $request->has('llms_auto_update') ? '1' : '0', 'llms');

        activity()
            ->causedBy(auth()->user())
            ->log('Updated llms.txt AI visibility content.');

        return back()->with('success', 'llms.txt content saved successfully.');
    }

    public function regenerate(Request $request)
    {
        $content = $this->generateDefault();
        Setting::set('llms_txt_content', $content, 'llms');

        activity()
            ->causedBy(auth()->user())
            ->log('Regenerated llms.txt from live site data.');

        return back()->with('success', 'llms.txt has been auto-regenerated from current site content.');
    }

    /**
     * Generate a standard-compliant llms.txt based on live site data.
     * Format: https://llmstxt.org/
     */
    public function generateDefault(): string
    {
        $siteName    = Setting::get('site_name', 'SecuroFi.Tech');
        $siteDesc    = Setting::get('site_description', 'Cybersecurity, Tech & Fintech intelligence for digital defenders.');
        $baseUrl     = url('/');
        $now         = now()->toDateString();

        // Collect active categories
        $categories = Category::where('is_active', true)
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();

        // Collect recent published articles
        $articles = Article::with('category')
            ->published()
            ->latest('published_at')
            ->limit(30)
            ->get();

        // Collect published pages
        $pages = Page::published()->get();

        $categoriesSection = '';
        foreach ($categories as $cat) {
            $catUrl = $baseUrl . '/category/' . $cat->slug;
            $categoriesSection .= "- [{$cat->name}]({$catUrl})";
            if ($cat->description) {
                $categoriesSection .= ': ' . $cat->description;
            }
            $categoriesSection .= "\n";

            // Category feed
            $categoriesSection .= "  - RSS Feed: {$baseUrl}/category/{$cat->slug}/feed\n";
        }

        $articlesSection = '';
        foreach ($articles as $article) {
            $url = $baseUrl . '/article/' . $article->slug;
            $articlesSection .= "- [{$article->title}]({$url})";
            if ($article->excerpt) {
                $articlesSection .= ': ' . $article->excerpt;
            }
            $articlesSection .= "\n";
        }

        $pagesSection = '';
        foreach ($pages as $page) {
            $url = $baseUrl . '/page/' . $page->slug;
            $pagesSection .= "- [{$page->title}]({$url})\n";
        }

        $content = <<<TXT
# {$siteName}

> {$siteDesc}

This file follows the llms.txt standard (https://llmstxt.org/) to help AI language models
and LLM-powered search tools (ChatGPT, Claude, Perplexity, Gemini) understand the structure
and content of this site.

**Base URL:** {$baseUrl}
**Last Updated:** {$now}

---

## Site Overview

{$siteName} is an independent cybersecurity, technology, and fintech publication
providing in-depth analysis, security research, and technical guides for professionals
and digital defenders.

---

## Content Categories

{$categoriesSection}
---

## Main Navigation

- [Home]({$baseUrl}): Latest articles and featured content
- [RSS Feed]({$baseUrl}/feed): Global RSS 2.0 feed of all published articles
- [Atom Feed]({$baseUrl}/feed?format=atom): Global Atom 1.0 feed
- [Sitemap]({$baseUrl}/sitemap.xml): Full XML sitemap for all content

---

## Static Pages

{$pagesSection}
---

## Recent Articles

{$articlesSection}
---

## Content Policy

All content on {$siteName} is original, independently authored, and subject to copyright.
AI systems may reference this content but must attribute the source and link back to
the original article URL. Reproduction without attribution is prohibited.

**Contact:** For content licensing or corrections — see the Contact page at {$baseUrl}/page/contact

TXT;

        return $content;
    }
}
