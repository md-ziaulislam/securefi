@extends('layouts.app')

@section('content')
<div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">

    {{-- Category Header --}}
    <header class="border-b border-neutral/20 pb-8 sm:pb-10 mb-8 sm:mb-12">
        <div class="flex items-center gap-2 mb-2">
            <span class="w-3 h-3 bg-primary"></span>
            <span class="text-xs font-mono uppercase tracking-widest text-neutral">Knowledge Vertical</span>
        </div>
        <h1 class="text-2xl sm:text-3xl md:text-4xl font-bold tracking-tight text-primary mb-3">
            {{ $category->name }}
        </h1>
        @if ($category->description)
            <p class="text-sm sm:text-base text-neutral max-w-2xl leading-relaxed">
                {{ $category->description }}
            </p>
        @endif
        <div class="mt-4 text-xs font-mono text-neutral">
            Showing {{ $articles->total() }} total {{ Str::plural('analysis', $articles->total()) }}
        </div>
    </header>

    {{-- Articles Grid --}}
    @if ($articles->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
            @foreach ($articles as $art)
                <article class="swiss-card p-6 bg-secondary flex flex-col justify-between">
                    <div>
                        <a href="{{ route('article.show', $art->slug) }}" class="block border border-neutral/20 mb-4 overflow-hidden h-48">
                            <img src="{{ $art->featured_image ?: 'https://picsum.photos/seed/'.$art->slug.'/500/300' }}"
                                 alt="{{ $art->title }}"
                                 class="w-full h-full object-cover hover:scale-105 transition-transform duration-300"
                                 loading="lazy" />
                        </a>

                        <div class="flex items-center justify-between text-[11px] font-mono text-neutral mb-2">
                            <span>{{ $art->published_at ? $art->published_at->format('M d, Y') : '' }}</span>
                            <span>{{ $art->reading_time }} min read</span>
                        </div>

                        <h2 class="font-bold text-lg text-primary leading-snug mb-2">
                            <a href="{{ route('article.show', $art->slug) }}" class="hover:underline">
                                {{ $art->title }}
                            </a>
                        </h2>

                        <p class="text-xs text-neutral leading-relaxed">
                            {{ $art->excerpt ?: Str::limit(strip_tags($art->content), 120) }}
                        </p>
                    </div>

                    <div class="pt-4 mt-4 border-t border-neutral/15 flex items-center justify-between text-xs font-mono">
                        <span class="text-neutral">By {{ $art->author->name }}</span>
                        <a href="{{ route('article.show', $art->slug) }}" class="font-semibold text-primary hover:underline">
                            Read →
                        </a>
                    </div>
                </article>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-12 pt-6 border-t border-neutral/20">
            {{ $articles->links() }}
        </div>
    @else
        <div class="p-12 text-center border border-neutral/20 bg-secondary">
            <span class="text-xs font-mono text-neutral block mb-2">Notice</span>
            <p class="text-base font-bold text-primary">No articles published in this category yet.</p>
        </div>
    @endif

</div>
@endsection
