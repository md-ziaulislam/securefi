<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- Dynamic SEO Tags, OpenGraph, Twitter Cards & Header Scripts --}}
    {!! \App\Services\SeoService::renderMeta($seoModel ?? null) !!}

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Database Dynamic Theme Settings (CSS Variables) --}}
    {!! \App\Services\ThemeService::renderCssVariables() !!}

    @stack('styles')

    {{-- RSS / Atom Feed Auto-Discovery (loaded when feed is enabled) --}}
    @if (\App\Models\Setting::get('feed_enabled', '1') === '1')
        <link rel="alternate" type="application/rss+xml" title="{{ \App\Models\Setting::get('site_name', 'SecuroFi.Tech') }} — RSS Feed" href="{{ route('feed.index') }}">
        <link rel="alternate" type="application/atom+xml" title="{{ \App\Models\Setting::get('site_name', 'SecuroFi.Tech') }} — Atom Feed" href="{{ route('feed.index', ['format' => 'atom']) }}">
    @endif
</head>
<body class="bg-secondary text-primary min-h-[100dvh] flex flex-col antialiased selection:bg-primary selection:text-secondary relative"
      :class="mobileMenuOpen ? 'overflow-hidden' : ''"
      x-data="{ mobileMenuOpen: false, mobileSearchOpen: false }"
      @keydown.window.escape="mobileMenuOpen = false; mobileSearchOpen = false">

    {{-- Body Scripts (Google Tag Manager noscript, etc.) --}}
    {!! \App\Services\SeoService::renderBodyScripts() !!}

    {{-- Top Announcement or Header Ad Slot --}}
    @php
        $headerAd = \App\Services\AdService::render('header', $currentCategoryId ?? null);
    @endphp
    @if ($headerAd->toHtml())
        <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 pt-3">
            {!! $headerAd !!}
        </div>
    @endif

    {{-- Site Navigation Bar --}}
    <header class="border-b border-neutral/20 bg-secondary/95 backdrop-blur-md sticky top-0 z-40">
        <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-2 sm:gap-4">
            {{-- Brand / Logo --}}
            <div class="flex items-center gap-3 md:gap-6 lg:gap-8 min-w-0 h-full">
                @php $siteLogo = \App\Models\Setting::get('site_logo_light'); @endphp
                <a href="{{ route('home') }}" class="flex items-center gap-2.5 text-primary group shrink-0 py-2">
                    @if ($siteLogo)
                        <img src="{{ $siteLogo }}" alt="{{ \App\Models\Setting::get('site_name', 'SecuroFi.Tech') }}"
                             class="h-7 sm:h-8 w-auto max-w-[130px] sm:max-w-[160px] object-contain">
                    @else
                        <span class="w-3.5 h-3.5 bg-primary group-hover:bg-neutral transition-colors shrink-0"></span>
                        <span class="font-bold text-base sm:text-lg lg:text-xl tracking-tight leading-none truncate">
                            {{ \App\Models\Setting::get('site_name', 'SecuroFi.Tech') }}
                        </span>
                    @endif
                </a>

                {{-- Desktop Category Links with Dropdown for Subcategories --}}
                @php
                    $navRootCats = \App\Models\Category::whereNull('parent_id')
                        ->where('is_active', true)
                        ->with(['children' => fn($q) => $q->where('is_active', true)->orderBy('order')->orderBy('name')])
                        ->orderBy('order')->orderBy('name')
                        ->get();
                @endphp
                <nav class="hidden lg:flex items-stretch h-16 text-xs font-mono font-medium">
                    @foreach ($navRootCats as $cat)
                        @php
                            $hasSubs = $cat->children->isNotEmpty();
                            $isActive = isset($currentCategory) && ($currentCategory->id === $cat->id || ($currentCategory->parent_id ?? null) === $cat->id);
                        @endphp
                        <div class="relative flex items-center h-full group"
                             x-data="{ dropdownOpen: false }"
                             @mouseenter="dropdownOpen = true"
                             @mouseleave="dropdownOpen = false"
                             @click.away="dropdownOpen = false">

                            @if ($hasSubs)
                                {{-- Split Action: Click text navigates, click or hover shows subcategories --}}
                                <div class="h-full flex items-center border-b-2 transition-colors {{ $isActive ? 'border-primary text-primary font-bold' : 'border-transparent text-neutral hover:text-primary hover:border-neutral/30' }}">
                                    <a href="{{ route('category.show', $cat->slug) }}"
                                       class="h-full flex items-center pl-2.5 xl:pl-3 pr-1">
                                        {{ $cat->name }}
                                    </a>
                                    <button type="button"
                                            @click.prevent="dropdownOpen = !dropdownOpen"
                                            class="h-full flex items-center pr-2 pl-0.5 text-neutral hover:text-primary focus:outline-none"
                                            aria-label="Toggle {{ $cat->name }} menu"
                                            :aria-expanded="dropdownOpen.toString()">
                                        <svg class="w-3 h-3 transition-transform duration-200" :class="dropdownOpen ? 'rotate-180 text-primary' : 'opacity-60'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </button>
                                </div>

                                {{-- Dropdown Menu (Flush with header bottom) --}}
                                <div x-show="dropdownOpen"
                                     x-cloak
                                     x-transition:enter="transition ease-out duration-150"
                                     x-transition:enter-start="opacity-0 translate-y-1"
                                     x-transition:enter-end="opacity-100 translate-y-0"
                                     x-transition:leave="transition ease-in duration-100"
                                     x-transition:leave-start="opacity-100 translate-y-0"
                                     x-transition:leave-end="opacity-0 translate-y-1"
                                     class="absolute left-0 top-full z-50 min-w-[220px] bg-secondary border border-neutral/20 shadow-2xl py-1.5 -mt-px">
                                    <a href="{{ route('category.show', $cat->slug) }}"
                                       class="flex items-center gap-2 px-4 py-2.5 text-[11px] text-primary hover:bg-tertiary font-semibold transition-colors border-b border-neutral/15 mb-1">
                                        <span class="w-1.5 h-1.5 bg-primary inline-block shrink-0"></span>
                                        All {{ $cat->name }}
                                        <span class="ml-auto text-[10px] text-neutral/70 font-mono">View →</span>
                                    </a>
                                    @foreach ($cat->children as $sub)
                                        <a href="{{ route('category.show', $sub->slug) }}"
                                           class="flex items-center gap-2 px-4 py-2 text-[11px] text-neutral hover:text-primary hover:bg-tertiary transition-colors
                                                  {{ isset($currentCategory) && $currentCategory->id === $sub->id ? 'text-primary font-bold bg-tertiary/60' : '' }}">
                                            <span class="w-1 h-1 border border-neutral/60 inline-block shrink-0"></span>
                                            {{ $sub->name }}
                                        </a>
                                    @endforeach
                                </div>
                            @else
                                {{-- Direct Category Link (No subcategories) --}}
                                <a href="{{ route('category.show', $cat->slug) }}"
                                   class="h-full flex items-center px-2.5 xl:px-3 border-b-2 transition-colors
                                          {{ $isActive ? 'border-primary text-primary font-bold' : 'border-transparent text-neutral hover:text-primary hover:border-neutral/30' }}">
                                    {{ $cat->name }}
                                </a>
                            @endif
                        </div>
                    @endforeach
                </nav>
            </div>

            {{-- Right Actions: Search + Dynamic In-Menu Pages + Contact CTA + Mobile Drawer Toggle --}}
            <div class="flex items-center gap-2 sm:gap-3 lg:gap-4 shrink-0">
                {{-- Desktop / Tablet Search Bar --}}
                <form action="{{ route('search') }}" method="GET" class="hidden sm:flex items-center relative">
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Search articles..."
                        class="w-28 sm:w-36 md:w-44 lg:w-48 xl:w-56 pl-8 pr-3 py-1.5 text-xs font-mono bg-tertiary/60 border border-neutral/30 focus:outline-none focus:border-primary focus:bg-secondary transition-all">
                    <svg class="w-3.5 h-3.5 text-neutral absolute left-2.5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </form>

                {{-- Mobile Search Icon Button (< sm) --}}
                <button type="button"
                        @click="mobileSearchOpen = !mobileSearchOpen; if (mobileSearchOpen) $nextTick(() => $refs.mobileSearchInput?.focus())"
                        class="sm:hidden p-2 text-neutral hover:text-primary transition-colors focus:outline-none"
                        aria-label="Toggle search bar">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </button>

                {{-- Dynamic In-Menu Pages (e.g. About) --}}
                @php
                    $headerMenuPages = \App\Models\Page::inMenu()->get();
                @endphp
                @foreach ($headerMenuPages as $hPage)
                    @if ($hPage->slug !== 'contact')
                        <a href="{{ route('page.show', $hPage->slug) }}"
                           class="hidden md:inline-block text-xs font-mono text-neutral hover:text-primary transition-colors px-1 py-1.5">
                            {{ $hPage->title }}
                        </a>
                    @endif
                @endforeach

                {{-- Contact CTA (Visible on tablet & desktop >= 640px) --}}
                <a href="{{ route('page.show', 'contact') }}" class="hidden sm:inline-flex btn-primary text-xs font-mono py-1.5 px-3 sm:px-3.5">
                    Contact
                </a>

                {{-- Mobile & Tablet Hamburger Menu Toggle (< lg) --}}
                <button type="button"
                        @click="mobileMenuOpen = !mobileMenuOpen"
                        class="lg:hidden p-2 text-primary hover:bg-tertiary transition-colors focus:outline-none"
                        aria-label="Toggle navigation menu"
                        :aria-expanded="mobileMenuOpen.toString()">
                    <svg x-show="!mobileMenuOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    <svg x-show="mobileMenuOpen" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>

        {{-- Mobile Expandable Search Bar Dropdown --}}
        <div x-show="mobileSearchOpen"
             x-cloak
             class="sm:hidden border-t border-neutral/20 bg-secondary px-4 py-3 shadow-md"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2">
            <form action="{{ route('search') }}" method="GET" class="flex items-center gap-2">
                <div class="relative flex-1">
                    <input type="text"
                           name="q"
                           x-ref="mobileSearchInput"
                           value="{{ request('q') }}"
                           placeholder="Search articles, reviews, guides..."
                           class="w-full pl-8 pr-3 py-2 text-xs font-mono bg-tertiary/60 border border-neutral/30 focus:outline-none focus:border-primary">
                    <svg class="w-3.5 h-3.5 text-neutral absolute left-2.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <button type="submit" class="btn-primary py-2 px-3 text-xs font-mono shrink-0">Search</button>
            </form>
        </div>
    </header>

    {{-- Mobile Slide-Over Drawer Navigation (Placed outside <header> to cover full viewport) --}}
    <div x-show="mobileMenuOpen"
         x-cloak
         class="fixed inset-0 z-50 lg:hidden"
         role="dialog"
         aria-modal="true"
         @keydown.escape.window="mobileMenuOpen = false">

        {{-- Backdrop with smooth fade --}}
        <div @click="mobileMenuOpen = false"
             class="fixed inset-0 bg-[#111111]/50 backdrop-blur-sm transition-opacity"
             x-show="mobileMenuOpen"
             x-transition:enter="transition-opacity ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"></div>

        {{-- Drawer Panel with smooth slide --}}
        <div class="fixed inset-y-0 right-0 w-full max-w-[320px] sm:max-w-sm bg-secondary border-l border-neutral/20 shadow-2xl flex flex-col justify-between overflow-y-auto h-[100dvh] z-10"
             x-show="mobileMenuOpen"
             x-transition:enter="transform transition ease-in-out duration-300"
             x-transition:enter-start="translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transform transition ease-in-out duration-300"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="translate-x-full">

            <div class="p-5 sm:p-6">
                {{-- Drawer Header --}}
                <div class="flex items-center justify-between pb-4 border-b border-neutral/20 mb-6">
                    <a href="{{ route('home') }}" class="flex items-center gap-2 text-primary font-bold text-base tracking-tight" @click="mobileMenuOpen = false">
                        <span class="w-3 h-3 bg-primary"></span>
                        <span>{{ \App\Models\Setting::get('site_name', 'SecuroFi.Tech') }}</span>
                    </a>
                    <button type="button"
                            @click="mobileMenuOpen = false"
                            class="p-1.5 text-neutral hover:text-primary transition-colors focus:outline-none"
                            aria-label="Close navigation menu">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Categories Navigation --}}
                <div class="space-y-4" x-data="{ expandedCat: null }">
                    <div class="text-[10px] font-mono text-neutral uppercase tracking-widest font-semibold flex items-center justify-between">
                        <span>Knowledge Verticals</span>
                        <span class="text-neutral/60 font-normal">({{ $navRootCats->count() }})</span>
                    </div>

                    <div class="space-y-1 font-mono text-xs">
                        @foreach ($navRootCats as $cat)
                            @php
                                $hasSubs = $cat->children->isNotEmpty();
                                $isActive = isset($currentCategory) && ($currentCategory->id === $cat->id || ($currentCategory->parent_id ?? null) === $cat->id);
                            @endphp
                            <div class="border-b border-neutral/10 pb-1">
                                <div class="flex items-center justify-between min-h-[44px]">
                                    <a href="{{ route('category.show', $cat->slug) }}"
                                       class="font-bold text-sm hover:underline flex items-center gap-2 py-2 flex-1 {{ $isActive ? 'text-primary' : 'text-neutral hover:text-primary' }}">
                                        <span class="w-2 h-2 {{ $isActive ? 'bg-primary' : 'bg-neutral/40' }} shrink-0"></span>
                                        <span>{{ $cat->name }}</span>
                                    </a>
                                    @if ($hasSubs)
                                        <button type="button"
                                                @click="expandedCat = (expandedCat === {{ $cat->id }} ? null : {{ $cat->id }})"
                                                class="px-2.5 py-2 text-neutral hover:text-primary transition-colors flex items-center gap-1 focus:outline-none"
                                                aria-label="Toggle {{ $cat->name }} subcategories">
                                            <span class="text-[10px] uppercase font-mono" x-text="expandedCat === {{ $cat->id }} ? 'Hide' : 'More'"></span>
                                            <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="expandedCat === {{ $cat->id }} ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        </button>
                                    @endif
                                </div>

                                @if ($hasSubs)
                                    <div x-show="expandedCat === {{ $cat->id }}"
                                         x-cloak
                                         x-transition:enter="transition ease-out duration-200"
                                         x-transition:enter-start="opacity-0 -translate-y-1"
                                         x-transition:enter-end="opacity-100 translate-y-0"
                                         x-transition:leave="transition ease-in duration-150"
                                         x-transition:leave-start="opacity-100 translate-y-0"
                                         x-transition:leave-end="opacity-0 -translate-y-1"
                                         class="pl-4 pb-2.5 space-y-1.5 border-l-2 border-primary/20 ml-2 mt-1">
                                        <a href="{{ route('category.show', $cat->slug) }}"
                                           class="text-xs text-primary font-semibold block py-1 hover:underline">
                                            → All {{ $cat->name }}
                                        </a>
                                        @foreach ($cat->children as $sub)
                                            <a href="{{ route('category.show', $sub->slug) }}"
                                               class="text-xs block py-1 hover:underline flex items-center gap-1.5 {{ isset($currentCategory) && $currentCategory->id === $sub->id ? 'text-primary font-bold' : 'text-neutral hover:text-primary' }}">
                                                <span class="w-1 h-1 bg-neutral/50 shrink-0"></span>
                                                <span>{{ $sub->name }}</span>
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Explore Pages --}}
                <div class="mt-6 space-y-3 pt-5 border-t border-neutral/20">
                    <div class="text-[10px] font-mono text-neutral uppercase tracking-widest font-semibold">Explore & Resources</div>
                    <div class="grid grid-cols-2 gap-2 text-xs font-mono">
                        <a href="{{ route('home') }}" class="py-1.5 text-neutral hover:text-primary transition-colors flex items-center gap-1.5">
                            <span>•</span> Home
                        </a>
                        @foreach (\App\Models\Page::published()->get() as $p)
                            <a href="{{ route('page.show', $p->slug) }}" class="py-1.5 text-neutral hover:text-primary transition-colors flex items-center gap-1.5 truncate">
                                <span>•</span> {{ $p->title }}
                            </a>
                        @endforeach
                        <a href="{{ url('/sitemap.xml') }}" target="_blank" class="py-1.5 text-neutral hover:text-primary transition-colors flex items-center gap-1.5">
                            <span>•</span> XML Sitemap
                        </a>

                    </div>
                </div>
            </div>

            {{-- Drawer Bottom CTA --}}
            <div class="p-5 sm:p-6 border-t border-neutral/20 bg-tertiary/40">
                <a href="{{ route('page.show', 'contact') }}" class="btn-primary w-full text-center text-xs font-mono py-2.5 block">
                    Contact Technical Staff →
                </a>
                <div class="mt-3 text-[10px] font-mono text-neutral text-center">
                    &copy; {{ date('Y') }} {{ \App\Models\Setting::get('site_name', 'SecuroFi.Tech') }}
                </div>
            </div>
        </div>
    </div>

    {{-- Main View Container --}}
    <main class="flex-1">
        @yield('content')
    </main>

    {{-- Swiss Minimalist Footer --}}
    <footer class="border-t border-neutral/20 bg-tertiary/40 pt-16 pb-12 mt-20">
        <div class="max-w-[1280px] mx-auto px-6">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-10 pb-12 border-b border-neutral/20">
                {{-- Column 1: Brand & Mandate --}}
                <div class="md:col-span-5 space-y-4">
                    <div class="flex items-center gap-2">
                        @php $footerLogo = \App\Models\Setting::get('site_logo_light'); @endphp
                        @if ($footerLogo)
                            <img src="{{ $footerLogo }}" alt="{{ \App\Models\Setting::get('site_name', 'SecuroFi.Tech') }}" class="h-7 w-auto max-w-[140px] object-contain">
                        @else
                            <span class="w-3 h-3 bg-primary"></span>
                            <span class="font-bold text-lg tracking-tight">{{ \App\Models\Setting::get('site_name', 'SecuroFi.Tech') }}</span>
                        @endif
                    </div>
                    <p class="text-sm text-neutral max-w-md leading-relaxed">
                        {{ \App\Models\Setting::get('site_description', 'Objective, Swiss-standard engineering analysis for AI software, web hosting architectures, digital security, and personal finance.') }}
                    </p>
                    <div class="flex items-center gap-3 pt-2 text-xs font-mono text-neutral">
                        <span>Lead Architect: Ziaul Islam</span>
                        <span>•</span>
                        <a href="{{ route('page.show', 'dev-info') }}" class="hover:text-primary underline">dev-info</a>
                    </div>
                </div>

                {{-- Column 2: Categories (Hierarchical) --}}
                @php
                    $footerRootCats = \App\Models\Category::whereNull('parent_id')
                        ->where('is_active', true)
                        ->with(['children' => fn($q) => $q->where('is_active', true)->orderBy('order')->orderBy('name')])
                        ->orderBy('order')->orderBy('name')
                        ->get();
                @endphp
                <div class="md:col-span-4 space-y-3">
                    <h3 class="text-xs font-mono uppercase tracking-widest text-primary font-semibold">Content Topics</h3>
                    <ul class="space-y-1.5 text-xs font-mono text-neutral">
                        @foreach ($footerRootCats as $cat)
                            <li>
                                <a href="{{ route('category.show', $cat->slug) }}" class="hover:text-primary transition-colors font-semibold">
                                    → {{ $cat->name }}
                                </a>
                                @if ($cat->children->isNotEmpty())
                                    <ul class="mt-1 ml-3 space-y-1 border-l border-neutral/20 pl-3">
                                        @foreach ($cat->children as $sub)
                                            <li>
                                                <a href="{{ route('category.show', $sub->slug) }}" class="hover:text-primary transition-colors text-[11px]">
                                                    {{ $sub->name }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>

                {{-- Column 3: Platform Links --}}
                <div class="md:col-span-3 space-y-3">
                    <h3 class="text-xs font-mono uppercase tracking-widest text-primary font-semibold">Governance & Pages</h3>
                    <ul class="space-y-2 text-xs font-mono text-neutral">
                        @foreach (\App\Models\Page::inFooter()->get() as $page)
                            <li>
                                <a href="{{ route('page.show', $page->slug) }}" class="hover:text-primary transition-colors">
                                    {{ $page->title }}
                                </a>
                            </li>
                        @endforeach
                        <li>
                            <a href="{{ url('/sitemap.xml') }}" target="_blank" class="hover:text-primary transition-colors">
                                XML Sitemap
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Sub-footer --}}
            <div class="pt-8 flex flex-col md:flex-row items-center justify-between gap-4 text-xs font-mono text-neutral">
                <div>
                    &copy; {{ date('Y') }} {{ \App\Models\Setting::get('site_name', 'SecuroFi.Tech') }}. Engineered with Precision & Integrity.
                </div>
                <div class="flex items-center gap-4">
                    <a href="{{ \App\Models\Setting::get('social_twitter', '#') }}" target="_blank" class="hover:text-primary transition-colors">X / Twitter</a>
                    <span>•</span>
                    <a href="{{ \App\Models\Setting::get('social_github', '#') }}" target="_blank" class="hover:text-primary transition-colors">GitHub</a>
                    @if(\App\Models\Setting::get('social_linkedin'))
                        <span>•</span>
                        <a href="{{ \App\Models\Setting::get('social_linkedin') }}" target="_blank" class="hover:text-primary transition-colors">LinkedIn</a>
                    @endif
                </div>
            </div>
        </div>
    </footer>

    {{-- Cookie Consent Banner (if enabled) --}}
    @if (\App\Models\Setting::get('cookie_consent_enabled', '1') === '1')
        <div id="cookie-banner" class="fixed bottom-0 inset-x-0 bg-primary text-secondary p-4 z-50 border-t border-neutral/30 hidden">
            <div class="max-w-[1280px] mx-auto px-6 flex flex-col sm:flex-row items-center justify-between gap-4">
                <p class="text-xs font-mono leading-relaxed max-w-3xl">
                    {{ \App\Models\Setting::get('cookie_consent_text', 'We use privacy-conscious analytics and cookies to deliver high-performance browsing and relevant recommendations.') }}
                </p>
                <div class="flex items-center gap-3 shrink-0">
                    <button onclick="rejectCookies()" class="px-3 py-1.5 border border-secondary text-secondary text-xs font-mono hover:bg-secondary/10 transition-colors">
                        Essential Only
                    </button>
                    <button onclick="acceptCookies()" class="px-4 py-1.5 bg-secondary text-primary text-xs font-mono font-bold hover:bg-tertiary transition-colors">
                        Accept All
                    </button>
                </div>
            </div>
        </div>
        <script>
            if (!localStorage.getItem('cookie_consent')) {
                document.getElementById('cookie-banner').classList.remove('hidden');
            }
            function acceptCookies() {
                localStorage.setItem('cookie_consent', 'accepted');
                document.getElementById('cookie-banner').classList.add('hidden');
            }
            function rejectCookies() {
                localStorage.setItem('cookie_consent', 'essential_only');
                document.getElementById('cookie-banner').classList.add('hidden');
            }
        </script>
    @endif

    {{-- Sticky Floating Footer Ad Injection --}}
    {!! \App\Services\AdService::render('sticky_footer') !!}

    {{-- Footer Custom Scripts Injection --}}
    {!! \App\Services\SeoService::renderFooterScripts() !!}

    {{-- CAPTCHA Script (reCAPTCHA / Cloudflare Turnstile — loaded globally when enabled) --}}
    @php $captchaService = app(\App\Services\CaptchaService::class); @endphp
    @if ($captchaService->isEnabled())
        {!! $captchaService->scriptTag() !!}
    @endif

    {{-- Client-side Auto Timezone Detection & Conversion (PRD-ADDNEW 5.5) --}}
    @if($isTimezoneAutoDetect ?? true)
    <script>
        (function() {
            try {
                var userTz = Intl.DateTimeFormat().resolvedOptions().timeZone;
                if (userTz) {
                    var match = document.cookie.match(new RegExp('(^| )visitor_timezone=([^;]+)'));
                    if (!match || decodeURIComponent(match[2]) !== userTz) {
                        document.cookie = "visitor_timezone=" + encodeURIComponent(userTz) + "; path=/; max-age=31536000; SameSite=Lax";
                    }

                    document.querySelectorAll('.local-time, [data-timestamp]').forEach(function(el) {
                        var iso = el.getAttribute('data-timestamp') || el.getAttribute('datetime');
                        if (!iso) return;
                        var d = new Date(iso);
                        if (isNaN(d.getTime())) return;

                        if (el.hasAttribute('data-time-only')) {
                            el.textContent = d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                        } else if (el.hasAttribute('data-date-only')) {
                            el.textContent = d.toLocaleDateString([], { year: 'numeric', month: 'short', day: 'numeric' });
                        } else if (el.hasAttribute('data-relative-time')) {
                            var diffSec = Math.floor((new Date() - d) / 1000);
                            if (diffSec < 60) el.textContent = 'just now';
                            else if (diffSec < 3600) el.textContent = Math.floor(diffSec / 60) + ' min ago';
                            else if (diffSec < 86400) el.textContent = Math.floor(diffSec / 3600) + ' hours ago';
                            else if (diffSec < 604800) el.textContent = Math.floor(diffSec / 86400) + ' days ago';
                            else el.textContent = d.toLocaleDateString([], { year: 'numeric', month: 'short', day: 'numeric' });
                        } else {
                            el.textContent = d.toLocaleDateString([], { year: 'numeric', month: 'short', day: 'numeric' }) + ' ' + d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                        }
                    });
                }
            } catch(e) {}
        })();
    </script>
    @endif

    @stack('scripts')
</body>
</html>
