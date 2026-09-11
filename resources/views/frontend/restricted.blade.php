@extends('layouts.app')

@section('content')
<div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-20">

    <div class="max-w-2xl mx-auto border border-[#111111]/20 bg-white p-6 sm:p-10 text-left shadow-sm">
        
        {{-- Geo Icon & Badge --}}
        <div class="flex items-center justify-between border-b border-[#111111]/10 pb-4 mb-6">
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 bg-amber-500 inline-block"></span>
                <span class="text-xs font-mono uppercase tracking-wider text-[#808080]">Geo-Fencing Policy Notice</span>
            </div>
            @if ($visitorCountry)
                <span class="text-[10px] font-mono font-bold bg-[#F8F8F6] border border-[#111111]/15 px-2 py-0.5 text-[#111111]">
                    Detected Location: {{ $visitorCountry }}
                </span>
            @endif
        </div>

        {{-- Heading --}}
        <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-[#111111] mb-3">
            Content Not Available in Your Region
        </h1>

        {{-- Custom or Standard Message --}}
        <div class="text-sm font-mono text-[#808080] leading-relaxed mb-6 space-y-3">
            @if (!empty($fallbackMessage))
                <p class="text-[#111111] bg-amber-50/70 border-l-2 border-amber-500 p-3">
                    {{ $fallbackMessage }}
                </p>
            @else
                <p>
                    The publication <strong class="text-[#111111]">"{{ $title }}"</strong> is tailored for specific regional jurisdictions or regulatory standards and is currently restricted from your detected location (<strong>{{ $visitorCountry ?: 'Unknown' }}</strong>).
                </p>
            @endif
            <p class="text-xs">
                SecuroFi.Tech maintains strict compliance with local financial disclosures, data protection frameworks, and regional technical compliance guidelines.
            </p>
        </div>

        {{-- Localized Variant Suggestion --}}
        @if (!empty($localizedVariant))
            <div class="p-4 bg-emerald-50/60 border border-emerald-200 mb-8">
                <div class="flex items-start gap-3">
                    <span class="text-emerald-700 text-base">🌍</span>
                    <div>
                        <div class="text-xs font-bold text-emerald-950 uppercase tracking-wider mb-1 font-mono">
                            Localized Edition Available
                        </div>
                        <p class="text-xs text-emerald-800 font-mono mb-2">
                            We offer an edition specifically curated for your region:
                        </p>
                        <a href="{{ route('article.show', $localizedVariant->slug) }}" class="inline-flex items-center gap-1.5 text-xs font-bold font-mono text-emerald-900 bg-white border border-emerald-300 px-3 py-1.5 hover:bg-emerald-100 transition-colors">
                            <span>Read: {{ $localizedVariant->title }}</span>
                            <span>→</span>
                        </a>
                    </div>
                </div>
            </div>
        @elseif (!empty($allVariants) && $allVariants->count() > 1)
            <div class="p-4 bg-[#F8F8F6] border border-[#111111]/15 mb-8">
                <div class="text-xs font-bold text-[#111111] uppercase tracking-wider mb-2 font-mono">
                    Available Regional Editions:
                </div>
                <ul class="space-y-1.5 text-xs font-mono">
                    @foreach ($allVariants as $var)
                        @if ($var->isAccessibleInCountry($visitorCountry))
                            <li>
                                <a href="{{ route('article.show', $var->slug) }}" class="text-primary hover:underline flex items-center gap-1">
                                    <span>•</span>
                                    <span>{{ $var->title }} ({{ $var->variant_country ?: 'Global' }})</span>
                                    <span>→</span>
                                </a>
                            </li>
                        @endif
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Return Actions --}}
        <div class="flex flex-wrap items-center gap-3 pt-4 border-t border-[#111111]/10 text-xs font-mono">
            <a href="{{ route('home') }}" class="btn-primary px-4 py-2 text-xs">
                ← Return to Homepage
            </a>
            <a href="{{ route('feed.index') }}" class="px-3.5 py-2 border border-[#111111]/30 hover:bg-[#F5F1E8] text-[#111111]">
                View Global Feeds
            </a>
        </div>

    </div>

</div>
@endsection
