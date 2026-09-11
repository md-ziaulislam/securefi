@extends('layouts.admin')

@section('title', 'RSS / Atom Feed Management')
@section('header_title', 'RSS & Atom Feed Engine')

@section('content')
<div class="space-y-6 sm:space-y-8">

    {{-- Top Metrics & Status Overview --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        <div class="bg-white border border-[#111111]/15 p-4 sm:p-5">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[10px] font-mono text-[#808080] uppercase tracking-wider">Feed Visibility</span>
                <span class="px-2 py-0.5 text-[9px] font-mono font-bold uppercase {{ ($settings['feed_enabled'] ?? '1') === '1' ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-rose-100 text-rose-800 border border-rose-300' }}">
                    {{ ($settings['feed_enabled'] ?? '1') === '1' ? 'Active' : 'Disabled' }}
                </span>
            </div>
            <div class="text-base sm:text-lg font-bold font-mono text-[#111111]">
                {{ ($settings['feed_enabled'] ?? '1') === '1' ? 'Publicly Served' : '404 Inactive' }}
            </div>
            <div class="text-[11px] font-mono text-[#808080] mt-1">/feed & /category/{slug}/feed</div>
        </div>

        <div class="bg-white border border-[#111111]/15 p-4 sm:p-5">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[10px] font-mono text-[#808080] uppercase tracking-wider">Indexed Pool</span>
                <span class="px-2 py-0.5 text-[9px] font-mono font-bold uppercase bg-blue-100 text-blue-800 border border-blue-200">
                    Live
                </span>
            </div>
            <div class="text-2xl font-bold font-mono text-[#111111]">{{ number_format($publishedCount ?? 0) }}</div>
            <div class="text-[11px] font-mono text-[#808080] mt-1">Published Articles Ready</div>
        </div>

        <div class="bg-white border border-[#111111]/15 p-4 sm:p-5">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[10px] font-mono text-[#808080] uppercase tracking-wider">Items Cap</span>
                <span class="px-2 py-0.5 text-[9px] font-mono font-bold uppercase bg-neutral-100 text-neutral-800 border border-neutral-300">
                    Limit
                </span>
            </div>
            <div class="text-2xl font-bold font-mono text-[#111111]">{{ $settings['feed_items_count'] ?? '20' }}</div>
            <div class="text-[11px] font-mono text-[#808080] mt-1">Max Articles per Request</div>
        </div>

        <div class="bg-white border border-[#111111]/15 p-4 sm:p-5">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[10px] font-mono text-[#808080] uppercase tracking-wider">Formats Supported</span>
                <span class="px-2 py-0.5 text-[9px] font-mono font-bold uppercase bg-purple-100 text-purple-800 border border-purple-200">
                    2 Protocols
                </span>
            </div>
            <div class="text-base sm:text-lg font-bold font-mono text-[#111111]">RSS 2.0 & Atom 1.0</div>
            <div class="text-[11px] font-mono text-[#808080] mt-1">XML Specification Compliant</div>
        </div>
    </div>

    {{-- Main Grid: Settings & Endpoints --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 sm:gap-8 items-start">

        {{-- Left: Feed Settings Form (7 cols) --}}
        <div class="lg:col-span-7 space-y-6">

            <form action="{{ route('admin.feed.update') }}" method="POST" class="space-y-6">
                @csrf

                {{-- Card 1: Visibility & Basic Behavior --}}
                <div class="bg-white border border-[#111111]/15 p-5 sm:p-6 space-y-5">
                    <div class="border-b border-[#111111]/10 pb-3">
                        <h3 class="font-bold text-sm text-[#111111]">1. Visibility & Delivery Controls</h3>
                        <p class="text-xs font-mono text-[#808080]">Enable or restrict public syndication endpoints.</p>
                    </div>

                    {{-- Enable Toggle --}}
                    <div class="p-4 bg-[#F8F8F6] border border-[#111111]/10">
                        <label class="flex items-start sm:items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="feed_enabled" value="1"
                                {{ ($settings['feed_enabled'] ?? '1') === '1' ? 'checked' : '' }}
                                class="w-4 h-4 rounded-none border-[#111111] text-[#111111] mt-0.5 sm:mt-0">
                            <div>
                                <span class="text-xs font-mono font-bold text-[#111111] block">Enable Public RSS / Atom Feeds</span>
                                <span class="text-[11px] font-mono text-[#808080] block mt-0.5">
                                    When disabled, <code>/feed</code> and category feeds immediately return 404.
                                </span>
                            </div>
                        </label>
                    </div>

                    {{-- Items Count --}}
                    <div>
                        <label for="feed_items_count" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1.5 font-medium">
                            Max Articles Per Feed (5–100) *
                        </label>
                        <input type="number" id="feed_items_count" name="feed_items_count" min="5" max="100"
                            value="{{ old('feed_items_count', $settings['feed_items_count'] ?? '20') }}" required
                            class="w-full px-3.5 py-2.5 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                        <p class="text-[11px] font-mono text-[#808080] mt-1.5">Recommended: 20–30 articles for optimal feed reader performance.</p>
                    </div>

                    {{-- Content Mode --}}
                    <div class="space-y-3 pt-3 border-t border-[#111111]/10">
                        <p class="text-xs font-mono font-bold text-[#111111] uppercase tracking-wider">Feed Content Inclusions:</p>

                        <label class="flex items-start gap-2.5 cursor-pointer">
                            <input type="checkbox" name="feed_show_excerpt" value="1"
                                {{ ($settings['feed_show_excerpt'] ?? '1') === '1' ? 'checked' : '' }}
                                class="w-4 h-4 rounded-none border-[#111111] text-[#111111] mt-0.5">
                            <div>
                                <span class="text-xs font-mono text-[#111111] font-medium block">Include Article Excerpt / Summary</span>
                                <span class="text-[10px] font-mono text-[#808080]">Outputs <code>&lt;description&gt;</code> in RSS and <code>&lt;summary&gt;</code> in Atom.</span>
                            </div>
                        </label>

                        <label class="flex items-start gap-2.5 cursor-pointer">
                            <input type="checkbox" name="feed_show_fulltext" value="1"
                                {{ ($settings['feed_show_fulltext'] ?? '0') === '1' ? 'checked' : '' }}
                                class="w-4 h-4 rounded-none border-[#111111] text-[#111111] font-medium block mt-0.5">
                            <div>
                                <span class="text-xs font-mono text-[#111111] font-medium block">Include Full Article HTML (Full-Text Syndication)</span>
                                <span class="text-[10px] font-mono text-[#808080]">Injects <code>&lt;content:encoded&gt;</code> so RSS readers like Feedly can render complete articles inline.</span>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- Card 2: Feed Metadata Customization --}}
                <div class="bg-white border border-[#111111]/15 p-5 sm:p-6 space-y-4">
                    <div class="border-b border-[#111111]/10 pb-3">
                        <h3 class="font-bold text-sm text-[#111111]">2. Feed Channel Metadata</h3>
                        <p class="text-xs font-mono text-[#808080]">Custom title and descriptions for aggregators. Leave blank to inherit site defaults.</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="feed_title" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1 font-medium">
                                Custom Feed Title
                            </label>
                            <input type="text" id="feed_title" name="feed_title"
                                value="{{ old('feed_title', $settings['feed_title'] ?? '') }}"
                                placeholder="{{ $settings['site_name'] ?? 'SecuroFi.Tech' }}"
                                class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                        </div>

                        <div>
                            <label for="feed_language" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1 font-medium">
                                Feed Language Code
                            </label>
                            <input type="text" id="feed_language" name="feed_language"
                                value="{{ old('feed_language', $settings['feed_language'] ?? 'en-us') }}"
                                placeholder="en-us"
                                class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                        </div>
                    </div>

                    <div>
                        <label for="feed_description" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1 font-medium">
                            Feed Channel Subtitle / Description
                        </label>
                        <textarea id="feed_description" name="feed_description" rows="2"
                            placeholder="{{ $settings['site_description'] ?? 'Cybersecurity, Tech & Fintech intelligence for digital defenders.' }}"
                            class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">{{ old('feed_description', $settings['feed_description'] ?? '') }}</textarea>
                    </div>

                    <div>
                        <label for="feed_copyright" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1 font-medium">
                            Copyright Notice
                        </label>
                        <input type="text" id="feed_copyright" name="feed_copyright"
                            value="{{ old('feed_copyright', $settings['feed_copyright'] ?? ('© ' . date('Y') . ' ' . ($settings['site_name'] ?? 'SecuroFi.Tech') . '. All Rights Reserved.')) }}"
                            class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]"
                            placeholder="© {{ date('Y') }} SecuroFi.Tech. All Rights Reserved.">
                    </div>
                </div>

                {{-- Submit Button --}}
                <div class="p-4 bg-white border border-[#111111]/15">
                    <button type="submit" class="btn-primary w-full text-xs font-mono uppercase tracking-wider py-3.5 text-center">
                        Save Feed Configuration →
                    </button>
                </div>

            </form>
        </div>

        {{-- Right: Live Feed Endpoints, Category Selector & Live Inspector (5 cols) --}}
        <div class="lg:col-span-5 space-y-6">

            {{-- Live Global Endpoints --}}
            <div class="bg-white border border-[#111111]/15 p-5 sm:p-6 space-y-4">
                <div class="border-b border-[#111111]/10 pb-3">
                    <h3 class="font-bold text-sm text-[#111111]">Live Public Feed Endpoints</h3>
                    <p class="text-xs font-mono text-[#808080]">Direct URLs for news readers, podcasts, and aggregators.</p>
                </div>

                <div class="space-y-3">
                    {{-- Global RSS 2.0 --}}
                    <div class="p-3.5 bg-[#F8F8F6] border border-[#111111]/10 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-mono font-bold text-[#111111] uppercase tracking-wider">Global RSS 2.0</span>
                            <span class="text-[9px] font-mono text-emerald-800 bg-emerald-100 px-1.5 py-0.5">Primary</span>
                        </div>
                        <div class="bg-white p-2 border border-[#111111]/15 text-xs font-mono text-[#111111] break-all select-all">
                            {{ route('feed.index') }}
                        </div>
                        <div class="flex items-center gap-2 pt-1">
                            <button type="button" onclick="copyToClipboard('{{ route('feed.index') }}', this)"
                                class="flex-1 text-center text-xs font-mono py-1.5 px-3 border border-[#111111] hover:bg-[#F5F1E8] transition-colors">
                                Copy URL
                            </button>
                            <a href="{{ route('feed.index') }}" target="_blank"
                                class="text-center text-xs font-mono py-1.5 px-4 bg-[#111111] text-white hover:opacity-85 transition-opacity">
                                Test Feed →
                            </a>
                        </div>
                    </div>

                    {{-- Global Atom 1.0 --}}
                    <div class="p-3.5 bg-[#F8F8F6] border border-[#111111]/10 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-mono font-bold text-[#111111] uppercase tracking-wider">Global Atom 1.0</span>
                            <span class="text-[9px] font-mono text-purple-800 bg-purple-100 px-1.5 py-0.5">RFC 4287</span>
                        </div>
                        <div class="bg-white p-2 border border-[#111111]/15 text-xs font-mono text-[#111111] break-all select-all">
                            {{ route('feed.index', ['format' => 'atom']) }}
                        </div>
                        <div class="flex items-center gap-2 pt-1">
                            <button type="button" onclick="copyToClipboard('{{ route('feed.index', ['format' => 'atom']) }}', this)"
                                class="flex-1 text-center text-xs font-mono py-1.5 px-3 border border-[#111111] hover:bg-[#F5F1E8] transition-colors">
                                Copy URL
                            </button>
                            <a href="{{ route('feed.index', ['format' => 'atom']) }}" target="_blank"
                                class="text-center text-xs font-mono py-1.5 px-4 bg-[#111111] text-white hover:opacity-85 transition-opacity">
                                Test Feed →
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Category Feed Live Selector --}}
            <div class="bg-white border border-[#111111]/15 p-5 sm:p-6 space-y-4" x-data="{ selectedSlug: '{{ $categories->first()?->slug ?? '' }}' }">
                <div class="border-b border-[#111111]/10 pb-3">
                    <h3 class="font-bold text-sm text-[#111111]">Per-Category Feeds</h3>
                    <p class="text-xs font-mono text-[#808080]">Deliver targeted feeds for niche readers.</p>
                </div>

                @if ($categories->isNotEmpty())
                    <div>
                        <label for="category_feed_picker" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1 font-medium">
                            Select Category to Test:
                        </label>
                        <select id="category_feed_picker" x-model="selectedSlug"
                            class="w-full px-3 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->slug }}">{{ $cat->name }} ({{ $cat->articles_count ?? $cat->articles()->count() }} articles)</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="p-3.5 bg-[#F8F8F6] border border-[#111111]/10 space-y-2">
                        <div class="text-[10px] font-mono text-[#808080] uppercase tracking-wider">Dynamic Category RSS Endpoint:</div>
                        <div class="bg-white p-2 border border-[#111111]/15 text-xs font-mono text-[#111111] break-all"
                             x-text="'{{ url('/category') }}/' + selectedSlug + '/feed'">
                        </div>
                        <div class="flex items-center gap-2 pt-1">
                            <button type="button" @click="copyToClipboard('{{ url('/category') }}/' + selectedSlug + '/feed', $el)"
                                class="flex-1 text-center text-xs font-mono py-1.5 px-3 border border-[#111111] hover:bg-[#F5F1E8] transition-colors">
                                Copy Category RSS
                            </button>
                            <a :href="'{{ url('/category') }}/' + selectedSlug + '/feed'" target="_blank"
                                class="text-center text-xs font-mono py-1.5 px-3 bg-[#111111] text-white hover:opacity-85 transition-opacity">
                                Test Category →
                            </a>
                        </div>
                    </div>
                @else
                    <div class="p-4 text-center text-xs font-mono text-[#808080] bg-[#F8F8F6] border border-[#111111]/10">
                        No active categories configured yet.
                    </div>
                @endif
            </div>

            {{-- Live Feed Inspector --}}
            <div class="bg-white border border-[#111111]/15 p-5 sm:p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-[#111111]/10 pb-3">
                    <div>
                        <h3 class="font-bold text-sm text-[#111111]">Recent Feed Items Inspector</h3>
                        <p class="text-xs font-mono text-[#808080]">The top 5 articles currently served in the live XML stream.</p>
                    </div>
                    <span class="text-[10px] font-mono px-2 py-0.5 bg-neutral-100 text-[#111111] border border-[#111111]/15">
                        Live Stream
                    </span>
                </div>

                <div class="space-y-3">
                    @forelse ($recentArticles as $art)
                        <div class="p-3 bg-[#F8F8F6] border border-[#111111]/10 space-y-1">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-[10px] font-mono text-emerald-800 font-bold uppercase">
                                    {{ $art->category->name ?? 'Uncategorized' }}
                                </span>
                                <span class="text-[10px] font-mono text-[#808080]">
                                    {{ $art->published_at ? $art->published_at->format('M d, Y') : 'Draft' }}
                                </span>
                            </div>
                            <h4 class="text-xs font-bold text-[#111111] line-clamp-1" title="{{ $art->title }}">
                                {{ $art->title }}
                            </h4>
                            <p class="text-[11px] font-mono text-[#808080] line-clamp-2">
                                {{ $art->excerpt ?: Str::limit(strip_tags($art->content), 120) }}
                            </p>
                        </div>
                    @empty
                        <div class="p-6 text-center text-xs font-mono text-[#808080]">
                            No published articles available to stream in feed.
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Auto-Discovery & Reader Syndication Tips --}}
            <div class="bg-white border border-[#111111]/15 p-5 sm:p-6 space-y-3">
                <div class="border-b border-[#111111]/10 pb-3">
                    <h3 class="font-bold text-sm text-[#111111]">Auto-Discovery & Syndication</h3>
                    <p class="text-xs font-mono text-[#808080]">Browser & bot integration hints.</p>
                </div>

                <div class="text-xs font-mono text-[#808080] space-y-2">
                    <p>• Feed auto-discovery <code>&lt;link rel="alternate"&gt;</code> tags are automatically injected in the public <code>&lt;head&gt;</code> when enabled.</p>
                    <p>• Compatible with all modern RSS feed readers: <strong>Feedly, NewsBlur, Inoreader, Apple News, and Flipboard</strong>.</p>
                    <p>• Used by search bots & AI crawlers (Perplexity, ChatGPT, Claude) for real-time article discovery.</p>
                </div>
            </div>

        </div>

    </div>

</div>

@push('scripts')
<script>
    function copyToClipboard(text, btn) {
        if (!navigator.clipboard) {
            const temp = document.createElement('input');
            temp.value = text;
            document.body.appendChild(temp);
            temp.select();
            document.execCommand('copy');
            document.body.removeChild(temp);
        } else {
            navigator.clipboard.writeText(text);
        }

        const originalText = btn.innerText;
        btn.innerText = 'Copied!';
        btn.classList.add('bg-[#111111]', 'text-white');
        setTimeout(function() {
            btn.innerText = originalText;
            btn.classList.remove('bg-[#111111]', 'text-white');
        }, 2000);
    }
</script>
@endpush
@endsection
