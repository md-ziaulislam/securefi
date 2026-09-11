@extends('layouts.admin')

@section('title', 'Live Traffic Monitor & Analytics')
@section('header_title', 'Live Traffic & Audience Analytics')

@section('header_badge')
    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-emerald-50 text-emerald-800 border border-emerald-300 font-mono text-[11px] uppercase font-bold tracking-wider">
        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
        <span id="livePulseText">{{ $activeVisitorsNow }} Online Now</span>
    </span>
@endsection

@section('content')
<div class="space-y-6" x-data="analyticsPoller()">

    <!-- Control Bar & Range Switcher -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-[#111111]/15 pb-4">
        <div class="flex items-center gap-2">
            <span class="text-xs font-mono uppercase text-[#808080] font-semibold">Date Scope:</span>
            <div class="inline-flex border border-[#111111]/20 bg-white">
                <a href="{{ route('admin.analytics.index', ['period' => 'today']) }}" 
                   class="px-3 py-1 text-xs font-mono uppercase transition-colors {{ $period === 'today' ? 'bg-[#111111] text-white font-bold' : 'text-[#111111] hover:bg-[#F5F1E8]' }}">
                    Today
                </a>
                <a href="{{ route('admin.analytics.index', ['period' => '7d']) }}" 
                   class="px-3 py-1 text-xs font-mono uppercase transition-colors border-l border-[#111111]/20 {{ $period === '7d' ? 'bg-[#111111] text-white font-bold' : 'text-[#111111] hover:bg-[#F5F1E8]' }}">
                    7 Days
                </a>
                <a href="{{ route('admin.analytics.index', ['period' => '30d']) }}" 
                   class="px-3 py-1 text-xs font-mono uppercase transition-colors border-l border-[#111111]/20 {{ $period === '30d' ? 'bg-[#111111] text-white font-bold' : 'text-[#111111] hover:bg-[#F5F1E8]' }}">
                    30 Days
                </a>
                <a href="{{ route('admin.analytics.index', ['period' => 'all']) }}" 
                   class="px-3 py-1 text-xs font-mono uppercase transition-colors border-l border-[#111111]/20 {{ $period === 'all' ? 'bg-[#111111] text-white font-bold' : 'text-[#111111] hover:bg-[#F5F1E8]' }}">
                    All Time
                </a>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.analytics.export') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 border border-[#111111] bg-white text-[#111111] text-xs font-mono uppercase tracking-wider hover:bg-[#111111] hover:text-white transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                <span>Export CSV</span>
            </a>
            <button @click="refreshStats()" class="p-1.5 border border-[#111111]/20 bg-white text-[#808080] hover:text-[#111111] hover:border-[#111111] transition-colors" title="Force Refresh">
                <svg class="w-4 h-4" :class="loading ? 'animate-spin' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            </button>
        </div>
    </div>

    <!-- 4 High-Contrast KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1 -->
        <div class="bg-white border border-[#111111]/15 p-5 relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-xs font-mono uppercase tracking-wider text-[#808080]">Real-Time Active</span>
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-ping"></span>
            </div>
            <div class="text-3xl font-bold font-mono tracking-tight text-[#111111] mt-2" id="kpiActiveNow">
                {{ $activeVisitorsNow }}
            </div>
            <div class="text-xs font-mono text-[#808080] mt-1 flex items-center gap-2">
                <span class="text-emerald-700 font-bold" id="kpiActiveHumans">{{ $activeHumansNow }} Humans</span>
                <span>•</span>
                <span class="text-neutral-500" id="kpiActiveBots">{{ $activeBotsNow }} Bots / Crawlers</span>
            </div>
        </div>

        <!-- Card 2 -->
        <div class="bg-white border border-[#111111]/15 p-5">
            <span class="text-xs font-mono uppercase tracking-wider text-[#808080]">Total Pageviews</span>
            <div class="text-3xl font-bold font-mono tracking-tight text-[#111111] mt-2">
                {{ number_format($totalPageviews) }}
            </div>
            <div class="text-xs font-mono text-[#808080] mt-1">
                Selected period scope: <span class="uppercase font-bold text-[#111111]">{{ $period }}</span>
            </div>
        </div>

        <!-- Card 3 -->
        <div class="bg-white border border-[#111111]/15 p-5">
            <span class="text-xs font-mono uppercase tracking-wider text-[#808080]">Unique Visitors</span>
            <div class="text-3xl font-bold font-mono tracking-tight text-[#111111] mt-2">
                {{ number_format($uniqueSessions) }}
            </div>
            <div class="text-xs font-mono text-[#808080] mt-1">
                {{ number_format($returningCount) }} returning audience
            </div>
        </div>

        <!-- Card 4 -->
        <div class="bg-white border border-[#111111]/15 p-5">
            <span class="text-xs font-mono uppercase tracking-wider text-[#808080]">Human vs Bot Traffic</span>
            <div class="text-3xl font-bold font-mono tracking-tight text-[#111111] mt-2">
                @php
                    $humanPct = $totalPageviews > 0 ? round(($humanVisits / $totalPageviews) * 100) : 0;
                @endphp
                {{ $humanPct }}% <span class="text-xs font-normal text-[#808080]">Human</span>
            </div>
            <div class="text-xs font-mono text-[#808080] mt-1">
                {{ number_format($humanVisits) }} Human / {{ number_format($botVisits) }} Crawlers
            </div>
        </div>
    </div>

    <!-- 7-Day Traffic Activity Rhythm (Swiss Minimalist Bar Graph) -->
    <div class="bg-white border border-[#111111]/15 p-6">
        <div class="flex items-center justify-between pb-4 border-b border-[#111111]/10 mb-6">
            <div>
                <h3 class="text-xs font-mono uppercase font-bold text-[#111111]">Daily Traffic Volume & Crawler Ratio</h3>
                <p class="text-xs text-[#808080] mt-0.5">Distribution of daily human pageviews vs automated search & AI crawlers.</p>
            </div>
            <div class="flex items-center gap-4 text-xs font-mono">
                <span class="inline-flex items-center gap-1.5">
                    <span class="w-3 h-3 bg-[#111111]"></span>
                    <span>Human Traffic</span>
                </span>
                <span class="inline-flex items-center gap-1.5">
                    <span class="w-3 h-3 bg-[#B38B6D]"></span>
                    <span>Search / AI Bots</span>
                </span>
            </div>
        </div>

        @php
            $maxDaily = max(1, max(array_column($dailyTrend, 'total')));
        @endphp

        <div class="grid grid-cols-7 gap-3 items-end h-48 pt-6 border-b border-[#111111]/15">
            @foreach ($dailyTrend as $day)
                @php
                    $totalHeight = round(($day['total'] / $maxDaily) * 100);
                    $humanHeight = $day['total'] > 0 ? round(($day['humans'] / $day['total']) * 100) : 0;
                    $botHeight = 100 - $humanHeight;
                @endphp
                <div class="flex flex-col items-center h-full justify-end group relative">
                    <!-- Tooltip -->
                    <div class="absolute -top-12 bg-[#111111] text-white text-[10px] font-mono px-2 py-1 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap z-10">
                        {{ $day['label'] }}: {{ $day['total'] }} total ({{ $day['humans'] }} humans, {{ $day['bots'] }} bots)
                    </div>

                    <div class="w-full max-w-[48px] bg-neutral-100 flex flex-col justify-end transition-all" style="height: {{ max(6, $totalHeight) }}%;">
                        @if ($day['bots'] > 0)
                            <div class="w-full bg-[#B38B6D]" style="height: {{ $botHeight }}%;"></div>
                        @endif
                        @if ($day['humans'] > 0)
                            <div class="w-full bg-[#111111]" style="height: {{ $humanHeight }}%;"></div>
                        @endif
                    </div>
                    <span class="text-[10px] font-mono text-[#808080] mt-2">{{ $day['label'] }}</span>
                    <span class="text-[10px] font-mono font-bold text-[#111111]">{{ $day['total'] }}</span>
                </div>
            @endforeach
        </div>
    </div>

    <!-- 4-Column Breakdown Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

        <!-- Column 1: Top Countries -->
        <div class="bg-white border border-[#111111]/15 p-5">
            <h4 class="text-xs font-mono uppercase font-bold text-[#111111] border-b border-[#111111]/10 pb-2 mb-3">
                Top Countries
            </h4>
            <div class="space-y-2.5">
                @forelse ($topCountries as $item)
                    @php
                        $pct = $totalPageviews > 0 ? round(($item->count / $totalPageviews) * 100) : 0;
                    @endphp
                    <div>
                        <div class="flex items-center justify-between text-xs font-mono">
                            <span class="truncate text-[#111111] font-medium">{{ $item->country }}</span>
                            <span class="text-[#808080] shrink-0">{{ $item->count }} ({{ $pct }}%)</span>
                        </div>
                        <div class="w-full bg-[#F5F1E8] h-1.5 mt-1 overflow-hidden">
                            <div class="bg-[#111111] h-full" style="width: {{ $pct }}%;"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-xs font-mono text-[#808080] py-4">No country data yet.</p>
                @endforelse
            </div>
        </div>

        <!-- Column 2: Device Breakdown -->
        <div class="bg-white border border-[#111111]/15 p-5">
            <h4 class="text-xs font-mono uppercase font-bold text-[#111111] border-b border-[#111111]/10 pb-2 mb-3">
                Device Distribution
            </h4>
            <div class="space-y-2.5">
                @forelse ($topDevices as $item)
                    @php
                        $pct = $totalPageviews > 0 ? round(($item->count / $totalPageviews) * 100) : 0;
                    @endphp
                    <div>
                        <div class="flex items-center justify-between text-xs font-mono">
                            <span class="truncate text-[#111111] font-medium">{{ $item->device_type ?: 'Desktop' }}</span>
                            <span class="text-[#808080] shrink-0">{{ $item->count }} ({{ $pct }}%)</span>
                        </div>
                        <div class="w-full bg-[#F5F1E8] h-1.5 mt-1 overflow-hidden">
                            <div class="bg-[#111111] h-full" style="width: {{ $pct }}%;"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-xs font-mono text-[#808080] py-4">No device data yet.</p>
                @endforelse
            </div>
        </div>

        <!-- Column 3: Traffic Referrers -->
        <div class="bg-white border border-[#111111]/15 p-5">
            <h4 class="text-xs font-mono uppercase font-bold text-[#111111] border-b border-[#111111]/10 pb-2 mb-3">
                Referral Channels
            </h4>
            <div class="space-y-2.5">
                @forelse ($topReferrers as $item)
                    @php
                        $pct = $totalPageviews > 0 ? round(($item->count / $totalPageviews) * 100) : 0;
                    @endphp
                    <div>
                        <div class="flex items-center justify-between text-xs font-mono">
                            <span class="truncate text-[#111111] font-medium">{{ $item->referrer_source ?: 'Direct' }}</span>
                            <span class="text-[#808080] shrink-0">{{ $item->count }}</span>
                        </div>
                        <div class="w-full bg-[#F5F1E8] h-1.5 mt-1 overflow-hidden">
                            <div class="bg-[#B38B6D] h-full" style="width: {{ $pct }}%;"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-xs font-mono text-[#808080] py-4">No referrers recorded yet.</p>
                @endforelse
            </div>
        </div>

        <!-- Column 4: Browsers & OS -->
        <div class="bg-white border border-[#111111]/15 p-5">
            <h4 class="text-xs font-mono uppercase font-bold text-[#111111] border-b border-[#111111]/10 pb-2 mb-3">
                Top Browsers
            </h4>
            <div class="space-y-2.5">
                @forelse ($topBrowsers as $item)
                    @php
                        $pct = $totalPageviews > 0 ? round(($item->count / $totalPageviews) * 100) : 0;
                    @endphp
                    <div>
                        <div class="flex items-center justify-between text-xs font-mono">
                            <span class="truncate text-[#111111] font-medium">{{ $item->browser ?: 'Browser' }}</span>
                            <span class="text-[#808080] shrink-0">{{ $item->count }}</span>
                        </div>
                        <div class="w-full bg-[#F5F1E8] h-1.5 mt-1 overflow-hidden">
                            <div class="bg-[#808080] h-full" style="width: {{ $pct }}%;"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-xs font-mono text-[#808080] py-4">No browser logs yet.</p>
                @endforelse
            </div>
        </div>

    </div>

    <!-- Top Pages & AI Bot Radar Split Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Top Content / Pages -->
        <div class="bg-white border border-[#111111]/15 p-6">
            <h4 class="text-xs font-mono uppercase font-bold text-[#111111] border-b border-[#111111]/10 pb-3 mb-4 flex items-center justify-between">
                <span>Most Visited URLs / Articles</span>
                <span class="text-[10px] text-[#808080]">Ranked by views</span>
            </h4>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs font-mono">
                    <thead>
                        <tr class="border-b border-[#111111]/15 text-[#808080] uppercase text-[10px]">
                            <th class="py-2">Route Path</th>
                            <th class="py-2 text-right">Hits</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#111111]/10">
                        @forelse ($topPages as $page)
                            <tr class="hover:bg-[#F5F1E8]/40 transition-colors">
                                <td class="py-2.5 truncate max-w-[280px]">
                                    <a href="{{ $page->current_page }}" target="_blank" class="hover:underline text-[#111111]">
                                        {{ $page->current_page }}
                                    </a>
                                </td>
                                <td class="py-2.5 text-right font-bold text-[#111111]">
                                    {{ number_format($page->count) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="py-4 text-center text-[#808080]">No pages recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- AI & Search Bot Activity Radar -->
        <div class="bg-white border border-[#111111]/15 p-6">
            <h4 class="text-xs font-mono uppercase font-bold text-[#111111] border-b border-[#111111]/10 pb-3 mb-4 flex items-center justify-between">
                <span>AI Crawler & Search Engine Activity</span>
                <span class="text-[10px] text-[#808080]">LLM & Crawler Detection</span>
            </h4>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs font-mono">
                    <thead>
                        <tr class="border-b border-[#111111]/15 text-[#808080] uppercase text-[10px]">
                            <th class="py-2">Crawler Signature</th>
                            <th class="py-2">Target Page</th>
                            <th class="py-2 text-right">Timestamp</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#111111]/10">
                        @forelse ($recentBots as $bot)
                            <tr class="hover:bg-[#F5F1E8]/40 transition-colors">
                                <td class="py-2.5">
                                    <span class="px-2 py-0.5 bg-[#F5F1E8] border border-[#111111]/20 font-bold text-[#111111]">
                                        {{ $bot->bot_name ?: 'Bot Client' }}
                                    </span>
                                </td>
                                <td class="py-2.5 truncate max-w-[200px] text-[#808080]">
                                    {{ $bot->current_page }}
                                </td>
                                <td class="py-2.5 text-right text-[#808080]">
                                    {{ $bot->created_at->diffForHumans() }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-4 text-center text-[#808080]">No bots detected yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- Live Stream Visitor Log Table -->
    <div class="bg-white border border-[#111111]/15">
        <div class="p-4 border-b border-[#111111]/10 bg-[#F5F1E8]/40 flex items-center justify-between">
            <div>
                <h3 class="text-xs font-mono uppercase font-bold text-[#111111]">Real-Time Traffic Stream Feed</h3>
                <p class="text-xs text-[#808080] mt-0.5">Chronological record of visitor requests with GeoIP and device telemetry.</p>
            </div>
            <div class="text-[11px] font-mono text-[#808080] flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Auto-polls every 8s</span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs font-mono">
                <thead>
                    <tr class="border-b border-[#111111]/15 bg-[#F5F1E8] uppercase tracking-wider text-[#808080] text-[10px]">
                        <th class="py-3 px-4">Location</th>
                        <th class="py-3 px-4">Device & OS</th>
                        <th class="py-3 px-4">Browser</th>
                        <th class="py-3 px-4">Referrer</th>
                        <th class="py-3 px-4">Page Requested</th>
                        <th class="py-3 px-4">Type</th>
                        <th class="py-3 px-4 text-right">Time</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#111111]/10" id="streamTableBody">
                    @forelse ($recentStream as $log)
                        <tr class="hover:bg-[#F5F1E8]/30 transition-colors">
                            <td class="py-3 px-4 font-bold text-[#111111]">
                                {{ $log->city && $log->city !== 'Unknown' ? $log->city . ', ' : '' }}{{ $log->country ?: 'Direct' }}
                                <div class="text-[10px] text-[#808080] font-normal font-mono">{{ $log->ip_address }}</div>
                            </td>
                            <td class="py-3 px-4 text-[#111111]">
                                {{ $log->device_type ?: 'Desktop' }} <span class="text-[#808080]">({{ $log->os ?: 'OS' }})</span>
                            </td>
                            <td class="py-3 px-4 text-[#808080] truncate max-w-[150px]">
                                {{ $log->browser ?: 'Browser' }}
                            </td>
                            <td class="py-3 px-4 text-[#808080] truncate max-w-[140px]">
                                {{ $log->referrer_source ?: 'Direct' }}
                            </td>
                            <td class="py-3 px-4 truncate max-w-[200px]">
                                <a href="{{ $log->current_page }}" target="_blank" class="hover:underline text-[#111111]">
                                    {{ $log->current_page }}
                                </a>
                            </td>
                            <td class="py-3 px-4">
                                @if ($log->is_bot)
                                    <span class="inline-flex items-center px-1.5 py-0.5 bg-amber-50 text-amber-800 border border-amber-300 text-[10px] uppercase font-bold">
                                        {{ $log->bot_name ?: 'Bot' }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-1.5 py-0.5 bg-emerald-50 text-emerald-800 border border-emerald-300 text-[10px] uppercase font-bold">
                                        Human
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right text-[#808080]">
                                {{ $log->created_at->diffForHumans() }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-[#808080]">No traffic logs recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($recentStream->hasPages())
            <div class="p-4 border-t border-[#111111]/10">
                {{ $recentStream->appends(['period' => $period])->links() }}
            </div>
        @endif
    </div>

</div>

<script>
function analyticsPoller() {
    return {
        loading: false,
        pollInterval: null,
        init() {
            this.pollInterval = setInterval(() => {
                this.refreshStats();
            }, 8000);
        },
        refreshStats() {
            this.loading = true;
            fetch('{{ route('admin.analytics.live_stats') }}')
                .then(res => res.json())
                .then(data => {
                    document.getElementById('livePulseText').innerText = data.active_now + ' Online Now';
                    document.getElementById('kpiActiveNow').innerText = data.active_now;
                    document.getElementById('kpiActiveHumans').innerText = data.active_humans + ' Humans';
                    document.getElementById('kpiActiveBots').innerText = data.active_bots + ' Bots / Crawlers';
                    this.loading = false;
                })
                .catch(() => {
                    this.loading = false;
                });
        }
    }
}
</script>
@endsection
