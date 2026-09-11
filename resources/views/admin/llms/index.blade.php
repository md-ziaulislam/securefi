@extends('layouts.admin')

@section('title', 'llms.txt — AI / LLM Visibility')
@section('header_title', 'llms.txt — AI & LLM Crawl Visibility')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

    {{-- Left: Editor --}}
    <div class="lg:col-span-8 space-y-6">

        @if (session('success'))
            <div class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-mono">
                {{ session('success') }}
            </div>
        @endif

        {{-- Controls --}}
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-sm font-bold text-[#111111]">Edit llms.txt Content</h2>
                <p class="text-xs font-mono text-[#808080] mt-0.5">
                    Follows the <a href="https://llmstxt.org/" target="_blank" class="underline">llmstxt.org</a> standard.
                    This file is served at <a href="{{ url('/llms.txt') }}" target="_blank" class="underline font-medium text-[#111111]">/llms.txt</a>.
                </p>
            </div>

            <form action="{{ route('admin.llms.regenerate') }}" method="POST">
                @csrf
                <button type="submit"
                    class="px-4 py-2 text-xs font-mono bg-[#111111] text-white hover:opacity-80 transition-opacity uppercase tracking-wider"
                    onclick="return confirm('Regenerate llms.txt from live site data? This will overwrite your manual edits.')">
                    ↺ Auto-Regenerate from Site
                </button>
            </form>
        </div>

        {{-- Main Form --}}
        <form action="{{ route('admin.llms.update') }}" method="POST" class="space-y-5">
            @csrf

            {{-- Enable/Disable & Options --}}
            <div class="bg-white border border-[#111111]/15 p-5 space-y-4">
                <div class="flex flex-wrap gap-6">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="llms_enabled" value="1"
                            {{ ($settings['llms_enabled'] ?? '1') === '1' ? 'checked' : '' }}
                            class="w-4 h-4 rounded-none border-[#111111]">
                        <span class="text-xs font-mono font-bold text-[#111111]">Enable /llms.txt (publicly accessible)</span>
                    </label>

                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="llms_auto_update" value="1"
                            {{ ($settings['llms_auto_update'] ?? '0') === '1' ? 'checked' : '' }}
                            class="w-4 h-4 rounded-none border-[#111111]">
                        <span class="text-xs font-mono text-[#111111]">Auto-update from live content on every request</span>
                    </label>
                </div>
                <p class="text-[10px] font-mono text-[#808080]">
                    Auto-update always generates fresh content from current articles, categories, and pages.
                    When off, the manually edited content below is served.
                </p>
            </div>

            {{-- Content Editor --}}
            <div class="bg-white border border-[#111111]/15">
                <div class="border-b border-[#111111]/10 px-4 py-2.5 flex items-center justify-between">
                    <span class="text-[10px] font-mono uppercase tracking-widest text-[#808080] font-semibold">llms.txt Content</span>
                    <span class="text-[10px] font-mono text-[#808080]">Markdown-compatible plain text</span>
                </div>
                <textarea id="llms_txt_content" name="llms_txt_content" rows="28"
                    class="w-full px-4 py-3 text-xs font-mono bg-white border-0 focus:outline-none leading-relaxed resize-y"
                    style="min-height: 400px; tab-size: 2;">{{ old('llms_txt_content', $currentContent) }}</textarea>
            </div>

            {{-- Submit --}}
            <div class="p-4 bg-white border border-[#111111]/15">
                <button type="submit" class="btn-primary w-full text-xs font-mono uppercase tracking-wider py-3.5">
                    Save llms.txt Content →
                </button>
            </div>
        </form>
    </div>

    {{-- Right: Info Panel --}}
    <div class="lg:col-span-4 space-y-6">

        {{-- Status --}}
        <div class="bg-white border border-[#111111]/15 p-5 space-y-3">
            <h3 class="font-bold text-sm text-[#111111] border-b border-[#111111]/10 pb-2">Live File Status</h3>

            @if (($settings['llms_enabled'] ?? '1') === '1')
                <div class="flex items-center gap-2 p-2 bg-emerald-50 border border-emerald-200">
                    <span class="w-2 h-2 bg-emerald-500 rounded-full shrink-0"></span>
                    <span class="text-xs font-mono text-emerald-800">Active — publicly reachable</span>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ url('/llms.txt') }}" target="_blank"
                        class="text-xs font-mono px-3 py-1.5 bg-[#111111] text-white hover:opacity-80 transition-opacity">
                        View /llms.txt →
                    </a>
                </div>
            @else
                <div class="flex items-center gap-2 p-2 bg-amber-50 border border-amber-200">
                    <span class="w-2 h-2 bg-amber-400 rounded-full shrink-0"></span>
                    <span class="text-xs font-mono text-amber-800">Disabled — returns 404</span>
                </div>
            @endif

            <div class="text-xs font-mono text-[#808080] space-y-1">
                <p><strong class="text-[#111111]">URL:</strong> <code>{{ url('/llms.txt') }}</code></p>
                <p><strong class="text-[#111111]">Content-Type:</strong> <code>text/plain; charset=UTF-8</code></p>
                <p><strong class="text-[#111111]">Cache:</strong> 1 hour (public)</p>
            </div>
        </div>

        {{-- What is llms.txt --}}
        <div class="bg-white border border-[#111111]/15 p-5 space-y-3">
            <h3 class="font-bold text-sm text-[#111111] border-b border-[#111111]/10 pb-2">What is llms.txt?</h3>
            <div class="text-xs font-mono text-[#808080] space-y-2 leading-relaxed">
                <p>Similar to <code>robots.txt</code> for search engines, <code>llms.txt</code> is a standard file that tells <strong class="text-[#111111]">AI language models and LLM-powered search tools</strong> (ChatGPT, Claude, Perplexity, Gemini) about your site's structure and key content.</p>
                <p>It helps AI systems:</p>
                <ul class="space-y-1 pl-3">
                    <li>→ Discover and understand your content categories</li>
                    <li>→ Find your most important articles and pages</li>
                    <li>→ Correctly attribute and cite your content</li>
                    <li>→ Reference your RSS feed for fresh content</li>
                </ul>
                <p class="pt-1">Standard: <a href="https://llmstxt.org/" target="_blank" class="underline hover:text-[#111111]">llmstxt.org</a></p>
            </div>
        </div>

        {{-- Format Guide --}}
        <div class="bg-white border border-[#111111]/15 p-5 space-y-3">
            <h3 class="font-bold text-sm text-[#111111] border-b border-[#111111]/10 pb-2">Format Reference</h3>
            <div class="text-[10px] font-mono text-[#808080] space-y-2">
                <div class="bg-[#F8F8F6] p-3 border border-[#111111]/10 space-y-1">
                    <p class="text-[#111111] font-bold"># Site Name</p>
                    <p>> Site tagline / description</p>
                    <p class="mt-1">## Section Heading</p>
                    <p>- [Link Text](https://url): Description</p>
                </div>
                <p>The file uses Markdown-compatible syntax. The first H1 heading is the site name, blockquote is the tagline, and H2 sections organize content types.</p>
            </div>
        </div>

        {{-- AEO Tips --}}
        <div class="bg-white border border-[#111111]/15 p-5 space-y-3">
            <h3 class="font-bold text-sm text-[#111111] border-b border-[#111111]/10 pb-2">AEO Tips</h3>
            <ul class="text-xs font-mono text-[#808080] space-y-2">
                <li>→ Keep descriptions concise and factual — AI models prefer structured data.</li>
                <li>→ Include your RSS feed URL so AI crawlers find fresh content automatically.</li>
                <li>→ Update regularly when you add new categories or major content sections.</li>
                <li>→ Use <strong class="text-[#111111]">Auto-Regenerate</strong> after publishing many new articles to keep the file fresh.</li>
            </ul>
        </div>

    </div>

</div>
@endsection
