@extends('layouts.app')

@section('content')
<div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-10">

    {{-- HERO SECTION: Split-Screen (design.md: Text left, visual right) --}}
    @if ($heroArticle)
        <section class="border-b border-neutral/20 pb-12 sm:pb-16 mb-12 sm:mb-16 fade-in-up">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10 items-center">
                {{-- Left: Text & Metadata --}}
                <div class="lg:col-span-6 space-y-4 sm:space-y-5">
                    <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                        <span class="text-[11px] font-mono font-semibold uppercase tracking-widest px-2.5 py-1 bg-primary text-secondary">
                            {{ $heroArticle->category->name }}
                        </span>
                        <span class="text-xs font-mono text-neutral">
                            {{ $heroArticle->published_at ? $heroArticle->published_at->format('M d, Y') : 'Featured' }} • {{ $heroArticle->reading_time }} min read
                        </span>
                    </div>

                    <h1 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-bold tracking-tight text-primary leading-[1.15]">
                        <a href="{{ route('article.show', $heroArticle->slug) }}" class="hover:underline underline-offset-4">
                            {{ $heroArticle->title }}
                        </a>
                    </h1>

                    <p class="text-sm sm:text-base text-neutral leading-relaxed max-w-xl">
                        {{ $heroArticle->excerpt ?: Str::limit(strip_tags($heroArticle->content), 200) }}
                    </p>

                    <div class="pt-2 flex flex-wrap items-center gap-4">
                        <a href="{{ route('article.show', $heroArticle->slug) }}" class="btn-primary">
                            Read Analysis →
                        </a>
                        <span class="text-xs font-mono text-neutral">
                            By {{ $heroArticle->author->name }}
                        </span>
                    </div>
                </div>

                {{-- Right: Visual / Image with Sharp Swiss Border --}}
                <div class="lg:col-span-6">
                    <a href="{{ route('article.show', $heroArticle->slug) }}" class="block border border-neutral/30 overflow-hidden group">
                        <img src="{{ $heroArticle->featured_image ?: 'https://picsum.photos/seed/'.$heroArticle->slug.'/800/500' }}"
                             alt="{{ $heroArticle->title }}"
                             class="w-full h-56 sm:h-72 md:h-80 lg:h-96 object-cover grayscale-[20%] group-hover:grayscale-0 group-hover:scale-105 transition-all duration-300" />
                    </a>
                </div>
            </div>
        </section>
    @endif

    {{-- TOPICS BAR --}}
    <section class="mb-12 sm:mb-16">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-neutral/20 pb-3 mb-6">
            <span class="text-xs font-mono uppercase tracking-widest text-neutral font-semibold">Core Knowledge Verticals</span>
            <span class="text-xs font-mono text-neutral">Strict Editorial Independence</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach ($categories as $cat)
                <div class="swiss-card p-5 bg-secondary flex flex-col gap-3">
                    {{-- Category Header --}}
                    <div>
                        <div class="text-[10px] font-mono text-neutral uppercase tracking-widest mb-1">Vertical {{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</div>
                        <a href="{{ route('category.show', $cat->slug) }}" class="font-bold text-base text-primary hover:underline leading-snug block">
                            {{ $cat->name }}
                        </a>
                        <div class="text-xs font-mono text-neutral mt-1.5">
                            {{ $cat->articles_count }} {{ Str::plural('analysis', $cat->articles_count) }}
                        </div>
                    </div>

                    {{-- Subcategories as pill links --}}
                    @if ($cat->children->isNotEmpty())
                        <div class="flex flex-wrap gap-1.5 pt-1 border-t border-neutral/15">
                            @foreach ($cat->children as $sub)
                                <a href="{{ route('category.show', $sub->slug) }}"
                                   class="inline-flex items-center gap-1 px-2 py-0.5 text-[10px] font-mono
                                          border border-neutral/25 text-neutral hover:border-primary hover:text-primary
                                          transition-colors leading-none">
                                    <span class="w-1 h-1 bg-neutral/40 inline-block"></span>
                                    {{ $sub->name }}
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </section>

    {{-- ZIG-ZAG FEATURED SECTION (design.md: Zig-zag alternating text+image rows) --}}
    @if ($featuredArticles->isNotEmpty())
        <section class="border-b border-neutral/20 pb-12 sm:pb-16 mb-12 sm:mb-16 space-y-8 sm:space-y-12">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-neutral/20 pb-3">
                <span class="text-xs font-mono uppercase tracking-widest text-primary font-semibold">Featured Investigations</span>
                <span class="text-xs font-mono text-neutral">Deep Architectural Insights</span>
            </div>

            @foreach ($featuredArticles as $idx => $fArt)
                <div class="grid grid-cols-1 md:grid-cols-12 gap-6 sm:gap-8 items-center {{ $idx % 2 === 1 ? 'md:flex-row-reverse' : '' }}">
                    {{-- Image (Alternate Order) --}}
                    <div class="md:col-span-5 {{ $idx % 2 === 1 ? 'md:order-2' : 'md:order-1' }}">
                        <a href="{{ route('article.show', $fArt->slug) }}" class="block border border-neutral/20 overflow-hidden">
                            <img src="{{ $fArt->featured_image ?: 'https://picsum.photos/seed/'.$fArt->slug.'/600/400' }}"
                                 alt="{{ $fArt->title }}"
                                 class="w-full h-48 sm:h-56 object-cover hover:scale-105 transition-transform duration-300"
                                 loading="lazy" />
                        </a>
                    </div>

                    {{-- Text Content --}}
                    <div class="md:col-span-7 space-y-3 {{ $idx % 2 === 1 ? 'md:order-1' : 'md:order-2' }}">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-[10px] font-mono uppercase tracking-widest px-2 py-0.5 bg-tertiary text-primary border border-neutral/20">
                                {{ $fArt->category->name }}
                            </span>
                            <span class="text-xs font-mono text-neutral">
                                {{ $fArt->reading_time }} min read
                            </span>
                        </div>

                        <h2 class="text-xl sm:text-2xl font-bold text-primary leading-tight">
                            <a href="{{ route('article.show', $fArt->slug) }}" class="hover:underline">
                                {{ $fArt->title }}
                            </a>
                        </h2>

                        <p class="text-sm text-neutral leading-relaxed">
                            {{ $fArt->excerpt ?: Str::limit(strip_tags($fArt->content), 150) }}
                        </p>

                        <div>
                            <a href="{{ route('article.show', $fArt->slug) }}" class="text-xs font-mono font-semibold text-primary hover:underline">
                                Read Breakdown →
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </section>
    @endif

    {{-- DUAL COLUMN: Latest Stream + Trending & Monetization Sidebar --}}
    <section class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start">
        {{-- Left: Latest Articles Stream --}}
        <div class="lg:col-span-8 space-y-6 sm:space-y-8">
            <div class="flex items-center justify-between border-b border-neutral/20 pb-3">
                <span class="text-xs font-mono uppercase tracking-widest text-primary font-semibold">Latest Dispatches</span>
                <span class="text-xs font-mono text-neutral">Chronological Feed</span>
            </div>

            <div class="space-y-4 sm:space-y-6">
                @foreach ($latestArticles as $lArt)
                    <article class="swiss-card p-4 sm:p-6 flex flex-col sm:flex-row gap-4 sm:gap-6 items-start bg-secondary">
                        <a href="{{ route('article.show', $lArt->slug) }}" class="w-full sm:w-48 h-44 sm:h-32 border border-neutral/20 shrink-0 overflow-hidden block">
                            <img src="{{ $lArt->featured_image ?: 'https://picsum.photos/seed/'.$lArt->slug.'/400/250' }}"
                                 alt="{{ $lArt->title }}"
                                 class="w-full h-full object-cover hover:scale-105 transition-transform duration-300"
                                 loading="lazy" />
                        </a>

                        <div class="flex-1 space-y-2 w-full">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="text-[10px] font-mono uppercase tracking-widest text-neutral">
                                    {{ $lArt->category->name }}
                                </span>
                                <span class="text-xs font-mono text-neutral">• {{ $lArt->reading_time }} min read</span>
                            </div>

                            <h3 class="text-base sm:text-lg font-bold text-primary leading-snug">
                                <a href="{{ route('article.show', $lArt->slug) }}" class="hover:underline">
                                    {{ $lArt->title }}
                                </a>
                            </h3>

                            <p class="text-xs text-neutral leading-relaxed">
                                {{ $lArt->excerpt ?: Str::limit(strip_tags($lArt->content), 120) }}
                            </p>

                            <div class="pt-1 text-[11px] font-mono text-neutral flex items-center justify-between">
                                <span>{{ $lArt->published_at ? $lArt->published_at->format('M d, Y') : '' }}</span>
                                <a href="{{ route('article.show', $lArt->slug) }}" class="font-semibold text-primary hover:underline">
                                    Full Text →
                                </a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>

        {{-- Right: Sidebar (Trending + Sidebar Ad + Newsletter) --}}
        <div class="lg:col-span-4 space-y-6 sm:space-y-8">
            {{-- Trending Box --}}
            <div class="border border-neutral/25 p-5 sm:p-6 bg-secondary">
                <h3 class="text-xs font-mono uppercase tracking-widest text-primary font-bold border-b border-neutral/20 pb-3 mb-4">
                    High Engagement
                </h3>
                <div class="space-y-4">
                    @foreach ($trendingArticles as $tArt)
                        <div class="flex items-start gap-3">
                            <span class="text-base font-mono font-bold text-neutral/40">0{{ $loop->iteration }}</span>
                            <div>
                                <a href="{{ route('article.show', $tArt->slug) }}" class="text-xs font-bold text-primary hover:underline leading-snug block">
                                    {{ $tArt->title }}
                                </a>
                                <span class="text-[10px] font-mono text-neutral mt-0.5 block">
                                    {{ $tArt->category->name }} • {{ number_format($tArt->view_count) }} views
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Sidebar Ad Slot --}}
            {!! \App\Services\AdService::render('sidebar') !!}

            {{-- Newsletter Box --}}
            <div class="p-5 sm:p-6 bg-tertiary border border-neutral/30">
                <span class="text-[10px] font-mono uppercase tracking-widest text-neutral block mb-1">Intelligence Dispatch</span>
                <h4 class="font-bold text-base text-primary mb-2">Subscribe to SecuroFi Briefing</h4>
                <p class="text-xs text-neutral leading-relaxed mb-4">
                    A weekly digest of non-sponsored software benchmarks, cybersecurity warnings, and fintech infrastructure.
                </p>

                @if (session('newsletter_success'))
                    <div class="p-2.5 bg-primary text-secondary text-xs font-mono mb-3">
                        {{ session('newsletter_success') }}
                    </div>
                @endif

                <form action="{{ route('newsletter.subscribe') }}" method="POST" class="space-y-3">
                    @csrf
                    <input type="email" name="email" required placeholder="name@company.com"
                        class="w-full px-3 py-2 text-xs font-mono bg-white border border-neutral/30 focus:outline-none focus:border-primary">
                    @php $captchaService = app(\App\Services\CaptchaService::class); @endphp
                    @if ($captchaService->isEnabledForForm('newsletter'))
                        <div>
                            {!! $captchaService->widgetHtml('newsletter', 'subscribe') !!}
                            @error('captcha')
                                <p class="text-xs font-mono text-rose-700 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif
                    <button type="submit" class="btn-primary w-full text-xs font-mono py-2.5">
                        Subscribe Free →
                    </button>
                </form>
            </div>
        </div>
    </section>

</div>
@endsection

