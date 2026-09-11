@extends('layouts.admin')

@section('title', 'SEO Suite & Traffic Routing')
@section('header_title', 'Advanced SEO Suite & Intelligence Console')

@section('content')
<div class="space-y-6 sm:space-y-8" x-data="{
    activeTab: 'global',
    serpMode: 'desktop',
    siteName: '{{ addslashes($settings['site_name'] ?? 'SecuroFi.Tech') }}',
    tagline: '{{ addslashes($settings['site_tagline'] ?? 'Smart Insights on AI, Hosting, Cybersecurity & Finance') }}',
    separator: '{{ addslashes($settings['title_separator'] ?? '—') }}',
    description: '{{ addslashes($settings['site_description'] ?? 'In-depth, objective analysis on AI tools, web hosting, cybersecurity, and personal finance.') }}',
    ogImage: '{{ addslashes($settings['default_og_image'] ?? '') }}',
    redirectSearch: ''
}">

    {{-- Top SEO Command Bar / Overview Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">

        {{-- SEO Health Score --}}
        <div class="bg-white border border-[#111111]/15 p-4 sm:p-5">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[10px] font-mono text-[#808080] uppercase tracking-wider">Site SEO Score</span>
                <span class="px-2 py-0.5 text-[9px] font-mono font-bold uppercase {{ $seoHealthScore >= 80 ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : ($seoHealthScore >= 60 ? 'bg-amber-100 text-amber-800 border border-amber-300' : 'bg-rose-100 text-rose-800 border border-rose-300') }}">
                    {{ $seoHealthScore >= 80 ? 'Optimal' : ($seoHealthScore >= 60 ? 'Moderate' : 'Needs Action') }}
                </span>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-3xl font-bold font-mono text-[#111111]">{{ $seoHealthScore }}</span>
                <span class="text-xs font-mono text-[#808080]">/ 100</span>
            </div>
            <div class="w-full bg-[#F8F8F6] h-1.5 mt-2.5 overflow-hidden border border-[#111111]/10">
                <div class="h-full {{ $seoHealthScore >= 80 ? 'bg-emerald-500' : ($seoHealthScore >= 60 ? 'bg-amber-500' : 'bg-rose-500') }}" style="width: {{ $seoHealthScore }}%"></div>
            </div>
        </div>

        {{-- XML Sitemap Card with Ping Action --}}
        <div class="bg-white border border-[#111111]/15 p-4 sm:p-5">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[10px] font-mono text-[#808080] uppercase tracking-wider">XML Sitemap</span>
                <span class="px-2 py-0.5 text-[9px] font-mono font-bold uppercase {{ ($settings['sitemap_enabled'] ?? '1') === '1' ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-rose-100 text-rose-800 border border-rose-300' }}">
                    {{ ($settings['sitemap_enabled'] ?? '1') === '1' ? 'Active' : 'Disabled' }}
                </span>
            </div>
            <div class="text-sm font-bold font-mono text-[#111111] truncate mb-2">/sitemap.xml</div>
            <div class="flex items-center gap-2">
                <a href="{{ url('/sitemap.xml') }}" target="_blank" class="text-xs font-mono text-[#111111] hover:underline flex items-center gap-1">
                    <span>Inspect</span> →
                </a>
                <span class="text-[#111111]/20">|</span>
                <form action="{{ route('admin.seo.ping') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="text-xs font-mono text-emerald-700 hover:text-emerald-900 font-bold">
                        Ping Bots
                    </button>
                </form>
            </div>
        </div>

        {{-- Crawler Directives Card --}}
        <div class="bg-white border border-[#111111]/15 p-4 sm:p-5">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[10px] font-mono text-[#808080] uppercase tracking-wider">Crawler Directives</span>
                <span class="px-2 py-0.5 text-[9px] font-mono font-bold uppercase bg-emerald-100 text-emerald-800 border border-emerald-300">
                    Live
                </span>
            </div>
            <div class="text-sm font-bold font-mono text-[#111111] truncate mb-2">/robots.txt</div>
            <div class="flex items-center gap-2">
                <a href="{{ url('/robots.txt') }}" target="_blank" class="text-xs font-mono text-[#111111] hover:underline">
                    View Live File →
                </a>
                <span class="text-[#111111]/20">|</span>
                <button type="button" @click="activeTab = 'robots'" class="text-xs font-mono text-[#808080] hover:text-[#111111]">
                    Edit Directives
                </button>
            </div>
        </div>

        {{-- Active 301/302 Redirects --}}
        <div class="bg-white border border-[#111111]/15 p-4 sm:p-5">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[10px] font-mono text-[#808080] uppercase tracking-wider">Link Preservation</span>
                <span class="px-2 py-0.5 text-[9px] font-mono font-bold uppercase bg-purple-100 text-purple-800 border border-purple-200">
                    301 / 302
                </span>
            </div>
            <div class="text-2xl font-bold font-mono text-[#111111]">{{ number_format($totalRedirects) }} Rules</div>
            <div class="text-[11px] font-mono text-[#808080] mt-1">{{ number_format($totalRedirectHits) }} 404 Hits Saved</div>
        </div>

    </div>

    {{-- Horizontal Navigation Tabs --}}
    <div class="bg-white border border-[#111111]/15 p-1.5 overflow-x-auto">
        <nav class="flex items-center gap-1 min-w-max text-xs font-mono">
            <button type="button" @click="activeTab = 'global'"
                :class="activeTab === 'global' ? 'bg-[#111111] text-white font-bold' : 'text-[#111111] hover:bg-[#F5F1E8]'"
                class="px-3.5 py-2 transition-colors">
                1. Global Meta & SERP
            </button>

            <button type="button" @click="activeTab = 'social'"
                :class="activeTab === 'social' ? 'bg-[#111111] text-white font-bold' : 'text-[#111111] hover:bg-[#F5F1E8]'"
                class="px-3.5 py-2 transition-colors">
                2. Social & OpenGraph
            </button>

            <button type="button" @click="activeTab = 'webmasters'"
                :class="activeTab === 'webmasters' ? 'bg-[#111111] text-white font-bold' : 'text-[#111111] hover:bg-[#F5F1E8]'"
                class="px-3.5 py-2 transition-colors">
                3. Webmaster Verifications
            </button>

            <button type="button" @click="activeTab = 'sitemap'"
                :class="activeTab === 'sitemap' ? 'bg-[#111111] text-white font-bold' : 'text-[#111111] hover:bg-[#F5F1E8]'"
                class="px-3.5 py-2 transition-colors">
                4. XML Sitemap & Ping
            </button>

            <button type="button" @click="activeTab = 'robots'"
                :class="activeTab === 'robots' ? 'bg-[#111111] text-white font-bold' : 'text-[#111111] hover:bg-[#F5F1E8]'"
                class="px-3.5 py-2 transition-colors">
                5. robots.txt & AI Crawlers
            </button>

            <button type="button" @click="activeTab = 'schema'"
                :class="activeTab === 'schema' ? 'bg-[#111111] text-white font-bold' : 'text-[#111111] hover:bg-[#F5F1E8]'"
                class="px-3.5 py-2 transition-colors">
                6. Structured Data / Schema
            </button>

            <button type="button" @click="activeTab = 'redirects'"
                :class="activeTab === 'redirects' ? 'bg-[#111111] text-white font-bold' : 'text-[#111111] hover:bg-[#F5F1E8]'"
                class="px-3.5 py-2 transition-colors">
                7. 301/302 Redirect Manager
            </button>

            <button type="button" @click="activeTab = 'scripts'"
                :class="activeTab === 'scripts' ? 'bg-[#111111] text-white font-bold' : 'text-[#111111] hover:bg-[#F5F1E8]'"
                class="px-3.5 py-2 transition-colors">
                8. Script Injections
            </button>

            <button type="button" @click="activeTab = 'audit'"
                :class="activeTab === 'audit' ? 'bg-[#111111] text-white font-bold' : 'text-[#111111] hover:bg-[#F5F1E8]'"
                class="px-3.5 py-2 transition-colors">
                9. Content SEO Audit
            </button>
        </nav>
    </div>

    {{-- ========================================================================= --}}
    {{-- TAB 1: Global Meta & Live SERP Simulator --}}
    {{-- ========================================================================= --}}
    <div x-show="activeTab === 'global'" class="space-y-6">
        <form action="{{ route('admin.seo.global') }}" method="POST">
            @csrf
            {{-- Pass hidden values for other sections so submitting this form preserves them --}}
            <input type="hidden" name="sitemap_enabled" value="{{ $settings['sitemap_enabled'] ?? '1' }}">
            <input type="hidden" name="sitemap_include_articles" value="{{ $settings['sitemap_include_articles'] ?? '1' }}">
            <input type="hidden" name="sitemap_include_pages" value="{{ $settings['sitemap_include_pages'] ?? '1' }}">
            <input type="hidden" name="sitemap_include_categories" value="{{ $settings['sitemap_include_categories'] ?? '1' }}">
            <input type="hidden" name="sitemap_include_tags" value="{{ $settings['sitemap_include_tags'] ?? '0' }}">
            <input type="hidden" name="google_verification" value="{{ $settings['google_verification'] ?? '' }}">
            <input type="hidden" name="bing_verification" value="{{ $settings['bing_verification'] ?? '' }}">
            <input type="hidden" name="yandex_verification" value="{{ $settings['yandex_verification'] ?? '' }}">
            <input type="hidden" name="pinterest_verification" value="{{ $settings['pinterest_verification'] ?? '' }}">
            <input type="hidden" name="baidu_verification" value="{{ $settings['baidu_verification'] ?? '' }}">
            <input type="hidden" name="twitter_handle" value="{{ $settings['twitter_handle'] ?? '' }}">
            <input type="hidden" name="twitter_card_type" value="{{ $settings['twitter_card_type'] ?? 'summary_large_image' }}">
            <input type="hidden" name="facebook_app_id" value="{{ $settings['facebook_app_id'] ?? '' }}">
            <input type="hidden" name="facebook_page_url" value="{{ $settings['facebook_page_url'] ?? '' }}">
            <input type="hidden" name="social_linkedin" value="{{ $settings['social_linkedin'] ?? '' }}">
            <input type="hidden" name="social_youtube" value="{{ $settings['social_youtube'] ?? '' }}">
            <input type="hidden" name="social_github" value="{{ $settings['social_github'] ?? '' }}">
            <input type="hidden" name="schema_organization_type" value="{{ $settings['schema_organization_type'] ?? 'NewsMediaOrganization' }}">
            <input type="hidden" name="schema_article_type" value="{{ $settings['schema_article_type'] ?? 'NewsArticle' }}">
            <input type="hidden" name="schema_searchbox_enabled" value="{{ $settings['schema_searchbox_enabled'] ?? '1' }}">
            <input type="hidden" name="schema_breadcrumbs_enabled" value="{{ $settings['schema_breadcrumbs_enabled'] ?? '1' }}">

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 sm:gap-8 items-start">

                {{-- Left: Inputs (7 cols) --}}
                <div class="lg:col-span-7 space-y-6">
                    <div class="bg-white border border-[#111111]/15 p-5 sm:p-6 space-y-5">
                        <div class="border-b border-[#111111]/10 pb-3">
                            <h3 class="font-bold text-sm text-[#111111]">Global Title & Meta Parameters</h3>
                            <p class="text-xs font-mono text-[#808080]">Universal brand titles, separator, and default search snippet.</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="sm:col-span-2">
                                <label for="site_name" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1 font-medium">Site Name *</label>
                                <input type="text" id="site_name" name="site_name" x-model="siteName" required
                                    class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                            </div>

                            <div>
                                <label for="title_separator" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1 font-medium">Separator</label>
                                <select id="title_separator" name="title_separator" x-model="separator"
                                    class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                                    <option value="—">— (Em Dash)</option>
                                    <option value="|">| (Pipe)</option>
                                    <option value="-">- (Hyphen)</option>
                                    <option value="•">• (Bullet)</option>
                                    <option value="/">/ (Slash)</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label for="site_tagline" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1 font-medium">Site Tagline</label>
                            <input type="text" id="site_tagline" name="site_tagline" x-model="tagline"
                                class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label for="site_description" class="block text-xs font-mono uppercase tracking-wider text-[#111111] font-medium">Default Meta Description</label>
                                <span class="text-[10px] font-mono" :class="description.length > 160 ? 'text-amber-600 font-bold' : 'text-[#808080]'" x-text="description.length + ' / 160 chars'"></span>
                            </div>
                            <textarea id="site_description" name="site_description" rows="3" x-model="description"
                                class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111] leading-relaxed"></textarea>
                            <p class="text-[10px] font-mono text-[#808080] mt-1">Recommended: 140–160 characters for maximum search engine snippet visibility.</p>
                        </div>

                        <div>
                            <label for="default_og_image" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1 font-medium">Default Social Share Image (OG Image URL)</label>
                            <input type="text" id="default_og_image" name="default_og_image" x-model="ogImage"
                                placeholder="https://securofi.tech/images/default-og.jpg"
                                class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                            <p class="text-[10px] font-mono text-[#808080] mt-1">Optimal dimensions: 1200 × 630 px (1.91:1 ratio).</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-[#111111]/10">
                            <div>
                                <label for="meta_robots_default" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1 font-medium">Default Robots Indexing</label>
                                <input type="text" id="meta_robots_default" name="meta_robots_default"
                                    value="{{ old('meta_robots_default', $settings['meta_robots_default'] ?? 'index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1') }}"
                                    class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                                <p class="text-[10px] font-mono text-[#808080] mt-1">Include <code>max-image-preview:large</code> for Google Discover.</p>
                            </div>

                            <div>
                                <label for="meta_keywords" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1 font-medium">Global Meta Keywords</label>
                                <input type="text" id="meta_keywords" name="meta_keywords"
                                    value="{{ old('meta_keywords', $settings['meta_keywords'] ?? '') }}"
                                    placeholder="cybersecurity, tech analysis, fintech"
                                    class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                            </div>
                        </div>

                        <div class="pt-2">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="canonical_force_https" value="1"
                                    {{ ($settings['canonical_force_https'] ?? '1') === '1' ? 'checked' : '' }}
                                    class="w-4 h-4 rounded-none border-[#111111] text-[#111111]">
                                <span class="text-xs font-mono text-[#111111]">Force HTTPS in all generated Canonical URLs</span>
                            </label>
                        </div>

                        <div class="pt-4 border-t border-[#111111]/10">
                            <button type="submit" class="btn-primary w-full text-xs font-mono uppercase tracking-wider py-3">
                                Save Global SEO Parameters →
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Right: Live Interactive SERP & Social Preview (5 cols) --}}
                <div class="lg:col-span-5 space-y-6">

                    {{-- Google SERP Snippet Preview --}}
                    <div class="bg-white border border-[#111111]/15 p-5 sm:p-6 space-y-4">
                        <div class="flex items-center justify-between border-b border-[#111111]/10 pb-3">
                            <div>
                                <h3 class="font-bold text-sm text-[#111111]">Live Google SERP Simulator</h3>
                                <p class="text-[11px] font-mono text-[#808080]">Real-time preview of search engine snippet.</p>
                            </div>
                            <div class="flex items-center gap-1 border border-[#111111]/20 p-0.5 text-[10px] font-mono">
                                <button type="button" @click="serpMode = 'desktop'" :class="serpMode === 'desktop' ? 'bg-[#111111] text-white' : 'text-[#808080]'" class="px-2 py-0.5">Desktop</button>
                                <button type="button" @click="serpMode = 'mobile'" :class="serpMode === 'mobile' ? 'bg-[#111111] text-white' : 'text-[#808080]'" class="px-2 py-0.5">Mobile</button>
                            </div>
                        </div>

                        {{-- SERP Container --}}
                        <div class="bg-[#F8F8F6] p-4 border border-[#111111]/10 rounded-sm font-sans" :class="serpMode === 'mobile' ? 'max-w-[340px] mx-auto' : ''">
                            {{-- Breadcrumb URL --}}
                            <div class="flex items-center gap-1.5 text-xs text-[#202124] mb-1">
                                <div class="w-4 h-4 rounded-full bg-[#111111] text-white flex items-center justify-center text-[9px] font-bold">S</div>
                                <div class="text-[12px] truncate">
                                    <span class="font-medium text-[#202124]" x-text="siteName"></span>
                                    <span class="text-[#5f6368] text-[11px]"> › https://{{ parse_url(url('/'), PHP_URL_HOST) ?? 'securofi.tech' }}</span>
                                </div>
                            </div>

                            {{-- Title --}}
                            <h4 class="text-[#1a0dab] hover:underline text-base leading-snug cursor-pointer font-medium line-clamp-1 mb-1"
                                x-text="siteName + ' ' + separator + ' ' + tagline">
                            </h4>

                            {{-- Description --}}
                            <p class="text-[#4d5156] text-[13px] leading-relaxed line-clamp-2"
                               x-text="description ? description : 'Please enter a default meta description to preview how your site snippet appears in Google Search.'">
                            </p>
                        </div>
                    </div>

                    {{-- Social Share Preview (Twitter / OpenGraph Card) --}}
                    <div class="bg-white border border-[#111111]/15 p-5 sm:p-6 space-y-4">
                        <div class="border-b border-[#111111]/10 pb-3">
                            <h3 class="font-bold text-sm text-[#111111]">Social Card Preview (X / Facebook / LinkedIn)</h3>
                            <p class="text-[11px] font-mono text-[#808080]">How links appear when shared across social channels.</p>
                        </div>

                        <div class="border border-[#111111]/20 rounded-lg overflow-hidden bg-white max-w-sm mx-auto font-sans">
                            <div class="aspect-[1.91/1] bg-[#111111]/5 flex items-center justify-center relative overflow-hidden border-b border-[#111111]/10">
                                <template x-if="ogImage">
                                    <img :src="ogImage" alt="OG Preview" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!ogImage">
                                    <div class="text-center p-4">
                                        <svg class="w-8 h-8 mx-auto text-[#808080] mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        <span class="text-[10px] font-mono text-[#808080]">No OG Image Provided</span>
                                    </div>
                                </template>
                            </div>

                            <div class="p-3 space-y-1">
                                <span class="text-[10px] text-[#808080] font-mono uppercase tracking-wider block">{{ parse_url(url('/'), PHP_URL_HOST) ?? 'securofi.tech' }}</span>
                                <h5 class="text-xs font-bold text-[#111111] line-clamp-1" x-text="siteName + ' ' + separator + ' ' + tagline"></h5>
                                <p class="text-[11px] text-[#808080] line-clamp-2" x-text="description"></p>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </form>
    </div>

    {{-- ========================================================================= --}}
    {{-- TAB 2: Social & OpenGraph / Knowledge Graph --}}
    {{-- ========================================================================= --}}
    <div x-show="activeTab === 'social'" class="space-y-6" style="display: none;">
        <form action="{{ route('admin.seo.global') }}" method="POST">
            @csrf
            {{-- Inherit required core fields --}}
            <input type="hidden" name="site_name" value="{{ $settings['site_name'] ?? 'SecuroFi.Tech' }}">
            <input type="hidden" name="site_tagline" value="{{ $settings['site_tagline'] ?? '' }}">
            <input type="hidden" name="site_description" value="{{ $settings['site_description'] ?? '' }}">
            <input type="hidden" name="title_separator" value="{{ $settings['title_separator'] ?? '—' }}">
            <input type="hidden" name="default_og_image" value="{{ $settings['default_og_image'] ?? '' }}">
            <input type="hidden" name="meta_robots_default" value="{{ $settings['meta_robots_default'] ?? 'index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1' }}">
            <input type="hidden" name="sitemap_enabled" value="{{ $settings['sitemap_enabled'] ?? '1' }}">

            <div class="bg-white border border-[#111111]/15 p-5 sm:p-6 space-y-6 max-w-4xl">
                <div class="border-b border-[#111111]/10 pb-3">
                    <h3 class="font-bold text-sm text-[#111111]">Social Media Accounts & Knowledge Graph Profiles</h3>
                    <p class="text-xs font-mono text-[#808080]">Configures <code>twitter:site</code>, <code>fb:app_id</code>, and Schema <code>sameAs</code> entity links for Google Knowledge Panel.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label for="twitter_handle" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1 font-medium">
                            X / Twitter Handle
                        </label>
                        <input type="text" id="twitter_handle" name="twitter_handle"
                            value="{{ old('twitter_handle', $settings['twitter_handle'] ?? '') }}"
                            placeholder="@SecuroFi"
                            class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                        <p class="text-[10px] font-mono text-[#808080] mt-1">Generates <code>twitter:site</code> & <code>twitter:creator</code>.</p>
                    </div>

                    <div>
                        <label for="twitter_card_type" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1 font-medium">
                            Twitter Card Format
                        </label>
                        <select id="twitter_card_type" name="twitter_card_type"
                            class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                            <option value="summary_large_image" {{ ($settings['twitter_card_type'] ?? 'summary_large_image') === 'summary_large_image' ? 'selected' : '' }}>summary_large_image (Large Hero Card - Recommended)</option>
                            <option value="summary" {{ ($settings['twitter_card_type'] ?? '') === 'summary' ? 'selected' : '' }}>summary (Small Thumbnail)</option>
                        </select>
                    </div>

                    <div>
                        <label for="facebook_app_id" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1 font-medium">
                            Facebook App ID
                        </label>
                        <input type="text" id="facebook_app_id" name="facebook_app_id"
                            value="{{ old('facebook_app_id', $settings['facebook_app_id'] ?? '') }}"
                            placeholder="123456789012345"
                            class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                    </div>

                    <div>
                        <label for="facebook_page_url" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1 font-medium">
                            Facebook Page URL
                        </label>
                        <input type="url" id="facebook_page_url" name="facebook_page_url"
                            value="{{ old('facebook_page_url', $settings['facebook_page_url'] ?? '') }}"
                            placeholder="https://facebook.com/securofi"
                            class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                    </div>

                    <div>
                        <label for="social_linkedin" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1 font-medium">
                            LinkedIn Company Page
                        </label>
                        <input type="url" id="social_linkedin" name="social_linkedin"
                            value="{{ old('social_linkedin', $settings['social_linkedin'] ?? '') }}"
                            placeholder="https://linkedin.com/company/securofi"
                            class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                    </div>

                    <div>
                        <label for="social_youtube" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1 font-medium">
                            YouTube Channel URL
                        </label>
                        <input type="url" id="social_youtube" name="social_youtube"
                            value="{{ old('social_youtube', $settings['social_youtube'] ?? '') }}"
                            placeholder="https://youtube.com/@securofi"
                            class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                    </div>

                    <div class="sm:col-span-2">
                        <label for="social_github" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1 font-medium">
                            GitHub Organization URL
                        </label>
                        <input type="url" id="social_github" name="social_github"
                            value="{{ old('social_github', $settings['social_github'] ?? '') }}"
                            placeholder="https://github.com/securofi"
                            class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                    </div>
                </div>

                <div class="pt-4 border-t border-[#111111]/10">
                    <button type="submit" class="btn-primary text-xs font-mono py-2.5 px-6">
                        Save Social & Knowledge Graph Profiles →
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- ========================================================================= --}}
    {{-- TAB 3: Webmaster Tools & Verification --}}
    {{-- ========================================================================= --}}
    <div x-show="activeTab === 'webmasters'" class="space-y-6" style="display: none;">
        <form action="{{ route('admin.seo.global') }}" method="POST">
            @csrf
            {{-- Preserve core fields --}}
            <input type="hidden" name="site_name" value="{{ $settings['site_name'] ?? 'SecuroFi.Tech' }}">
            <input type="hidden" name="site_tagline" value="{{ $settings['site_tagline'] ?? '' }}">
            <input type="hidden" name="site_description" value="{{ $settings['site_description'] ?? '' }}">
            <input type="hidden" name="title_separator" value="{{ $settings['title_separator'] ?? '—' }}">
            <input type="hidden" name="default_og_image" value="{{ $settings['default_og_image'] ?? '' }}">
            <input type="hidden" name="sitemap_enabled" value="{{ $settings['sitemap_enabled'] ?? '1' }}">

            <div class="bg-white border border-[#111111]/15 p-5 sm:p-6 space-y-6 max-w-4xl">
                <div class="border-b border-[#111111]/10 pb-3">
                    <h3 class="font-bold text-sm text-[#111111]">Search Engine Webmaster Verifications</h3>
                    <p class="text-xs font-mono text-[#808080]">Verify site ownership in Google, Bing, Yandex, Pinterest, and Baidu without editing source code files.</p>
                </div>

                <div class="space-y-4">
                    {{-- Google Search Console --}}
                    <div class="p-4 bg-[#F8F8F6] border border-[#111111]/10 space-y-2">
                        <div class="flex items-center justify-between">
                            <label for="google_verification" class="block text-xs font-mono uppercase tracking-wider text-[#111111] font-bold">
                                1. Google Search Console
                            </label>
                            <a href="https://search.google.com/search-console" target="_blank" class="text-[10px] font-mono text-[#111111] hover:underline">
                                Open Console ↗
                            </a>
                        </div>
                        <input type="text" id="google_verification" name="google_verification"
                            value="{{ old('google_verification', $settings['google_verification'] ?? '') }}"
                            placeholder="google-site-verification token or code"
                            class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                        <p class="text-[10px] font-mono text-[#808080]">Injected as <code>&lt;meta name="google-site-verification" content="..."&gt;</code>.</p>
                    </div>

                    {{-- Bing Webmaster Tools --}}
                    <div class="p-4 bg-[#F8F8F6] border border-[#111111]/10 space-y-2">
                        <div class="flex items-center justify-between">
                            <label for="bing_verification" class="block text-xs font-mono uppercase tracking-wider text-[#111111] font-bold">
                                2. Bing Webmaster Tools
                            </label>
                            <a href="https://www.bing.com/webmasters" target="_blank" class="text-[10px] font-mono text-[#111111] hover:underline">
                                Open Console ↗
                            </a>
                        </div>
                        <input type="text" id="bing_verification" name="bing_verification"
                            value="{{ old('bing_verification', $settings['bing_verification'] ?? '') }}"
                            placeholder="msvalidate.01 token"
                            class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                        <p class="text-[10px] font-mono text-[#808080]">Injected as <code>&lt;meta name="msvalidate.01" content="..."&gt;</code>.</p>
                    </div>

                    {{-- Yandex Webmaster --}}
                    <div class="p-4 bg-[#F8F8F6] border border-[#111111]/10 space-y-2">
                        <div class="flex items-center justify-between">
                            <label for="yandex_verification" class="block text-xs font-mono uppercase tracking-wider text-[#111111] font-bold">
                                3. Yandex Webmaster Tools
                            </label>
                            <a href="https://webmaster.yandex.com/" target="_blank" class="text-[10px] font-mono text-[#111111] hover:underline">
                                Open Console ↗
                            </a>
                        </div>
                        <input type="text" id="yandex_verification" name="yandex_verification"
                            value="{{ old('yandex_verification', $settings['yandex_verification'] ?? '') }}"
                            placeholder="yandex-verification token"
                            class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                        <p class="text-[10px] font-mono text-[#808080]">Injected as <code>&lt;meta name="yandex-verification" content="..."&gt;</code>.</p>
                    </div>

                    {{-- Pinterest Verification --}}
                    <div class="p-4 bg-[#F8F8F6] border border-[#111111]/10 space-y-2">
                        <label for="pinterest_verification" class="block text-xs font-mono uppercase tracking-wider text-[#111111] font-bold">
                            4. Pinterest Domain Verification
                        </label>
                        <input type="text" id="pinterest_verification" name="pinterest_verification"
                            value="{{ old('pinterest_verification', $settings['pinterest_verification'] ?? '') }}"
                            placeholder="p:domain_verify token"
                            class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                    </div>

                    {{-- Baidu Verification --}}
                    <div class="p-4 bg-[#F8F8F6] border border-[#111111]/10 space-y-2">
                        <label for="baidu_verification" class="block text-xs font-mono uppercase tracking-wider text-[#111111] font-bold">
                            5. Baidu Search Verification
                        </label>
                        <input type="text" id="baidu_verification" name="baidu_verification"
                            value="{{ old('baidu_verification', $settings['baidu_verification'] ?? '') }}"
                            placeholder="baidu-site-verification token"
                            class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                    </div>
                </div>

                <div class="pt-4 border-t border-[#111111]/10">
                    <button type="submit" class="btn-primary text-xs font-mono py-2.5 px-6">
                        Save Verification Tokens →
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- ========================================================================= --}}
    {{-- TAB 4: XML Sitemap Controls & Ping --}}
    {{-- ========================================================================= --}}
    <div x-show="activeTab === 'sitemap'" class="space-y-6" style="display: none;">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 sm:gap-8 items-start">
            <div class="lg:col-span-7 space-y-6">
                <form action="{{ route('admin.seo.global') }}" method="POST" class="space-y-6">
                    @csrf
                    <input type="hidden" name="site_name" value="{{ $settings['site_name'] ?? 'SecuroFi.Tech' }}">
                    <input type="hidden" name="site_tagline" value="{{ $settings['site_tagline'] ?? '' }}">
                    <input type="hidden" name="site_description" value="{{ $settings['site_description'] ?? '' }}">
                    <input type="hidden" name="title_separator" value="{{ $settings['title_separator'] ?? '—' }}">
                    <input type="hidden" name="default_og_image" value="{{ $settings['default_og_image'] ?? '' }}">

                    <div class="bg-white border border-[#111111]/15 p-5 sm:p-6 space-y-5">
                        <div class="border-b border-[#111111]/10 pb-3">
                            <h3 class="font-bold text-sm text-[#111111]">XML Sitemap Generation Engine</h3>
                            <p class="text-xs font-mono text-[#808080]">Configure which entity types are indexed in <code>/sitemap.xml</code>.</p>
                        </div>

                        {{-- Master Toggle --}}
                        <div class="p-4 bg-[#F8F8F6] border border-[#111111]/10">
                            <label class="flex items-start sm:items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="sitemap_enabled" value="1"
                                    {{ ($settings['sitemap_enabled'] ?? '1') === '1' ? 'checked' : '' }}
                                    class="w-4 h-4 rounded-none border-[#111111] text-[#111111] mt-0.5 sm:mt-0">
                                <div>
                                    <span class="text-xs font-mono font-bold text-[#111111] block">Enable Public Dynamic XML Sitemap</span>
                                    <span class="text-[11px] font-mono text-[#808080] block mt-0.5">
                                        URL: <code>{{ url('/sitemap.xml') }}</code>
                                    </span>
                                </div>
                            </label>
                        </div>

                        {{-- Granular Inclusions --}}
                        <div class="space-y-3 pt-2">
                            <p class="text-xs font-mono font-bold text-[#111111] uppercase tracking-wider">Include Entities in Sitemap:</p>

                            <label class="flex items-center justify-between p-3 border border-[#111111]/10 bg-white cursor-pointer hover:bg-[#F8F8F6]">
                                <div class="flex items-center gap-2.5">
                                    <input type="checkbox" name="sitemap_include_articles" value="1"
                                        {{ ($settings['sitemap_include_articles'] ?? '1') === '1' ? 'checked' : '' }}
                                        class="w-4 h-4 rounded-none border-[#111111] text-[#111111]">
                                    <span class="text-xs font-mono text-[#111111]">Published Articles (Priority 0.8, Weekly)</span>
                                </div>
                                <span class="text-[10px] font-mono text-[#808080]">{{ $totalArticles }} URLs</span>
                            </label>

                            <label class="flex items-center justify-between p-3 border border-[#111111]/10 bg-white cursor-pointer hover:bg-[#F8F8F6]">
                                <div class="flex items-center gap-2.5">
                                    <input type="checkbox" name="sitemap_include_pages" value="1"
                                        {{ ($settings['sitemap_include_pages'] ?? '1') === '1' ? 'checked' : '' }}
                                        class="w-4 h-4 rounded-none border-[#111111] text-[#111111]">
                                    <span class="text-xs font-mono text-[#111111]">CMS Static Pages (Priority 0.6, Monthly)</span>
                                </div>
                                <span class="text-[10px] font-mono text-[#808080]">{{ $totalPages }} URLs</span>
                            </label>

                            <label class="flex items-center justify-between p-3 border border-[#111111]/10 bg-white cursor-pointer hover:bg-[#F8F8F6]">
                                <div class="flex items-center gap-2.5">
                                    <input type="checkbox" name="sitemap_include_categories" value="1"
                                        {{ ($settings['sitemap_include_categories'] ?? '1') === '1' ? 'checked' : '' }}
                                        class="w-4 h-4 rounded-none border-[#111111] text-[#111111]">
                                    <span class="text-xs font-mono text-[#111111]">Active Categories (Priority 0.7, Weekly)</span>
                                </div>
                                <span class="text-[10px] font-mono text-[#808080]">{{ $totalCategories }} URLs</span>
                            </label>

                            <label class="flex items-center justify-between p-3 border border-[#111111]/10 bg-white cursor-pointer hover:bg-[#F8F8F6]">
                                <div class="flex items-center gap-2.5">
                                    <input type="checkbox" name="sitemap_include_tags" value="1"
                                        {{ ($settings['sitemap_include_tags'] ?? '0') === '1' ? 'checked' : '' }}
                                        class="w-4 h-4 rounded-none border-[#111111] text-[#111111]">
                                    <span class="text-xs font-mono text-[#111111]">Tag Archives (Priority 0.5, Monthly)</span>
                                </div>
                                <span class="text-[10px] font-mono text-[#808080]">{{ $totalTags }} URLs</span>
                            </label>
                        </div>

                        <div class="pt-4 border-t border-[#111111]/10">
                            <button type="submit" class="btn-primary w-full text-xs font-mono uppercase tracking-wider py-3">
                                Save Sitemap Configuration →
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Right: Search Engine Ping & Instructions (5 cols) --}}
            <div class="lg:col-span-5 space-y-6">
                <div class="bg-white border border-[#111111]/15 p-5 sm:p-6 space-y-4">
                    <div class="border-b border-[#111111]/10 pb-3">
                        <h3 class="font-bold text-sm text-[#111111]">Instant Search Engine Ping</h3>
                        <p class="text-xs font-mono text-[#808080]">Notify Google & Bing crawlers of newly published updates.</p>
                    </div>

                    <p class="text-xs font-mono text-[#808080] leading-relaxed">
                        Clicking below immediately dispatches an HTTP GET request to search engine ping endpoints with your live sitemap location.
                    </p>

                    <form action="{{ route('admin.seo.ping') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full text-center text-xs font-mono py-3 px-4 bg-[#111111] text-white hover:opacity-85 transition-opacity font-bold">
                            Ping Google & Bing Now →
                        </button>
                    </form>

                    <div class="p-3 bg-[#F8F8F6] border border-[#111111]/10 space-y-1 text-[11px] font-mono text-[#808080]">
                        <p class="font-bold text-[#111111]">Sitemap Location:</p>
                        <code class="block bg-white p-2 border border-[#111111]/10 break-all">{{ url('/sitemap.xml') }}</code>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- TAB 5: robots.txt & AI Crawler Management --}}
    {{-- ========================================================================= --}}
    <div x-show="activeTab === 'robots'" class="space-y-6" style="display: none;">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 sm:gap-8 items-start">
            <div class="lg:col-span-7 space-y-6">
                <div class="bg-white border border-[#111111]/15 p-5 sm:p-6 space-y-4">
                    <div class="border-b border-[#111111]/10 pb-3">
                        <h3 class="font-bold text-sm text-[#111111]">robots.txt Directive Console</h3>
                        <p class="text-xs font-mono text-[#808080]">Instruct Googlebot, Bingbot, and AI assistants on indexing boundaries.</p>
                    </div>

                    <form action="{{ route('admin.seo.robots') }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <textarea id="robots_txt" name="robots_txt" rows="12" required
                                class="w-full px-3.5 py-2.5 text-xs font-mono bg-[#F8F8F6] border border-[#111111]/30 focus:outline-none focus:border-[#111111] leading-relaxed">{{ old('robots_txt', $settings['robots_txt'] ?? "User-agent: *\nAllow: /\n\n# AI Crawlers\nUser-agent: GPTBot\nAllow: /\n\nUser-agent: ClaudeBot\nAllow: /\n\nUser-agent: PerplexityBot\nAllow: /\n\n# Scraping Bots\nUser-agent: CCBot\nDisallow: /\n\nSitemap: " . url('/sitemap.xml')) }}</textarea>
                        </div>

                        <button type="submit" class="btn-primary text-xs font-mono py-2.5 px-6">
                            Update robots.txt Directives →
                        </button>
                    </form>
                </div>
            </div>

            {{-- Right: 1-Click AI Presets & Information --}}
            <div class="lg:col-span-5 space-y-6">
                <div class="bg-white border border-[#111111]/15 p-5 sm:p-6 space-y-4">
                    <div class="border-b border-[#111111]/10 pb-3">
                        <h3 class="font-bold text-sm text-[#111111]">1-Click AI Bot Directives Presets</h3>
                        <p class="text-xs font-mono text-[#808080]">Quickly insert industry standard configurations.</p>
                    </div>

                    <div class="space-y-2">
                        <button type="button" onclick="setRobotsPreset('balanced')"
                            class="w-full text-left p-3 border border-[#111111]/15 hover:bg-[#F5F1E8] transition-colors font-mono">
                            <span class="text-xs font-bold text-[#111111] block">Balanced (Allow AI assistants, block scrapers)</span>
                            <span class="text-[10px] text-[#808080] block">Allows GPTBot & ClaudeBot, blocks CCBot & Bytespider.</span>
                        </button>

                        <button type="button" onclick="setRobotsPreset('allow_all')"
                            class="w-full text-left p-3 border border-[#111111]/15 hover:bg-[#F5F1E8] transition-colors font-mono">
                            <span class="text-xs font-bold text-[#111111] block">Open Access (Maximum Indexing)</span>
                            <span class="text-[10px] text-[#808080] block">Allows all bots, search engines, and AI models unconditionally.</span>
                        </button>

                        <button type="button" onclick="setRobotsPreset('strict')"
                            class="w-full text-left p-3 border border-[#111111]/15 hover:bg-[#F5F1E8] transition-colors font-mono">
                            <span class="text-xs font-bold text-[#111111] block">Strict Privacy (Block AI training)</span>
                            <span class="text-[10px] text-[#808080] block">Disallows GPTBot, Google-Extended, ClaudeBot, and Anthropic.</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- TAB 6: Structured Data & Schema.org --}}
    {{-- ========================================================================= --}}
    <div x-show="activeTab === 'schema'" class="space-y-6" style="display: none;">
        <form action="{{ route('admin.seo.global') }}" method="POST">
            @csrf
            {{-- Inherit core fields --}}
            <input type="hidden" name="site_name" value="{{ $settings['site_name'] ?? 'SecuroFi.Tech' }}">
            <input type="hidden" name="site_tagline" value="{{ $settings['site_tagline'] ?? '' }}">
            <input type="hidden" name="site_description" value="{{ $settings['site_description'] ?? '' }}">
            <input type="hidden" name="title_separator" value="{{ $settings['title_separator'] ?? '—' }}">
            <input type="hidden" name="default_og_image" value="{{ $settings['default_og_image'] ?? '' }}">
            <input type="hidden" name="sitemap_enabled" value="{{ $settings['sitemap_enabled'] ?? '1' }}">

            <div class="bg-white border border-[#111111]/15 p-5 sm:p-6 space-y-6 max-w-4xl">
                <div class="border-b border-[#111111]/10 pb-3">
                    <h3 class="font-bold text-sm text-[#111111]">Schema.org JSON-LD Structured Data</h3>
                    <p class="text-xs font-mono text-[#808080]">Inject clean semantic schema for Google Rich Results, Article carousels, and breadcrumbs.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label for="schema_organization_type" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1 font-medium">
                            Organization Schema Type
                        </label>
                        <select id="schema_organization_type" name="schema_organization_type"
                            class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                            <option value="NewsMediaOrganization" {{ ($settings['schema_organization_type'] ?? 'NewsMediaOrganization') === 'NewsMediaOrganization' ? 'selected' : '' }}>NewsMediaOrganization (Recommended for tech/fintech news)</option>
                            <option value="Organization" {{ ($settings['schema_organization_type'] ?? '') === 'Organization' ? 'selected' : '' }}>Organization (Standard)</option>
                            <option value="Corporation" {{ ($settings['schema_organization_type'] ?? '') === 'Corporation' ? 'selected' : '' }}>Corporation</option>
                        </select>
                    </div>

                    <div>
                        <label for="schema_article_type" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1 font-medium">
                            Article Schema Type
                        </label>
                        <select id="schema_article_type" name="schema_article_type"
                            class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                            <option value="NewsArticle" {{ ($settings['schema_article_type'] ?? 'NewsArticle') === 'NewsArticle' ? 'selected' : '' }}>NewsArticle (Eligible for Google News)</option>
                            <option value="Article" {{ ($settings['schema_article_type'] ?? '') === 'Article' ? 'selected' : '' }}>Article (Standard)</option>
                            <option value="TechArticle" {{ ($settings['schema_article_type'] ?? '') === 'TechArticle' ? 'selected' : '' }}>TechArticle</option>
                        </select>
                    </div>
                </div>

                <div class="space-y-3 pt-2 border-t border-[#111111]/10">
                    <p class="text-xs font-mono font-bold text-[#111111] uppercase tracking-wider">Automated Rich Snippet Injections:</p>

                    <label class="flex items-start gap-2.5 cursor-pointer">
                        <input type="checkbox" name="schema_breadcrumbs_enabled" value="1"
                            {{ ($settings['schema_breadcrumbs_enabled'] ?? '1') === '1' ? 'checked' : '' }}
                            class="w-4 h-4 rounded-none border-[#111111] text-[#111111] mt-0.5">
                        <div>
                            <span class="text-xs font-mono text-[#111111] font-medium block">Inject BreadcrumbList Schema on Articles & Pages</span>
                            <span class="text-[10px] font-mono text-[#808080]">Displays hierarchical navigation breadcrumb links in Google Search Results.</span>
                        </div>
                    </label>

                    <label class="flex items-start gap-2.5 cursor-pointer">
                        <input type="checkbox" name="schema_searchbox_enabled" value="1"
                            {{ ($settings['schema_searchbox_enabled'] ?? '1') === '1' ? 'checked' : '' }}
                            class="w-4 h-4 rounded-none border-[#111111] text-[#111111] mt-0.5">
                        <div>
                            <span class="text-xs font-mono text-[#111111] font-medium block">Enable Sitelinks SearchBox Schema (WebSite SearchAction)</span>
                            <span class="text-[10px] font-mono text-[#808080]">Enables users to search directly inside your site via Google SERP searchbox.</span>
                        </div>
                    </label>
                </div>

                <div class="pt-4 border-t border-[#111111]/10">
                    <button type="submit" class="btn-primary text-xs font-mono py-2.5 px-6">
                        Save Structured Data Configuration →
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- ========================================================================= --}}
    {{-- TAB 7: 301 / 302 Redirect Manager --}}
    {{-- ========================================================================= --}}
    <div x-show="activeTab === 'redirects'" class="space-y-6" style="display: none;">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 sm:gap-8 items-start">

            {{-- Left: Create Redirect (5 cols) --}}
            <div class="lg:col-span-5 space-y-6">
                <div class="bg-white border border-[#111111]/15 p-5 sm:p-6 space-y-4">
                    <div class="border-b border-[#111111]/10 pb-3">
                        <h3 class="font-bold text-sm text-[#111111]">Create 301 / 302 Redirect Rule</h3>
                        <p class="text-xs font-mono text-[#808080]">Map deprecated or changed URLs to protect backlinks and SEO link equity.</p>
                    </div>

                    <form action="{{ route('admin.seo.redirects.store') }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label for="from_url" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1 font-medium">From Path *</label>
                            <input type="text" id="from_url" name="from_url" required
                                class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]"
                                placeholder="/old-vps-article">
                        </div>

                        <div>
                            <label for="to_url" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1 font-medium">Target URL / Path *</label>
                            <input type="text" id="to_url" name="to_url" required
                                class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]"
                                placeholder="/article/new-vps-guide or https://external.com">
                        </div>

                        <div>
                            <label for="type" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1 font-medium">Redirect Status Code</label>
                            <select id="type" name="type" class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                                <option value="301">301 — Permanent (SEO Equity Transferred)</option>
                                <option value="302">302 — Temporary Redirect</option>
                            </select>
                        </div>

                        <button type="submit" class="btn-primary w-full text-xs font-mono uppercase tracking-wider py-2.5">
                            Add Redirect Rule →
                        </button>
                    </form>
                </div>
            </div>

            {{-- Right: Table with Search Filter & CSV Export (7 cols) --}}
            <div class="lg:col-span-7 space-y-4">
                <div class="bg-white border border-[#111111]/15 p-5 sm:p-6 space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-[#111111]/10 pb-3">
                        <div>
                            <h3 class="font-bold text-sm text-[#111111]">Configured Redirect Rules</h3>
                            <span class="text-xs font-mono text-[#808080]">{{ $redirects->total() }} active rules</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="text" x-model="redirectSearch" placeholder="Search rules..."
                                class="px-2.5 py-1 text-xs font-mono bg-[#F8F8F6] border border-[#111111]/20 focus:outline-none focus:border-[#111111]">
                            <a href="{{ route('admin.seo.redirects.export') }}"
                                class="text-xs font-mono py-1 px-3 border border-[#111111] hover:bg-[#F5F1E8] transition-colors shrink-0">
                                Export CSV
                            </a>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs font-mono">
                            <thead>
                                <tr class="border-b border-[#111111]/10 text-[#808080] uppercase tracking-wider text-[10px]">
                                    <th class="py-2.5 pr-2">From → To</th>
                                    <th class="py-2.5 px-2">Type</th>
                                    <th class="py-2.5 px-2">Hits</th>
                                    <th class="py-2.5 pl-2 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#111111]/5">
                                @forelse ($redirects as $redir)
                                    <tr x-show="!redirectSearch || '{{ strtolower($redir->from_url . ' ' . $redir->to_url) }}'.includes(redirectSearch.toLowerCase())">
                                        <td class="py-2.5 pr-2">
                                            <div class="font-bold text-[#111111] truncate max-w-[220px]" title="{{ $redir->from_url }}">{{ $redir->from_url }}</div>
                                            <div class="text-[10px] text-[#808080] truncate max-w-[220px]" title="{{ $redir->to_url }}">→ {{ $redir->to_url }}</div>
                                        </td>
                                        <td class="py-2.5 px-2">
                                            <span class="px-1.5 py-0.5 text-[9px] font-bold {{ $redir->type === 301 ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                                {{ $redir->type }}
                                            </span>
                                        </td>
                                        <td class="py-2.5 px-2 text-[#808080] font-bold">{{ number_format($redir->hit_count) }}</td>
                                        <td class="py-2.5 pl-2 text-right">
                                            <form action="{{ route('admin.seo.redirects.destroy', $redir->id) }}" method="POST" onsubmit="return confirm('Delete redirect for {{ $redir->from_url }}?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-rose-600 hover:underline">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-6 text-center text-[#808080]">No active redirect rules defined.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($redirects->hasPages())
                        <div class="pt-3 border-t border-[#111111]/10">
                            {{ $redirects->links() }}
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- TAB 8: Script Injections (Header, Body, Footer) --}}
    {{-- ========================================================================= --}}
    <div x-show="activeTab === 'scripts'" class="space-y-6" style="display: none;">
        <div class="bg-white border border-[#111111]/15 p-5 sm:p-6 space-y-5 max-w-4xl">
            <div class="border-b border-[#111111]/10 pb-3">
                <h3 class="font-bold text-sm text-[#111111]">Custom Script Injections</h3>
                <p class="text-xs font-mono text-[#808080]">Inject Google Analytics 4, Tag Manager, Facebook Pixel, or AdSense without code modifications.</p>
            </div>

            <form action="{{ route('admin.seo.scripts') }}" method="POST" class="space-y-5">
                @csrf
                <div>
                    <label for="header_scripts" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1 font-medium">
                        Header Scripts (Injected before &lt;/head&gt;)
                    </label>
                    <textarea id="header_scripts" name="header_scripts" rows="5"
                        class="w-full px-3.5 py-2 text-xs font-mono bg-[#F8F8F6] border border-[#111111]/30 focus:outline-none focus:border-[#111111]"
                        placeholder="<!-- Google tag (gtag.js) -->&#10;<script async src='https://www.googletagmanager.com/gtag/js?id=G-...'></script>">{{ old('header_scripts', $settings['header_scripts'] ?? '') }}</textarea>
                </div>

                <div>
                    <label for="body_scripts" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1 font-medium">
                        Body Opening Scripts (Injected immediately after &lt;body&gt;)
                    </label>
                    <textarea id="body_scripts" name="body_scripts" rows="4"
                        class="w-full px-3.5 py-2 text-xs font-mono bg-[#F8F8F6] border border-[#111111]/30 focus:outline-none focus:border-[#111111]"
                        placeholder="<!-- Google Tag Manager (noscript) -->&#10;<noscript><iframe src='https://www.googletagmanager.com/ns.html?id=GTM-...' height='0' width='0' style='display:none;visibility:hidden'></iframe></noscript>">{{ old('body_scripts', $settings['body_scripts'] ?? '') }}</textarea>
                </div>

                <div>
                    <label for="footer_scripts" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1 font-medium">
                        Footer Scripts (Injected before &lt;/body&gt;)
                    </label>
                    <textarea id="footer_scripts" name="footer_scripts" rows="4"
                        class="w-full px-3.5 py-2 text-xs font-mono bg-[#F8F8F6] border border-[#111111]/30 focus:outline-none focus:border-[#111111]"
                        placeholder="<!-- Chat widget, heatmaps, tracking pixels -->">{{ old('footer_scripts', $settings['footer_scripts'] ?? '') }}</textarea>
                </div>

                <button type="submit" class="btn-primary text-xs font-mono py-2.5 px-6">
                    Save Custom Scripts →
                </button>
            </form>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- TAB 9: Real-Time Content SEO Audit --}}
    {{-- ========================================================================= --}}
    <div x-show="activeTab === 'audit'" class="space-y-6" style="display: none;">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Audit Metrics --}}
            <div class="bg-white border border-[#111111]/15 p-5 space-y-3">
                <span class="text-[10px] font-mono text-[#808080] uppercase tracking-wider">Custom Meta Titles</span>
                <div class="text-2xl font-bold font-mono text-[#111111]">
                    {{ $articlesWithTitle }} / {{ $totalArticles }}
                </div>
                <div class="w-full bg-[#F8F8F6] h-1.5 overflow-hidden border border-[#111111]/10">
                    <div class="h-full bg-emerald-500" style="width: {{ $totalArticles > 0 ? ($articlesWithTitle / $totalArticles) * 100 : 100 }}%"></div>
                </div>
                <span class="text-[11px] font-mono text-[#808080]">
                    {{ $totalArticles > 0 ? round(($articlesWithTitle / $totalArticles) * 100) : 100 }}% Coverage
                </span>
            </div>

            <div class="bg-white border border-[#111111]/15 p-5 space-y-3">
                <span class="text-[10px] font-mono text-[#808080] uppercase tracking-wider">Custom Meta Descriptions</span>
                <div class="text-2xl font-bold font-mono text-[#111111]">
                    {{ $articlesWithDesc }} / {{ $totalArticles }}
                </div>
                <div class="w-full bg-[#F8F8F6] h-1.5 overflow-hidden border border-[#111111]/10">
                    <div class="h-full bg-blue-500" style="width: {{ $totalArticles > 0 ? ($articlesWithDesc / $totalArticles) * 100 : 100 }}%"></div>
                </div>
                <span class="text-[11px] font-mono text-[#808080]">
                    {{ $totalArticles > 0 ? round(($articlesWithDesc / $totalArticles) * 100) : 100 }}% Coverage
                </span>
            </div>

            <div class="bg-white border border-[#111111]/15 p-5 space-y-3">
                <span class="text-[10px] font-mono text-[#808080] uppercase tracking-wider">Custom Social OG Images</span>
                <div class="text-2xl font-bold font-mono text-[#111111]">
                    {{ $articlesWithOg }} / {{ $totalArticles }}
                </div>
                <div class="w-full bg-[#F8F8F6] h-1.5 overflow-hidden border border-[#111111]/10">
                    <div class="h-full bg-purple-500" style="width: {{ $totalArticles > 0 ? ($articlesWithOg / $totalArticles) * 100 : 100 }}%"></div>
                </div>
                <span class="text-[11px] font-mono text-[#808080]">
                    {{ $totalArticles > 0 ? round(($articlesWithOg / $totalArticles) * 100) : 100 }}% Coverage
                </span>
            </div>

        </div>

        {{-- Articles Needing SEO Attention --}}
        <div class="bg-white border border-[#111111]/15 p-5 sm:p-6 space-y-4">
            <div class="border-b border-[#111111]/10 pb-3 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-sm text-[#111111]">Articles Needing SEO Attention</h3>
                    <p class="text-xs font-mono text-[#808080]">Published articles currently missing custom meta titles or descriptions.</p>
                </div>
                <a href="{{ route('admin.articles.index') }}" class="text-xs font-mono text-[#111111] hover:underline">
                    View All Articles →
                </a>
            </div>

            <div class="divide-y divide-[#111111]/10">
                @forelse ($articlesNeedingSeo as $art)
                    <div class="py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                            <span class="text-[10px] font-mono text-[#808080] uppercase">{{ $art->category->name ?? 'General' }}</span>
                            <h4 class="text-xs font-bold text-[#111111] line-clamp-1">{{ $art->title }}</h4>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 text-[9px] font-mono bg-amber-100 text-amber-800">Missing Custom SEO</span>
                            <a href="{{ route('admin.articles.edit', $art->id) }}"
                               class="text-xs font-mono py-1 px-3 bg-[#111111] text-white hover:opacity-85 transition-opacity">
                                Edit Article →
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-xs font-mono text-emerald-700">
                        ✓ Excellent! All published articles have configured SEO metadata.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
    function setRobotsPreset(type) {
        const sitemapUrl = "{{ url('/sitemap.xml') }}";
        const textarea = document.getElementById('robots_txt');
        if (!textarea) return;

        if (type === 'balanced') {
            textarea.value = `User-agent: *\nAllow: /\n\n# AI Crawlers\nUser-agent: GPTBot\nAllow: /\n\nUser-agent: ClaudeBot\nAllow: /\n\nUser-agent: PerplexityBot\nAllow: /\n\n# Aggressive Scrapers\nUser-agent: CCBot\nDisallow: /\n\nUser-agent: Bytespider\nDisallow: /\n\nSitemap: ${sitemapUrl}`;
        } else if (type === 'allow_all') {
            textarea.value = `User-agent: *\nAllow: /\n\nSitemap: ${sitemapUrl}`;
        } else if (type === 'strict') {
            textarea.value = `User-agent: *\nAllow: /\n\n# Block All AI Training\nUser-agent: GPTBot\nDisallow: /\n\nUser-agent: ChatGPT-User\nDisallow: /\n\nUser-agent: Google-Extended\nDisallow: /\n\nUser-agent: ClaudeBot\nDisallow: /\n\nUser-agent: anthropic-ai\nDisallow: /\n\nUser-agent: CCBot\nDisallow: /\n\nSitemap: ${sitemapUrl}`;
        }
    }
</script>
@endpush
@endsection
