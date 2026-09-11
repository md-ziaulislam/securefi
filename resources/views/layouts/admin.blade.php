<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard') — {{ \App\Models\Setting::get('site_name', 'SecuroFi.Tech') }}</title>
    @php
        $adminFavicon = \App\Models\Setting::get('site_favicon');
        if ($adminFavicon) {
            $ext = strtolower(pathinfo(parse_url($adminFavicon, PHP_URL_PATH), PATHINFO_EXTENSION));
            $mimeMap = ['ico' => 'image/x-icon', 'png' => 'image/png', 'svg' => 'image/svg+xml'];
            $adminFaviconMime = $mimeMap[$ext] ?? 'image/png';
        }
    @endphp
    @if (!empty($adminFavicon))
        <link rel="icon" type="{{ $adminFaviconMime }}" href="{{ $adminFavicon }}">
        <link rel="shortcut icon" href="{{ $adminFavicon }}">
        <link rel="apple-touch-icon" href="{{ $adminFavicon }}">
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {!! \App\Services\ThemeService::renderCssVariables() !!}
    @stack('styles')
</head>
<body class="bg-[#F8F8F6] text-[#111111] antialiased min-h-[100dvh] flex flex-col" x-data="{ sidebarOpen: false }" @keydown.window.escape="sidebarOpen = false">

    <!-- Mobile / Tablet Sidebar Backdrop -->
    <div x-show="sidebarOpen"
         x-cloak
         @click="sidebarOpen = false"
         class="fixed inset-0 bg-[#111111]/50 backdrop-blur-xs z-40 lg:hidden"
         x-transition:enter="transition-opacity ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"></div>

    <!-- Sidebar Navigation -->
    <aside class="w-64 bg-white border-r border-[#111111]/15 flex flex-col justify-between shrink-0 fixed inset-y-0 left-0 z-50 transition-transform duration-300 ease-in-out lg:translate-x-0"
           :class="sidebarOpen ? 'translate-x-0 shadow-2xl' : '-translate-x-full lg:translate-x-0'">
        <div>
            <!-- Brand -->
            <div class="h-16 border-b border-[#111111]/15 flex items-center justify-between px-6">
                <div class="flex items-center gap-3">
                    @php $adminLogo = \App\Models\Setting::get('site_logo_light'); @endphp
                    @if ($adminLogo)
                        <img src="{{ $adminLogo }}" alt="{{ \App\Models\Setting::get('site_name', 'SecuroFi.Tech') }}" class="h-7 w-auto max-w-[120px] object-contain">
                    @else
                        <span class="w-3 h-3 bg-[#111111]"></span>
                    @endif
                    <div>
                        <span class="font-bold tracking-tight text-base text-[#111111] block leading-none">{{ \App\Models\Setting::get('site_name', 'SecuroFi.Tech') }}</span>
                        <span class="text-[10px] font-mono text-[#808080] uppercase tracking-wider block mt-1">Control Engine</span>
                    </div>
                </div>
                <!-- Close Button for Mobile & Tablet -->
                <button type="button" @click="sidebarOpen = false" class="lg:hidden p-1.5 text-[#808080] hover:text-[#111111] hover:bg-[#F5F1E8] transition-colors" aria-label="Close menu">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Navigation Links -->
            <nav class="p-3 space-y-0.5 overflow-y-auto max-h-[calc(100vh-8rem)] text-xs font-mono">
                <div class="px-3 pt-3 pb-1 text-[10px] uppercase text-[#808080] tracking-widest font-semibold">Core Management</div>

                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-none transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-[#111111] text-white font-medium' : 'text-[#111111] hover:bg-[#F5F1E8]' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    <span>Dashboard</span>
                </a>

                @can('manage articles')
                <a href="{{ route('admin.articles.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-none transition-colors {{ request()->routeIs('admin.articles.*') ? 'bg-[#111111] text-white font-medium' : 'text-[#111111] hover:bg-[#F5F1E8]' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                    <span>Articles</span>
                </a>
                @endcan

                @can('manage categories')
                <a href="{{ route('admin.categories.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-none transition-colors {{ request()->routeIs('admin.categories.*') ? 'bg-[#111111] text-white font-medium' : 'text-[#111111] hover:bg-[#F5F1E8]' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                    <span>Categories</span>
                </a>
                @endcan

                @can('manage tags')
                <a href="{{ route('admin.tags.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-none transition-colors {{ request()->routeIs('admin.tags.*') ? 'bg-[#111111] text-white font-medium' : 'text-[#111111] hover:bg-[#F5F1E8]' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/></svg>
                    <span>Tags</span>
                </a>
                @endcan

                @can('manage media')
                <a href="{{ route('admin.media.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-none transition-colors {{ request()->routeIs('admin.media.*') ? 'bg-[#111111] text-white font-medium' : 'text-[#111111] hover:bg-[#F5F1E8]' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>Media Library</span>
                </a>
                @endcan

                @can('manage comments')
                <a href="{{ route('admin.comments.index') }}" class="flex items-center justify-between px-3 py-2 rounded-none transition-colors {{ request()->routeIs('admin.comments.*') ? 'bg-[#111111] text-white font-medium' : 'text-[#111111] hover:bg-[#F5F1E8]' }}">
                    <div class="flex items-center gap-2.5">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        <span>Comments</span>
                    </div>
                    @php $pendingCountNav = \App\Models\Comment::where('status', 'pending')->count(); @endphp
                    @if ($pendingCountNav > 0)
                        <span class="px-1.5 py-0.2 bg-amber-500 text-white font-mono text-[9px] font-bold">
                            {{ $pendingCountNav }}
                        </span>
                    @endif
                </a>
                @endcan

                @canany(['manage ads', 'manage affiliates'])
                <div class="px-3 pt-4 pb-1 text-[10px] uppercase text-[#808080] tracking-widest font-semibold">Monetization</div>

                @can('manage ads')
                <a href="{{ route('admin.ads.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-none transition-colors {{ request()->routeIs('admin.ads.*') ? 'bg-[#111111] text-white font-medium' : 'text-[#111111] hover:bg-[#F5F1E8]' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                    <span>Ads Management</span>
                </a>
                @endcan

                @can('manage affiliates')
                <a href="{{ route('admin.affiliates.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-none transition-colors {{ request()->routeIs('admin.affiliates.*') ? 'bg-[#111111] text-white font-medium' : 'text-[#111111] hover:bg-[#F5F1E8]' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                    <span>Affiliate Links & Box</span>
                </a>
                @endcan
                @endcanany

                @canany(['manage seo', 'manage pages', 'manage theme', 'view analytics'])
                <div class="px-3 pt-4 pb-1 text-[10px] uppercase text-[#808080] tracking-widest font-semibold">Growth & Platform</div>

                @can('manage seo')
                <a href="{{ route('admin.seo.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-none transition-colors {{ request()->routeIs('admin.seo.*') ? 'bg-[#111111] text-white font-medium' : 'text-[#111111] hover:bg-[#F5F1E8]' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <span>SEO Suite</span>
                </a>
                @endcan

                @can('manage pages')
                <a href="{{ route('admin.pages.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-none transition-colors {{ request()->routeIs('admin.pages.*') ? 'bg-[#111111] text-white font-medium' : 'text-[#111111] hover:bg-[#F5F1E8]' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Pages (CMS)</span>
                </a>
                @endcan

                @can('manage theme')
                <a href="{{ route('admin.theme.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-none transition-colors {{ request()->routeIs('admin.theme.*') ? 'bg-[#111111] text-white font-medium' : 'text-[#111111] hover:bg-[#F5F1E8]' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4 5 5 0 015-5h1a1 1 0 001-1V9a4 4 0 018 0v2a1 1 0 001 1h1a5 5 0 015 5 4 4 0 01-4 4H7z"/></svg>
                    <span>Theme Customizer</span>
                </a>
                @endcan

                @can('view analytics')
                <a href="{{ route('admin.analytics.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-none transition-colors {{ request()->routeIs('admin.analytics.*') ? 'bg-[#111111] text-white font-medium' : 'text-[#111111] hover:bg-[#F5F1E8]' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    <span>Live Traffic Monitor</span>
                </a>
                @endcan
                @endcanany

                <div class="px-3 pt-4 pb-1 text-[10px] uppercase text-[#808080] tracking-widest font-semibold">Email Marketing</div>

                <a href="{{ route('admin.email.compose.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-none transition-colors {{ request()->routeIs('admin.email.compose.*') ? 'bg-[#111111] text-white font-medium' : 'text-[#111111] hover:bg-[#F5F1E8]' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span>Compose Mail</span>
                </a>

                <a href="{{ route('admin.email.subscribers.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-none transition-colors {{ request()->routeIs('admin.email.subscribers.*') ? 'bg-[#111111] text-white font-medium' : 'text-[#111111] hover:bg-[#F5F1E8]' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    <span>Subscribers</span>
                </a>

                <a href="{{ route('admin.email.campaigns.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-none transition-colors {{ request()->routeIs('admin.email.campaigns.*') ? 'bg-[#111111] text-white font-medium' : 'text-[#111111] hover:bg-[#F5F1E8]' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    <span>Campaigns</span>
                </a>

                <a href="{{ route('admin.email.templates.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-none transition-colors {{ request()->routeIs('admin.email.templates.*') ? 'bg-[#111111] text-white font-medium' : 'text-[#111111] hover:bg-[#F5F1E8]' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/></svg>
                    <span>Email Templates</span>
                </a>

                <a href="{{ route('admin.email.logs.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-none transition-colors {{ request()->routeIs('admin.email.logs.*') ? 'bg-[#111111] text-white font-medium' : 'text-[#111111] hover:bg-[#F5F1E8]' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Delivery Logs</span>
                </a>

                <a href="{{ route('admin.email.smtp.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-none transition-colors {{ request()->routeIs('admin.email.smtp.*') ? 'bg-[#111111] text-white font-medium' : 'text-[#111111] hover:bg-[#F5F1E8]' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    <span>SMTP Settings</span>
                </a>

                @canany(['manage users', 'view logs', 'manage security', 'manage settings'])
                <div class="px-3 pt-4 pb-1 text-[10px] uppercase text-[#808080] tracking-widest font-semibold">Administration</div>

                @can('manage users')
                <a href="{{ route('admin.users.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-none transition-colors {{ request()->routeIs('admin.users.*') ? 'bg-[#111111] text-white font-medium' : 'text-[#111111] hover:bg-[#F5F1E8]' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    <span>Users & RBAC</span>
                </a>
                @endcan

                @canany(['view logs', 'manage security'])
                <a href="{{ route('admin.security.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-none transition-colors {{ request()->routeIs('admin.security.*') ? 'bg-[#111111] text-white font-medium' : 'text-[#111111] hover:bg-[#F5F1E8]' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    <span>Security & Backups</span>
                </a>
                @endcanany

                @can('manage settings')
                <a href="{{ route('admin.settings.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-none transition-colors {{ request()->routeIs('admin.settings.*') ? 'bg-[#111111] text-white font-medium' : 'text-[#111111] hover:bg-[#F5F1E8]' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>General Settings</span>
                </a>

                <a href="{{ route('admin.feed.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-none transition-colors {{ request()->routeIs('admin.feed.*') ? 'bg-[#111111] text-white font-medium' : 'text-[#111111] hover:bg-[#F5F1E8]' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 5c7.18 0 13 5.82 13 13M6 11a7 7 0 017 7m-6 0a1 1 0 11-2 0 1 1 0 012 0z"/></svg>
                    <span>RSS / Atom Feed</span>
                </a>

                <a href="{{ route('admin.llms.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-none transition-colors {{ request()->routeIs('admin.llms.*') ? 'bg-[#111111] text-white font-medium' : 'text-[#111111] hover:bg-[#F5F1E8]' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    <span>llms.txt (AI Visibility)</span>
                </a>

                <a href="{{ route('admin.cdn.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-none transition-colors {{ request()->routeIs('admin.cdn.*') ? 'bg-[#111111] text-white font-medium' : 'text-[#111111] hover:bg-[#F5F1E8]' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    <span>CDN & Cache Purge</span>
                </a>

                <a href="{{ route('admin.uptime.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-none transition-colors {{ request()->routeIs('admin.uptime.*') ? 'bg-[#111111] text-white font-medium' : 'text-[#111111] hover:bg-[#F5F1E8]' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    <span>Uptime & Health</span>
                </a>

                <a href="{{ route('admin.errors.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-none transition-colors {{ request()->routeIs('admin.errors.*') ? 'bg-[#111111] text-white font-medium' : 'text-[#111111] hover:bg-[#F5F1E8]' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>Error Monitoring</span>
                </a>
                @endcan
                @endcanany
            </nav>
        </div>

        <!-- User Profile & Logout -->
        <div class="p-3.5 border-t border-[#111111]/15 bg-white">
            <div class="flex items-center justify-between gap-2">
                <a href="{{ route('admin.profile.index') }}" class="flex items-center gap-2.5 truncate flex-1 p-1 -m-1 hover:bg-[#F8F8F6] transition-colors rounded-none group" title="Account Profile & 2FA Security">
                    <div class="w-7 h-7 bg-[#111111] text-white flex items-center justify-center font-mono font-bold text-[11px] flex-shrink-0 relative">
                        @if(auth()->user() && auth()->user()->avatar && file_exists(public_path('storage/' . auth()->user()->avatar)))
                            <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="{{ auth()->user()->name }}" class="w-full h-full object-cover">
                        @else
                            <span>{{ auth()->user()->initials ?? 'AD' }}</span>
                        @endif
                        @if(auth()->user() && auth()->user()->hasTwoFactorEnabled())
                            <span class="absolute -top-0.5 -right-0.5 w-2 h-2 bg-emerald-500 border border-white rounded-full" title="2FA Active"></span>
                        @endif
                    </div>
                    <div class="truncate">
                        <div class="font-bold text-xs truncate group-hover:text-[#111111]">{{ auth()->user()->name ?? 'Administrator' }}</div>
                        <div class="text-[10px] text-[#808080] font-mono truncate flex items-center gap-1">
                            <span>Profile & 2FA</span>
                            <span class="text-[8px]">→</span>
                        </div>
                    </div>
                </a>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" title="Sign Out" class="p-1.5 text-[#808080] hover:text-[#111111] hover:bg-[#F5F1E8] transition-colors flex-shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="lg:pl-64 flex-1 flex flex-col min-w-0 w-full">
        <!-- Top Navbar -->
        <header class="h-16 bg-white border-b border-[#111111]/15 px-4 sm:px-6 lg:px-8 flex items-center justify-between sticky top-0 z-30">
            <div class="flex items-center gap-3 min-w-0">
                <!-- Hamburger Button on Mobile / Tablet -->
                <button type="button" @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-2 -ml-2 text-[#111111] hover:bg-[#F5F1E8] transition-colors focus:outline-none" aria-label="Toggle navigation menu">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <h2 class="text-sm sm:text-base font-bold tracking-tight truncate">@yield('header_title', 'Overview')</h2>
                <div class="hidden sm:inline-flex">@yield('header_badge')</div>
            </div>

            <div class="flex items-center gap-2 sm:gap-4 shrink-0">
                <a href="{{ route('admin.profile.index') }}" class="text-xs font-mono text-[#808080] hover:text-[#111111] flex items-center gap-1.5 transition-colors {{ request()->routeIs('admin.profile.*') || request()->routeIs('admin.users.profile*') ? 'text-[#111111] font-bold' : '' }}" title="Account Profile & 2FA Security">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <span class="hidden sm:inline">Profile & 2FA</span>
                </a>
                <span class="text-[#111111]/20">|</span>
                <a href="{{ route('home') }}" target="_blank" class="text-xs font-mono text-[#808080] hover:text-[#111111] flex items-center gap-1 transition-colors" title="Live Public Site">
                    <span class="hidden sm:inline">Live Public Site</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                </a>
            </div>
        </header>

        <!-- Alerts -->
        @if (session('success'))
            <div class="mx-4 sm:mx-6 lg:mx-8 mt-4 sm:mt-6 p-3 sm:p-4 bg-emerald-50 border border-emerald-300 text-emerald-900 text-xs font-mono flex items-center justify-between">
                <span>{{ session('success') }}</span>
                <button onclick="this.parentElement.remove()" class="text-emerald-700 hover:text-emerald-900 ml-2">&times;</button>
            </div>
        @endif

        @if ($errors->any())
            <div class="mx-4 sm:mx-6 lg:mx-8 mt-4 sm:mt-6 p-3 sm:p-4 bg-rose-50 border border-rose-300 text-rose-900 text-xs font-mono">
                <div class="font-bold mb-1">Please correct the following errors:</div>
                <ul class="list-disc pl-4 space-y-0.5">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Body Content -->
        <main class="p-4 sm:p-6 lg:p-8 flex-1 w-full max-w-full overflow-x-hidden">
            @yield('content')
        </main>
    </div>

    {{-- Admin Console Timezone Auto-Converter (PRD-ADDNEW 5.5) --}}
    <script>
        (function() {
            try {
                var adminTz = "{{ auth()->user()->timezone ?? $currentTimezone ?? 'UTC' }}";
                document.querySelectorAll('.local-time, [data-timestamp]').forEach(function(el) {
                    var iso = el.getAttribute('data-timestamp') || el.getAttribute('datetime');
                    if (!iso) return;
                    var d = new Date(iso);
                    if (isNaN(d.getTime())) return;

                    var options = { timeZone: adminTz, year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' };
                    if (el.hasAttribute('data-time-only')) {
                        el.textContent = d.toLocaleTimeString([], { timeZone: adminTz, hour: '2-digit', minute: '2-digit' });
                    } else if (el.hasAttribute('data-date-only')) {
                        el.textContent = d.toLocaleDateString([], { timeZone: adminTz, year: 'numeric', month: 'short', day: 'numeric' });
                    } else {
                        el.textContent = d.toLocaleString([], options);
                    }
                });
            } catch(e) {}
        })();
    </script>

    @stack('scripts')
</body>
</html>
