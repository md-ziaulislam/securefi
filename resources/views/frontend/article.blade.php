@extends('layouts.app')

@section('content')
<div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-10">

    {{-- Breadcrumb Navigation --}}
    <nav class="flex items-center gap-2 text-xs font-mono text-neutral mb-6 sm:mb-8 border-b border-neutral/15 pb-4 overflow-x-auto whitespace-nowrap no-scrollbar">
        <a href="{{ route('home') }}" class="hover:text-primary shrink-0">Home</a>
        <span>/</span>
        <a href="{{ route('category.show', $article->category->slug) }}" class="hover:text-primary shrink-0">{{ $article->category->name }}</a>
        <span>/</span>
        <span class="text-primary truncate max-w-[200px] sm:max-w-sm">{{ $article->title }}</span>
    </nav>

    {{-- Article Header --}}
    <header class="max-w-4xl mb-10 space-y-4">
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('category.show', $article->category->slug) }}" class="text-[11px] font-mono font-semibold uppercase tracking-widest px-2.5 py-1 bg-primary text-secondary">
                {{ $article->category->name }}
            </a>
            @if($article->published_at)
                <time datetime="{{ $article->published_at->toIso8601String() }}" data-date-only="true" class="local-time text-xs font-mono text-neutral">
                    @localtime($article->published_at, 'F d, Y')
                </time>
            @else
                <span class="text-xs font-mono text-neutral">Draft</span>
            @endif
            <span class="text-neutral">•</span>
            <span class="text-xs font-mono text-neutral">{{ $article->reading_time }} min read</span>
            <span class="text-neutral">•</span>
            <span class="text-xs font-mono text-neutral">{{ number_format($article->view_count) }} views</span>
        </div>

        <h1 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-bold tracking-tight text-primary leading-[1.2]">
            {{ $article->title }}
        </h1>

        @if ($article->excerpt)
            <p class="text-lg text-neutral leading-relaxed">
                {{ $article->excerpt }}
            </p>
        @endif

        {{-- Author Signature --}}
        <div class="pt-4 flex items-center gap-3 border-t border-neutral/15">
            <div class="w-10 h-10 rounded-full bg-tertiary border border-neutral/30 flex items-center justify-center font-bold text-sm font-mono text-primary shrink-0">
                {{ substr($article->author->name, 0, 2) }}
            </div>
            <div>
                <div class="text-xs font-bold text-primary">{{ $article->author->name }}</div>
                <div class="text-[11px] font-mono text-neutral">{{ $article->author->bio ?? 'Lead Security Architect & Technical Writer' }}</div>
            </div>
        </div>
    </header>

    {{-- Featured Hero Image --}}
    @if ($article->featured_image)
        <div class="mb-12 border border-neutral/20 overflow-hidden max-w-5xl">
            <img src="{{ $article->featured_image }}" alt="{{ $article->title }}" class="w-full h-auto max-h-[520px] object-cover" />
        </div>
    @endif

    {{-- Top In-Article Ad Slot --}}
    {!! \App\Services\AdService::render('in_article_top', $article->category_id) !!}

    {{-- Layout with Content + Sidebar (TOC) --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start max-w-6xl">
        {{-- Main Article Content Column (Max 72ch per design.md) --}}
        <div class="lg:col-span-8">
            {{-- Mobile / Inline Table of Contents --}}
            @if (!empty($toc))
                <div class="p-6 bg-tertiary/50 border border-neutral/25 mb-8">
                    <div class="text-xs font-mono uppercase tracking-widest text-primary font-bold mb-3">
                        Table of Contents
                    </div>
                    <ul class="space-y-1.5 text-xs font-mono text-neutral">
                        @foreach ($toc as $item)
                            <li>
                                <a href="#{{ $item['anchor'] }}" class="hover:text-primary hover:underline transition-colors">
                                    → {{ $item['title'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Rich Article Body Content --}}
            <article class="prose max-w-[72ch] text-base leading-[1.7] text-primary/90 space-y-6">
                {!! $processedContent !!}
            </article>

            {{-- Middle In-Article Ad Slot --}}
            {!! \App\Services\AdService::render('in_article_middle', $article->category_id) !!}

            {{-- Bottom In-Article Ad Slot --}}
            {!! \App\Services\AdService::render('in_article_bottom', $article->category_id) !!}

            {{-- Category Affiliate Recommendation Widget --}}
            {!! \App\Services\ShortcodeParser::renderCategoryAffiliateBox($article->category_id) !!}

            {{-- Tags & Social Sharing --}}
            <div class="mt-12 pt-8 border-t border-neutral/20 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
                {{-- Tags --}}
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-xs font-mono text-neutral">Tags:</span>
                    @foreach ($article->tags as $tag)
                        <a href="{{ route('tag.show', $tag->slug) }}" class="px-2.5 py-1 text-xs font-mono bg-tertiary text-primary hover:bg-neutral/20 transition-colors border border-neutral/20">
                            #{{ $tag->name }}
                        </a>
                    @endforeach
                </div>

                {{-- Social Share --}}
                <div class="flex items-center gap-3">
                    <span class="text-xs font-mono text-neutral">Share:</span>
                    <a href="https://twitter.com/intent/tweet?text={{ urlencode($article->title) }}&url={{ urlencode(url()->current()) }}" target="_blank" rel="noopener" class="p-2 border border-neutral/30 hover:bg-tertiary transition-colors" title="Share on X">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                    </a>
                    <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode(url()->current()) }}" target="_blank" rel="noopener" class="p-2 border border-neutral/30 hover:bg-tertiary transition-colors" title="Share on LinkedIn">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M19 3a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h14m-.5 15.5v-5.3a3.26 3.26 0 0 0-3.26-3.26c-.85 0-1.84.52-2.28 1.3v-1.11h-2.79v8.37h2.79v-4.93c0-.77.62-1.4 1.39-1.4a1.4 1.4 0 0 1 1.4 1.4v4.93h2.75M6.46 10.9v8.37H9.25V10.9H6.46M7.86 6.55a1.62 1.62 0 1 0 0 3.24 1.62 1.62 0 0 0 0-3.24z"/></svg>
                    </a>
                    <button onclick="navigator.clipboard.writeText(window.location.href); alert('Article URL copied to clipboard.');" class="p-2 border border-neutral/30 hover:bg-tertiary transition-colors" title="Copy Link">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                    </button>
                </div>
            </div>

            {{-- Discussion & Comments Section --}}
            <section id="comments" class="mt-12 pt-8 border-t border-neutral/20" x-data="{
                replyToId: null,
                replyToName: '',
                setReply(id, name) {
                    this.replyToId = id;
                    this.replyToName = name;
                    $nextTick(() => {
                        const form = document.getElementById('comment-form');
                        if (form) {
                            form.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            document.getElementById('comment-content')?.focus();
                        }
                    });
                },
                cancelReply() {
                    this.replyToId = null;
                    this.replyToName = '';
                }
            }">
                <div class="flex items-center justify-between border-b border-neutral/20 pb-3 mb-6">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-mono uppercase tracking-widest text-primary font-bold">
                            Discussion
                        </span>
                        <span class="text-xs font-mono px-2 py-0.5 bg-tertiary text-primary border border-neutral/20">
                            {{ $article->approvedComments->count() }}
                        </span>
                    </div>
                    <span class="text-[11px] font-mono text-neutral hidden sm:inline">
                        Technical discourse & analysis
                    </span>
                </div>

                {{-- Flash Success Message --}}
                @if (session('comment_success'))
                    <div class="mb-6 p-4 bg-tertiary border border-neutral/30 flex items-start gap-3">
                        <svg class="w-4 h-4 text-primary shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <div class="text-xs font-mono text-primary">
                            {{ session('comment_success') }}
                        </div>
                    </div>
                @endif

                {{-- Flash Error Message --}}
                @if (session('comment_error'))
                    <div class="mb-6 p-4 bg-tertiary border border-red-500/50 flex items-start gap-3">
                        <svg class="w-4 h-4 text-red-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="text-xs font-mono text-red-500">
                            {{ session('comment_error') }}
                        </div>
                    </div>
                @endif

                {{-- Validation Errors --}}
                @if ($errors->any())
                    <div class="mb-6 p-4 bg-tertiary border border-red-500/50">
                        <div class="text-xs font-mono font-bold text-red-500 mb-2">Submission Error:</div>
                        <ul class="text-xs font-mono text-neutral space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>• {{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Comment Form --}}
                <div id="comment-form" class="border border-neutral/20 p-5 bg-secondary mb-10">
                    {{-- Replying To Banner --}}
                    <div x-show="replyToId" x-cloak class="mb-4 p-2.5 bg-tertiary border border-neutral/30 flex items-center justify-between text-xs font-mono">
                        <div class="flex items-center gap-2">
                            <span class="text-neutral">Replying to:</span>
                            <span class="font-bold text-primary" x-text="replyToName"></span>
                        </div>
                        <button type="button" @click="cancelReply()" class="text-neutral hover:text-primary underline">
                            Cancel Reply
                        </button>
                    </div>

                    <form action="{{ route('article.comment.store', $article->slug) }}" method="POST">
                        @csrf
                        <input type="hidden" name="parent_id" :value="replyToId">

                        {{-- Spam honeypot (hidden from human readers) --}}
                        <div class="hidden" aria-hidden="true">
                            <input type="text" name="company_url" tabindex="-1" autocomplete="off" />
                        </div>

                        @auth
                            <div class="mb-4 flex items-center gap-3 p-3 bg-tertiary border border-neutral/20">
                                <div class="w-7 h-7 bg-primary text-secondary font-mono text-xs font-bold flex items-center justify-center shrink-0">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                                </div>
                                <div class="text-xs font-mono">
                                    <span class="text-neutral">Posting as:</span>
                                    <span class="font-bold text-primary">{{ auth()->user()->name }}</span>
                                    <span class="text-neutral text-[10px]">({{ auth()->user()->email }})</span>
                                </div>
                            </div>
                        @else
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-4">
                                <div>
                                    <label class="block text-[10px] font-mono uppercase tracking-widest text-neutral mb-1">
                                        Name <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="name" value="{{ old('name') }}" required
                                        class="w-full text-xs font-mono bg-tertiary border border-neutral/30 px-3 py-2 text-primary focus:outline-none focus:border-primary transition-colors"
                                        placeholder="Alex Vance" />
                                </div>
                                <div>
                                    <label class="block text-[10px] font-mono uppercase tracking-widest text-neutral mb-1">
                                        Email <span class="text-red-500">*</span> <span class="text-[9px] text-neutral/70">(private)</span>
                                    </label>
                                    <input type="email" name="email" value="{{ old('email') }}" required
                                        class="w-full text-xs font-mono bg-tertiary border border-neutral/30 px-3 py-2 text-primary focus:outline-none focus:border-primary transition-colors"
                                        placeholder="alex@example.com" />
                                </div>
                                <div>
                                    <label class="block text-[10px] font-mono uppercase tracking-widest text-neutral mb-1">
                                        Website <span class="text-[9px] text-neutral/70">(optional)</span>
                                    </label>
                                    <input type="url" name="website" value="{{ old('website') }}"
                                        class="w-full text-xs font-mono bg-tertiary border border-neutral/30 px-3 py-2 text-primary focus:outline-none focus:border-primary transition-colors"
                                        placeholder="https://domain.com" />
                                </div>
                            </div>
                        @endauth

                        <div class="mb-4">
                            <label class="block text-[10px] font-mono uppercase tracking-widest text-neutral mb-1">
                                Analysis / Response <span class="text-red-500">*</span>
                            </label>
                            <textarea id="comment-content" name="content" rows="4" required
                                class="w-full text-xs font-mono bg-tertiary border border-neutral/30 p-3 text-primary focus:outline-none focus:border-primary transition-colors"
                                placeholder="Share your technical perspectives, critiques, or counter-analyses...">{{ old('content') }}</textarea>
                        </div>

                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                            @php $captchaService = app(\App\Services\CaptchaService::class); @endphp
                            @if ($captchaService->isEnabledForForm('comment'))
                                <div class="w-full mb-2">
                                    {!! $captchaService->widgetHtml('comment', 'comment') !!}
                                    @error('captcha')
                                        <p class="text-xs font-mono text-rose-700 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            @else
                                <span class="text-[10px] font-mono text-neutral">
                                    Moderated for quality & security discourse.
                                </span>
                            @endif
                            <button type="submit" class="w-full sm:w-auto px-5 py-2 bg-primary text-secondary font-mono text-xs font-bold uppercase tracking-wider hover:opacity-90 transition-opacity text-center">
                                Post Comment →
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Existing Comments List --}}
                @if ($article->approvedComments->isEmpty())
                    <div class="border border-neutral/20 p-8 text-center bg-secondary">
                        <p class="text-xs font-mono text-neutral">No commentary yet on this briefing.</p>
                        <p class="text-[11px] font-mono text-neutral/70 mt-1">Be the first to submit a technical perspective.</p>
                    </div>
                @else
                    <div class="space-y-6">
                        @foreach ($article->approvedComments as $comment)
                            <div id="comment-{{ $comment->id }}" class="border border-neutral/20 p-5 bg-secondary">
                                <div class="flex items-start justify-between gap-4 mb-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 bg-tertiary border border-neutral/30 font-mono text-xs font-bold text-primary flex items-center justify-center shrink-0">
                                            {{ $comment->initials }}
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2">
                                                @if ($comment->website)
                                                    <a href="{{ $comment->website }}" target="_blank" rel="nofollow noopener" class="text-xs font-mono font-bold text-primary hover:underline">
                                                        {{ $comment->name }}
                                                    </a>
                                                @else
                                                    <span class="text-xs font-mono font-bold text-primary">{{ $comment->name }}</span>
                                                @endif

                                                @if ($comment->is_admin_reply)
                                                    <span class="px-1.5 py-0.2 bg-primary text-secondary text-[9px] font-mono font-bold uppercase tracking-wider">
                                                        Editorial Staff
                                                    </span>
                                                @endif
                                            </div>
                                            <span class="text-[10px] font-mono text-neutral block">
                                                {{ $comment->created_at->diffForHumans() }}
                                            </span>
                                        </div>
                                    </div>
                                    <button type="button" @click="setReply({{ $comment->id }}, '{{ addslashes($comment->name) }}')"
                                        class="text-xs font-mono text-neutral hover:text-primary transition-colors flex items-center gap-1 border border-neutral/20 px-2 py-1 hover:bg-tertiary">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                        </svg>
                                        Reply
                                    </button>
                                </div>

                                <div class="text-xs font-mono text-primary leading-relaxed whitespace-pre-line pl-11">
                                    {!! nl2br(e($comment->content)) !!}
                                </div>

                                {{-- Nested Replies --}}
                                @if ($comment->replies && $comment->replies->count())
                                    <div class="mt-4 pt-4 border-t border-neutral/15 pl-6 md:pl-11 space-y-4">
                                        @foreach ($comment->replies as $reply)
                                            <div id="comment-{{ $reply->id }}" class="p-3 bg-tertiary border border-neutral/20">
                                                <div class="flex items-start justify-between gap-3 mb-2">
                                                    <div class="flex items-center gap-2">
                                                        <div class="w-6 h-6 bg-secondary border border-neutral/30 font-mono text-[10px] font-bold text-primary flex items-center justify-center shrink-0">
                                                            {{ $reply->initials }}
                                                        </div>
                                                        <div>
                                                            <div class="flex items-center gap-2">
                                                                <span class="text-xs font-mono font-bold text-primary">{{ $reply->name }}</span>
                                                                @if ($reply->is_admin_reply)
                                                                    <span class="px-1.5 py-0.2 bg-primary text-secondary text-[8px] font-mono font-bold uppercase tracking-wider">
                                                                        Staff
                                                                    </span>
                                                                @endif
                                                            </div>
                                                            <span class="text-[9px] font-mono text-neutral">
                                                                {{ $reply->created_at->diffForHumans() }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <button type="button" @click="setReply({{ $comment->id }}, '{{ addslashes($reply->name) }}')"
                                                        class="text-[11px] font-mono text-neutral hover:text-primary transition-colors underline">
                                                        Reply
                                                    </button>
                                                </div>
                                                <div class="text-xs font-mono text-primary leading-relaxed whitespace-pre-line pl-8">
                                                    {!! nl2br(e($reply->content)) !!}
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>
        </div>

        {{-- Sticky Right Sidebar: TOC & Ad Slot --}}
        <div class="lg:col-span-4 space-y-8 sticky top-24">
            @if (!empty($toc))
                <div class="hidden lg:block border border-neutral/20 p-5 bg-secondary">
                    <span class="text-xs font-mono uppercase tracking-widest text-primary font-bold block border-b border-neutral/20 pb-2 mb-3">
                        Contents
                    </span>
                    <ul class="space-y-2 text-xs font-mono text-neutral">
                        @foreach ($toc as $item)
                            <li>
                                <a href="#{{ $item['anchor'] }}" class="hover:text-primary transition-colors block">
                                    → {{ $item['title'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {!! \App\Services\AdService::render('sidebar', $article->category_id) !!}
        </div>
    </div>

    {{-- Related Articles Section --}}
    @if ($relatedArticles->isNotEmpty())
        <section class="mt-20 pt-12 border-t border-neutral/20">
            <div class="flex items-center justify-between border-b border-neutral/20 pb-3 mb-8">
                <span class="text-xs font-mono uppercase tracking-widest text-primary font-semibold">Related Analysis</span>
                <span class="text-xs font-mono text-neutral">{{ $article->category->name }}</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach ($relatedArticles as $rel)
                    <article class="swiss-card p-5 bg-secondary flex flex-col justify-between">
                        <div>
                            <a href="{{ route('article.show', $rel->slug) }}" class="block border border-neutral/20 mb-3 overflow-hidden h-36">
                                <img src="{{ $rel->featured_image ?: 'https://picsum.photos/seed/'.$rel->slug.'/400/250' }}" alt="{{ $rel->title }}" class="w-full h-full object-cover hover:scale-105 transition-transform duration-300" loading="lazy" />
                            </a>
                            <span class="text-[10px] font-mono text-neutral uppercase tracking-widest block mb-1">
                                {{ $rel->published_at ? $rel->published_at->format('M d, Y') : '' }} • {{ $rel->reading_time }} min
                            </span>
                            <h3 class="font-bold text-sm text-primary leading-snug">
                                <a href="{{ route('article.show', $rel->slug) }}" class="hover:underline">
                                    {{ $rel->title }}
                                </a>
                            </h3>
                        </div>
                        <div class="pt-3 mt-3 border-t border-neutral/15">
                            <a href="{{ route('article.show', $rel->slug) }}" class="text-xs font-mono font-semibold text-primary hover:underline">
                                Read More →
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

</div>
@endsection
