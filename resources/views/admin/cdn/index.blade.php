@extends('layouts.admin')

@section('title', 'CDN & Cloudflare Cache Purge')
@section('header_title', 'CDN Integration & Cloudflare Edge Controls')

@section('content')
<div class="space-y-8">

    {{-- Status Banner & Telemetry --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        {{-- CDN State --}}
        <div class="bg-white border border-[#111111]/15 p-4 flex items-center justify-between">
            <div>
                <p class="text-[10px] font-mono uppercase tracking-wider text-[#808080]">CDN Status</p>
                <p class="text-sm font-mono font-bold mt-1 {{ $isEnabled ? 'text-emerald-600' : 'text-amber-600' }}">
                    {{ $isEnabled ? '● Active / Enabled' : '○ Standby / Disabled' }}
                </p>
            </div>
            <span class="inline-flex items-center px-2 py-0.5 text-[10px] font-mono {{ $isEnabled ? 'bg-emerald-50 text-emerald-700 border border-emerald-300' : 'bg-amber-50 text-amber-700 border border-amber-300' }}">
                {{ $isEnabled ? 'CDN Routing' : 'Direct Origin' }}
            </span>
        </div>

        {{-- API Verification --}}
        <div class="bg-white border border-[#111111]/15 p-4 flex items-center justify-between">
            <div>
                <p class="text-[10px] font-mono uppercase tracking-wider text-[#808080]">Cloudflare API Credentials</p>
                <p class="text-sm font-mono font-bold mt-1 {{ $isConfigured ? 'text-emerald-600' : 'text-red-600' }}">
                    {{ $isConfigured ? '● Configured' : '○ Incomplete' }}
                </p>
            </div>
            <span class="inline-flex items-center px-2 py-0.5 text-[10px] font-mono {{ $isConfigured ? 'bg-emerald-50 text-emerald-700 border border-emerald-300' : 'bg-red-50 text-red-700 border border-red-300' }}">
                {{ $isConfigured ? 'Zone Token Set' : 'Credentials Needed' }}
            </span>
        </div>

        {{-- Last Purge --}}
        <div class="bg-white border border-[#111111]/15 p-4 flex items-center justify-between">
            <div>
                <p class="text-[10px] font-mono uppercase tracking-wider text-[#808080]">Last Edge Purge</p>
                <p class="text-sm font-mono font-bold text-[#111111] mt-1">
                    {{ $lastPurgedAt ? \Carbon\Carbon::parse($lastPurgedAt)->diffForHumans() : 'Never' }}
                </p>
            </div>
            @if($lastPurgedAt)
                <span class="inline-flex items-center px-2 py-0.5 text-[10px] font-mono bg-[#F8F8F6] text-[#111111] border border-[#111111]/20">
                    {{ strtoupper($lastPurgeType) }}
                </span>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

        {{-- Left: Configuration Form (6 cols) --}}
        <div class="lg:col-span-6 space-y-6">

            <div class="bg-white border border-[#111111]/15 p-6 space-y-6">
                <div class="border-b border-[#111111]/10 pb-3 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-sm text-[#111111]">1. Cloudflare CDN Configuration</h3>
                        <p class="text-xs font-mono text-[#808080]">Configure Cloudflare Zone ID and Purge API Token.</p>
                    </div>
                    <span class="text-[10px] font-mono text-[#808080]">API v4</span>
                </div>

                <form action="{{ route('admin.cdn.settings') }}" method="POST" class="space-y-5">
                    @csrf

                    {{-- Enable Toggle --}}
                    <div class="p-3 bg-[#F8F8F6] border border-[#111111]/15">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="cdn_enabled" value="1"
                                {{ ($settings['cdn_enabled'] ?? '0') === '1' ? 'checked' : '' }}
                                class="w-4 h-4 rounded-none border-[#111111] text-[#111111] focus:ring-0">
                            <div>
                                <span class="text-xs font-mono font-bold text-[#111111] uppercase tracking-wider">Enable CDN Integration</span>
                                <p class="text-xs font-mono text-[#808080]">Enables cache purge triggers and CDN headers management.</p>
                            </div>
                        </label>
                    </div>

                    {{-- Zone ID --}}
                    <div>
                        <label for="cloudflare_zone_id" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1 font-medium">
                            Cloudflare Zone ID *
                        </label>
                        <input type="text" id="cloudflare_zone_id" name="cloudflare_zone_id"
                            value="{{ old('cloudflare_zone_id', $settings['cloudflare_zone_id'] ?? '') }}"
                            placeholder="e.g. 023e105f4ecef8ad9ca31a8372d0c353"
                            class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                        <p class="text-xs font-mono text-[#808080] mt-1">Found in your Cloudflare dashboard under your domain's Overview tab (right column).</p>
                    </div>

                    {{-- API Token --}}
                    <div>
                        <label for="cloudflare_api_token" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1 font-medium">
                            Cloudflare API Token *
                        </label>
                        <input type="password" id="cloudflare_api_token" name="cloudflare_api_token"
                            placeholder="{{ !empty($settings['cloudflare_api_token']) ? '••••••••••••••••••••••••••••••••••••••••' : 'Paste your API Token...' }}"
                            autocomplete="new-password"
                            class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                        <p class="text-xs font-mono text-[#808080] mt-1">Needs <code class="bg-[#F8F8F6] px-1 font-bold">Zone - Cache Purge - Purge</code> permissions. Leave empty to keep current.</p>
                    </div>

                    {{-- Custom CDN Domain Prefix --}}
                    <div>
                        <label for="cdn_custom_domain" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1">
                            Custom CDN Asset Domain <span class="text-[#808080] normal-case">(Optional)</span>
                        </label>
                        <input type="url" id="cdn_custom_domain" name="cdn_custom_domain"
                            value="{{ old('cdn_custom_domain', $settings['cdn_custom_domain'] ?? '') }}"
                            placeholder="https://cdn.securofi.tech"
                            class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                        <p class="text-xs font-mono text-[#808080] mt-1">Leave empty if you use Cloudflare proxying (orange cloud) directly on your root domain.</p>
                    </div>

                    {{-- Auto-Purge Toggle --}}
                    <div class="p-3 bg-[#F8F8F6] border border-[#111111]/15">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="cdn_auto_purge" value="1"
                                {{ ($settings['cdn_auto_purge'] ?? '1') === '1' ? 'checked' : '' }}
                                class="w-4 h-4 rounded-none border-[#111111] text-[#111111] focus:ring-0">
                            <div>
                                <span class="text-xs font-mono font-bold text-[#111111] uppercase tracking-wider">Auto-Purge Article URLs on Update</span>
                                <p class="text-xs font-mono text-[#808080]">Automatically invalidate Cloudflare cache for an article URL when it is updated or published.</p>
                            </div>
                        </label>
                    </div>

                    <div class="pt-2 flex flex-col sm:flex-row gap-3">
                        <button type="submit" class="btn-primary flex-1 text-xs font-mono uppercase tracking-wider py-2.5">
                            Save CDN Configuration →
                        </button>
                    </div>
                </form>

                {{-- Test Connection Button --}}
                <div class="pt-4 border-t border-[#111111]/10 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-mono font-bold text-[#111111]">Validate API Connection</p>
                        <p class="text-xs font-mono text-[#808080]">Verify that your Zone ID and API Token are accepted by Cloudflare.</p>
                    </div>
                    <form action="{{ route('admin.cdn.test') }}" method="POST">
                        @csrf
                        <button type="submit" class="px-4 py-2 text-xs font-mono uppercase tracking-wider bg-white border border-[#111111] hover:bg-[#F8F8F6] text-[#111111] transition-colors">
                            Test Connection
                        </button>
                    </form>
                </div>
            </div>

            {{-- Setup Guide Card --}}
            <div class="bg-white border border-[#111111]/15 p-6 space-y-4">
                <div class="border-b border-[#111111]/10 pb-3">
                    <h3 class="font-bold text-sm text-[#111111]">Cloudflare Setup Instructions</h3>
                    <p class="text-xs font-mono text-[#808080]">How to obtain your credentials in under 2 minutes.</p>
                </div>

                <ol class="text-xs font-mono text-[#111111] space-y-3 list-decimal list-inside leading-relaxed">
                    <li>
                        Log in to your <a href="https://dash.cloudflare.com" target="_blank" class="underline hover:text-primary font-bold">Cloudflare Dashboard</a> and click on your website domain.
                    </li>
                    <li>
                        On the <strong>Overview</strong> page, scroll down the right-hand sidebar to the <strong>API</strong> section. Copy the <strong>Zone ID</strong> and paste it into the field above.
                    </li>
                    <li>
                        Click on <strong>Get your API token</strong> (or navigate to <a href="https://dash.cloudflare.com/profile/api-tokens" target="_blank" class="underline hover:text-primary">User Profile → API Tokens</a>).
                    </li>
                    <li>
                        Click <strong>Create Token</strong>, locate the <strong>"Cache Purge"</strong> template and click <strong>Use template</strong>.
                    </li>
                    <li>
                        Under <strong>Zone Resources</strong>, select <em>Include → Specific zone → [Your Domain]</em>. Click <strong>Continue to summary</strong> and then <strong>Create Token</strong>.
                    </li>
                    <li>
                        Copy the generated token string, paste it above, and click <strong>Test Connection</strong>.
                    </li>
                </ol>
            </div>

        </div>

        {{-- Right: Cache Purge Actions (6 cols) --}}
        <div class="lg:col-span-6 space-y-6">

            {{-- Purge Entire Cache Card --}}
            <div class="bg-white border border-[#111111]/15 p-6 space-y-4">
                <div class="border-b border-[#111111]/10 pb-3">
                    <h3 class="font-bold text-sm text-[#111111]">2. Purge Entire Cache</h3>
                    <p class="text-xs font-mono text-[#808080]">Invalidate every cached asset globally across all Cloudflare data centers.</p>
                </div>

                <div class="p-4 bg-amber-50 border border-amber-200 text-amber-900 text-xs font-mono space-y-2">
                    <div class="flex items-center gap-2 font-bold">
                        <svg class="w-4 h-4 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>Global Cache Flush Warning</span>
                    </div>
                    <p class="leading-relaxed">
                        Purging everything causes subsequent requests to hit your origin server until Cloudflare rebuilds its cache. Use this primarily after deploying major CSS, JavaScript, or template overhauls.
                    </p>
                </div>

                <form action="{{ route('admin.cdn.purge-all') }}" method="POST" onsubmit="return confirm('Are you sure you want to purge the ENTIRE Cloudflare edge cache? All global edge nodes will be flushed.')">
                    @csrf
                    <button type="submit" class="w-full bg-[#111111] text-white hover:bg-black font-mono text-xs uppercase tracking-wider py-3.5 transition-colors flex items-center justify-center gap-2 font-medium">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Purge Entire Edge Cache Now →
                    </button>
                </form>
            </div>

            {{-- Purge Specific URLs Card --}}
            <div class="bg-white border border-[#111111]/15 p-6 space-y-4">
                <div class="border-b border-[#111111]/10 pb-3">
                    <h3 class="font-bold text-sm text-[#111111]">3. Purge Specific URLs</h3>
                    <p class="text-xs font-mono text-[#808080]">Selectively invalidate specific pages or assets without affecting the rest of the cache.</p>
                </div>

                <form action="{{ route('admin.cdn.purge-urls') }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label for="purge_urls" class="block text-xs font-mono uppercase tracking-wider text-[#111111] font-medium">
                                Target URLs (One per line) *
                            </label>
                            <span class="text-[10px] font-mono text-[#808080]">Max 30 URLs per batch</span>
                        </div>
                        <textarea id="purge_urls" name="purge_urls" rows="6" required
                            placeholder="{{ url('/') }}&#10;{{ url('/feed') }}&#10;{{ url('/css/app.css') }}"
                            class="w-full px-3.5 py-2.5 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111] leading-relaxed"></textarea>
                        <p class="text-xs font-mono text-[#808080] mt-1">Must be full absolute URLs including protocol (e.g. <code>https://securofi.tech/article-slug</code>).</p>
                    </div>

                    {{-- Quick Insert Shortcuts --}}
                    <div class="space-y-1.5">
                        <p class="text-[10px] font-mono uppercase tracking-wider text-[#808080]">Quick Shortcuts:</p>
                        <div class="flex flex-wrap gap-2">
                            <button type="button" onclick="insertUrl('{{ url('/') }}')"
                                class="px-2 py-1 text-[11px] font-mono bg-[#F8F8F6] border border-[#111111]/20 hover:border-[#111111] transition-colors">
                                + Homepage
                            </button>
                            <button type="button" onclick="insertUrl('{{ route('feed.index') }}')"
                                class="px-2 py-1 text-[11px] font-mono bg-[#F8F8F6] border border-[#111111]/20 hover:border-[#111111] transition-colors">
                                + RSS Feed
                            </button>
                            <button type="button" onclick="insertUrl('{{ url('/llms.txt') }}')"
                                class="px-2 py-1 text-[11px] font-mono bg-[#F8F8F6] border border-[#111111]/20 hover:border-[#111111] transition-colors">
                                + llms.txt
                            </button>
                            <button type="button" onclick="insertUrl('{{ url('/sitemap.xml') }}')"
                                class="px-2 py-1 text-[11px] font-mono bg-[#F8F8F6] border border-[#111111]/20 hover:border-[#111111] transition-colors">
                                + Sitemap
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="w-full px-4 py-2.5 text-xs font-mono uppercase tracking-wider bg-white border border-[#111111] hover:bg-[#111111] hover:text-white text-[#111111] transition-colors font-medium">
                        Purge Specified URLs →
                    </button>
                </form>
            </div>

        </div>

    </div>

</div>

@push('scripts')
<script>
    function insertUrl(url) {
        var textarea = document.getElementById('purge_urls');
        if (!textarea) return;

        var currentVal = textarea.value.trim();
        if (currentVal.length > 0) {
            var lines = currentVal.split('\n').map(function(l) { return l.trim(); });
            if (lines.indexOf(url) === -1) {
                textarea.value = currentVal + '\n' + url;
            }
        } else {
            textarea.value = url;
        }
    }
</script>
@endpush
@endsection
