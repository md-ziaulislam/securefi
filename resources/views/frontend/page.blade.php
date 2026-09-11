@extends('layouts.app')

@section('content')
<div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">

    <article class="max-w-3xl mx-auto">
        <header class="border-b border-neutral/20 pb-6 sm:pb-8 mb-8 sm:mb-10">
            <div class="flex items-center gap-2 mb-2">
                <span class="w-3 h-3 bg-primary"></span>
                <span class="text-xs font-mono uppercase tracking-widest text-neutral">Document</span>
            </div>
            <h1 class="text-2xl sm:text-3xl md:text-4xl font-bold tracking-tight text-primary">
                {{ $page->title }}
            </h1>
            <div class="mt-2 text-xs font-mono text-neutral">
                Last modified: {{ $page->updated_at->format('F d, Y') }}
            </div>
        </header>

        <div class="prose max-w-[72ch] text-base leading-[1.7] text-primary/90 space-y-6">
            {!! $page->content !!}
        </div>
    </article>

</div>
@endsection
