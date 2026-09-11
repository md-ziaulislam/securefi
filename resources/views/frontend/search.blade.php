@extends('layouts.app')

@section('content')
<div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">

    <header class="border-b border-neutral/20 pb-6 sm:pb-8 mb-8 sm:mb-12">
        <div class="flex items-center gap-2 mb-2">
            <span class="w-3 h-3 bg-primary"></span>
            <span class="text-xs font-mono uppercase tracking-widest text-neutral">Search Query</span>
        </div>
        <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-primary">
            @if (!empty($query))
                Results for: "{{ $query }}"
            @else
                Site Search
            @endif
        </h1>
        <div class="mt-4 max-w-md">
            <form action="{{ route('search') }}" method="GET" class="flex items-center">
                <input type="text" name="q" value="{{ $query }}" placeholder="Keywords, tech stacks..." required
                    class="w-full px-4 py-2 text-xs font-mono bg-white border border-neutral/30 focus:outline-none focus:border-primary">
                <button type="submit" class="btn-primary text-xs font-mono py-2 px-4 sm:px-5 shrink-0">Search</button>
            </form>
        </div>
        @if (!empty($query))
            <div class="mt-3 text-xs font-mono text-neutral">
                Found {{ $articles->total() }} {{ Str::plural('result', $articles->total()) }}
            </div>
        @endif
    </header>

    @if ($articles->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
            @foreach ($articles as $art)
                <article class="swiss-card p-6 bg-secondary flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between text-[11px] font-mono text-neutral mb-2">
                            <span>{{ $art->category->name }}</span>
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
                    <div class="pt-4 mt-4 border-t border-neutral/15 text-xs font-mono flex justify-between">
                        <span>{{ $art->published_at ? $art->published_at->format('M d, Y') : '' }}</span>
                        <a href="{{ route('article.show', $art->slug) }}" class="font-semibold text-primary hover:underline">
                            Read Analysis →
                        </a>
                    </div>
                </article>
            @endforeach
        </div>
        <div class="mt-12 pt-6 border-t border-neutral/20">
            {{ $articles->links() }}
        </div>
    @elseif (!empty($query))
        <div class="p-12 text-center border border-neutral/20 bg-secondary">
            <p class="text-base font-bold text-primary">No articles matched your query. Try broader keywords.</p>
        </div>
    @endif

</div>
@endsection
