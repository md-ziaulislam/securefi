@extends('layouts.admin')

@section('title', 'Uptime & Health Telemetry')
@section('header_title', 'Real-Time Availability, Uptime & System Telemetry')

@section('content')
<div class="space-y-8">

    {{-- Public Heartbeat URL Notification Banner --}}
    <div class="bg-white border border-[#111111]/15 p-4 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div class="space-y-1">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full {{ $health['database']['connected'] ? 'bg-emerald-500 animate-pulse' : 'bg-red-500' }}"></span>
                <span class="text-xs font-mono font-bold text-[#111111] uppercase tracking-wider">Heartbeat Endpoint</span>
            </div>
            <p class="text-xs font-mono text-[#808080]">External uptime monitors ping this URL every 1-5 minutes to verify service availability.</p>
        </div>
        <div class="flex items-center gap-2 w-full md:w-auto">
            <input type="text" id="heartbeat-url" value="{{ $health['heartbeat_url'] }}" readonly
                class="px-3 py-1.5 text-xs font-mono bg-[#F8F8F6] border border-[#111111]/30 w-full md:w-80 select-all">
            <button type="button" onclick="copyHeartbeatUrl()" id="copy-btn"
                class="px-3 py-1.5 text-xs font-mono uppercase tracking-wider bg-[#111111] text-white hover:bg-black transition-colors whitespace-nowrap">
                Copy
            </button>
            <a href="{{ $health['heartbeat_url'] }}" target="_blank"
                class="px-3 py-1.5 text-xs font-mono uppercase tracking-wider bg-white border border-[#111111] hover:bg-[#F8F8F6] text-[#111111] transition-colors whitespace-nowrap">
                Test ↗
            </a>
        </div>
    </div>

    {{-- Top Telemetry / Ratio Cards Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">

        {{-- Status Card --}}
        <div class="bg-white border border-[#111111]/15 p-4 flex flex-col justify-between">
            <p class="text-[10px] font-mono uppercase tracking-wider text-[#808080]">Overall Status</p>
            <div class="text-sm font-mono font-bold my-2 flex items-center gap-2 {{ $uptimeData['summary']['is_up'] ? 'text-emerald-600' : 'text-red-600' }}">
                <span class="w-2.5 h-2.5 rounded-full {{ $uptimeData['summary']['is_up'] ? 'bg-emerald-500' : 'bg-red-500' }}"></span>
                <span class="truncate">{{ $uptimeData['summary']['status_label'] }}</span>
            </div>
            <p class="text-[10px] font-mono text-[#808080]">
                {{ $uptimeData['configured'] ? 'Via UptimeRobot' : 'Local Health Check' }}
            </p>
        </div>

        {{-- 24 Hours Ratio --}}
        <div class="bg-white border border-[#111111]/15 p-4 flex flex-col justify-between">
            <p class="text-[10px] font-mono uppercase tracking-wider text-[#808080]">24 Hours Uptime</p>
            <div class="text-2xl font-bold font-mono text-[#111111] my-1">
                {{ $uptimeData['summary']['ratio_24h'] }}<span class="text-sm">%</span>
            </div>
            <p class="text-[10px] font-mono text-emerald-600">Past 24 Hours</p>
        </div>

        {{-- 7 Days Ratio --}}
        <div class="bg-white border border-[#111111]/15 p-4 flex flex-col justify-between">
            <p class="text-[10px] font-mono uppercase tracking-wider text-[#808080]">7 Days Uptime</p>
            <div class="text-2xl font-bold font-mono text-[#111111] my-1">
                {{ $uptimeData['summary']['ratio_7d'] }}<span class="text-sm">%</span>
            </div>
            <p class="text-[10px] font-mono text-emerald-600">Past 7 Days</p>
        </div>

        {{-- 30 Days Ratio --}}
        <div class="bg-white border border-[#111111]/15 p-4 flex flex-col justify-between">
            <p class="text-[10px] font-mono uppercase tracking-wider text-[#808080]">30 Days Uptime</p>
            <div class="text-2xl font-bold font-mono text-[#111111] my-1">
                {{ $uptimeData['summary']['ratio_30d'] }}<span class="text-sm">%</span>
            </div>
            <p class="text-[10px] font-mono text-emerald-600">Target: 99.9% SLA</p>
        </div>

        {{-- Average Response Time --}}
        <div class="bg-white border border-[#111111]/15 p-4 flex flex-col justify-between">
            <p class="text-[10px] font-mono uppercase tracking-wider text-[#808080]">Avg Response Time</p>
            <div class="text-2xl font-bold font-mono text-[#111111] my-1">
                {{ $uptimeData['summary']['avg_response_time'] > 0 ? $uptimeData['summary']['avg_response_time'] . 'ms' : $health['latency_ms'] . 'ms' }}
            </div>
            <p class="text-[10px] font-mono text-[#808080]">Origin Latency</p>
        </div>

    </div>

    {{-- Main Content Grid: Monitors & Telemetry (7 cols) + Settings & Guide (5 cols) --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

        {{-- Left: Monitors List & Local Health (7 cols) --}}
        <div class="lg:col-span-7 space-y-6">

            {{-- External Monitors Table --}}
            <div class="bg-white border border-[#111111]/15 p-6 space-y-4">
                <div class="border-b border-[#111111]/10 pb-3 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-sm text-[#111111]">1. Active External Monitors</h3>
                        <p class="text-xs font-mono text-[#808080]">Monitored endpoints tracked via UptimeRobot.</p>
                    </div>
                    <form action="{{ route('admin.uptime.refresh') }}" method="POST">
                        @csrf
                        <button type="submit" class="px-3 py-1 text-xs font-mono bg-[#F8F8F6] border border-[#111111]/30 hover:border-[#111111] text-[#111111] transition-colors">
                            ↻ Refresh
                        </button>
                    </form>
                </div>

                @if(!empty($uptimeData['monitors']))
                    <div class="space-y-4">
                        @foreach($uptimeData['monitors'] as $m)
                            <div class="p-4 border border-[#111111]/15 bg-[#F8F8F6] space-y-3">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="w-2 h-2 rounded-full {{ $m['is_up'] ? 'bg-emerald-500' : 'bg-red-500' }}"></span>
                                            <h4 class="text-xs font-mono font-bold text-[#111111]">{{ $m['name'] }}</h4>
                                        </div>
                                        <p class="text-[10px] font-mono text-[#808080] mt-0.5 truncate max-w-sm">{{ $m['url'] }}</p>
                                    </div>
                                    <span class="px-2 py-0.5 text-[10px] font-mono uppercase {{ $m['is_up'] ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $m['status_label'] }}
                                    </span>
                                </div>

                                <div class="grid grid-cols-4 gap-2 pt-2 border-t border-[#111111]/10 text-center text-xs font-mono">
                                    <div>
                                        <span class="text-[10px] text-[#808080] block">24h Ratio</span>
                                        <span class="font-bold text-[#111111]">{{ $m['ratio_24h'] }}%</span>
                                    </div>
                                    <div>
                                        <span class="text-[10px] text-[#808080] block">7d Ratio</span>
                                        <span class="font-bold text-[#111111]">{{ $m['ratio_7d'] }}%</span>
                                    </div>
                                    <div>
                                        <span class="text-[10px] text-[#808080] block">30d Ratio</span>
                                        <span class="font-bold text-[#111111]">{{ $m['ratio_30d'] }}%</span>
                                    </div>
                                    <div>
                                        <span class="text-[10px] text-[#808080] block">Avg Response</span>
                                        <span class="font-bold text-[#111111]">{{ $m['avg_response_time'] }}ms</span>
                                    </div>
                                </div>

                                {{-- Recent Logs --}}
                                @if(!empty($m['logs']))
                                    <div class="pt-2 border-t border-[#111111]/10 space-y-1">
                                        <p class="text-[10px] font-mono uppercase text-[#808080] font-bold">Recent Events:</p>
                                        @foreach(array_slice($m['logs'], 0, 3) as $log)
                                            <div class="text-[10px] font-mono flex items-center justify-between text-[#808080]">
                                                <span>{{ ($log['type'] ?? 1) == 2 ? '🟢 Operational' : '🔴 Outage' }} ({{ $log['reason']['detail'] ?? 'Normal check' }})</span>
                                                <span>{{ isset($log['datetime']) ? \Carbon\Carbon::createFromTimestamp($log['datetime'])->diffForHumans() : '' }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-6 border border-[#111111]/15 bg-[#F8F8F6] text-center space-y-2">
                        <p class="text-xs font-mono text-[#111111] font-bold">No External Monitors Synced</p>
                        <p class="text-xs font-mono text-[#808080]">
                            {{ $uptimeData['message'] }}
                        </p>
                        <p class="text-[10px] font-mono text-[#808080]">
                            Configure your UptimeRobot API Key in the panel on the right to sync live monitors.
                        </p>
                    </div>
                @endif
            </div>

            {{-- Local System Health Telemetry Card --}}
            <div class="bg-white border border-[#111111]/15 p-6 space-y-4">
                <div class="border-b border-[#111111]/10 pb-3">
                    <h3 class="font-bold text-sm text-[#111111]">2. Internal System Telemetry</h3>
                    <p class="text-xs font-mono text-[#808080]">Self-diagnostics and infrastructure health parameters.</p>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-xs font-mono">
                    <div class="p-3 bg-[#F8F8F6] border border-[#111111]/10 space-y-1">
                        <span class="text-[10px] text-[#808080] uppercase">Database Connection</span>
                        <p class="font-bold {{ $health['database']['connected'] ? 'text-emerald-600' : 'text-red-600' }}">
                            {{ $health['database']['connected'] ? '● Connected (' . $health['database']['driver'] . ')' : '✕ Disconnected' }}
                        </p>
                    </div>

                    <div class="p-3 bg-[#F8F8F6] border border-[#111111]/10 space-y-1">
                        <span class="text-[10px] text-[#808080] uppercase">Origin Latency</span>
                        <p class="font-bold text-[#111111]">{{ $health['latency_ms'] }} ms</p>
                    </div>

                    <div class="p-3 bg-[#F8F8F6] border border-[#111111]/10 space-y-1">
                        <span class="text-[10px] text-[#808080] uppercase">PHP / Laravel</span>
                        <p class="font-bold text-[#111111]">v{{ $health['php_version'] }} / {{ $health['laravel_version'] }}</p>
                    </div>

                    <div class="p-3 bg-[#F8F8F6] border border-[#111111]/10 space-y-1">
                        <span class="text-[10px] text-[#808080] uppercase">Memory Usage</span>
                        <p class="font-bold text-[#111111]">{{ $health['memory_usage_mb'] }} MB (Peak {{ $health['memory_peak_mb'] }} MB)</p>
                    </div>

                    <div class="p-3 bg-[#F8F8F6] border border-[#111111]/10 space-y-1">
                        <span class="text-[10px] text-[#808080] uppercase">Disk Available</span>
                        <p class="font-bold text-[#111111]">
                            {{ $health['disk_free_gb'] !== null ? $health['disk_free_gb'] . ' GB Free' : 'N/A' }}
                        </p>
                    </div>

                    <div class="p-3 bg-[#F8F8F6] border border-[#111111]/10 space-y-1">
                        <span class="text-[10px] text-[#808080] uppercase">Disk Usage</span>
                        <p class="font-bold text-[#111111]">
                            {{ $health['disk_usage_pct'] !== null ? $health['disk_usage_pct'] . '%' : 'Normal' }}
                        </p>
                    </div>
                </div>
            </div>

        </div>

        {{-- Right: Settings & UptimeRobot Setup Guide (5 cols) --}}
        <div class="lg:col-span-5 space-y-6">

            {{-- API Settings Card --}}
            <div class="bg-white border border-[#111111]/15 p-6 space-y-5">
                <div class="border-b border-[#111111]/10 pb-3">
                    <h3 class="font-bold text-sm text-[#111111]">3. Monitoring Integration</h3>
                    <p class="text-xs font-mono text-[#808080]">Configure external uptime service connection.</p>
                </div>

                <form action="{{ route('admin.uptime.settings') }}" method="POST" class="space-y-4">
                    @csrf

                    {{-- Provider --}}
                    <div>
                        <label for="uptime_provider" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1 font-medium">
                            Monitoring Service Provider
                        </label>
                        <select id="uptime_provider" name="uptime_provider"
                            class="w-full px-3 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                            <option value="uptimerobot" {{ ($settings['uptime_provider'] ?? 'uptimerobot') === 'uptimerobot' ? 'selected' : '' }}>
                                UptimeRobot (Free 50 Monitors / 5-min checks)
                            </option>
                            <option value="betteruptime" {{ ($settings['uptime_provider'] ?? '') === 'betteruptime' ? 'selected' : '' }}>
                                Better Stack / Better Uptime
                            </option>
                            <option value="custom" {{ ($settings['uptime_provider'] ?? '') === 'custom' ? 'selected' : '' }}>
                                Custom Pinger / Internal Heartbeat
                            </option>
                        </select>
                    </div>

                    {{-- API Key --}}
                    <div>
                        <label for="uptime_api_key" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1 font-medium">
                            UptimeRobot API Key (Read-Only)
                        </label>
                        <input type="password" id="uptime_api_key" name="uptime_api_key"
                            placeholder="{{ !empty($settings['uptime_api_key']) ? '••••••••••••••••••••••••••••••••' : 'u123456-abcdef...' }}"
                            autocomplete="new-password"
                            class="w-full px-3 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                        <p class="text-[10px] font-mono text-[#808080] mt-1">Use a "Read-Only API Key" for maximum security. Leave blank to preserve current key.</p>
                    </div>

                    {{-- Dashboard Widget Toggle --}}
                    <div class="p-3 bg-[#F8F8F6] border border-[#111111]/15">
                        <label class="flex items-center gap-2.5 cursor-pointer">
                            <input type="checkbox" name="uptime_widget_enabled" value="1"
                                {{ ($settings['uptime_widget_enabled'] ?? '1') === '1' ? 'checked' : '' }}
                                class="w-4 h-4 rounded-none border-[#111111] text-[#111111] focus:ring-0">
                            <div>
                                <span class="text-xs font-mono font-bold text-[#111111] uppercase tracking-wider">Show Uptime Widget on Dashboard</span>
                                <p class="text-[10px] font-mono text-[#808080]">Displays a live status badge & 30-day ratio on main admin dashboard.</p>
                            </div>
                        </label>
                    </div>

                    <button type="submit" class="btn-primary w-full text-xs font-mono uppercase tracking-wider py-2.5">
                        Save Monitoring Parameters →
                    </button>
                </form>
            </div>

            {{-- 2-Minute Setup Guide Card --}}
            <div class="bg-white border border-[#111111]/15 p-6 space-y-4">
                <div class="border-b border-[#111111]/10 pb-3">
                    <h3 class="font-bold text-sm text-[#111111]">UptimeRobot Free Setup Guide</h3>
                    <p class="text-xs font-mono text-[#808080]">Complete setup in under 2 minutes.</p>
                </div>

                <ol class="text-xs font-mono text-[#111111] space-y-3 list-decimal list-inside leading-relaxed">
                    <li>
                        Sign up for free at <a href="https://uptimerobot.com" target="_blank" class="underline hover:text-primary font-bold">uptimerobot.com</a> (Includes 50 free monitors with 5-min checks).
                    </li>
                    <li>
                        Click <strong>+ Add New Monitor</strong>.
                        <ul class="list-disc list-inside pl-4 pt-1 space-y-0.5 text-[#808080]">
                            <li>Monitor Type: <strong>HTTP(s)</strong></li>
                            <li>Friendly Name: <strong>SecuroFi.Tech Production</strong></li>
                            <li>URL: <code class="bg-[#F8F8F6] px-1 text-[#111111]">{{ $health['heartbeat_url'] }}</code></li>
                            <li>Monitoring Interval: <strong>5 minutes</strong></li>
                        </ul>
                    </li>
                    <li>
                        In UptimeRobot, go to <strong>My Settings</strong> → Scroll down to <strong>API Settings</strong>.
                    </li>
                    <li>
                        Under <strong>Read-Only API Key</strong>, click <em>Create Read-Only API Key</em>.
                    </li>
                    <li>
                        Copy the key, paste it into the field above, and click <strong>Save Monitoring Parameters</strong>.
                    </li>
                </ol>
            </div>

        </div>

    </div>

</div>

@push('scripts')
<script>
    function copyHeartbeatUrl() {
        var input = document.getElementById('heartbeat-url');
        var btn = document.getElementById('copy-btn');
        if (!input) return;

        input.select();
        input.setSelectionRange(0, 99999);

        if (navigator.clipboard) {
            navigator.clipboard.writeText(input.value).then(function() {
                btn.innerText = 'Copied!';
                setTimeout(function() { btn.innerText = 'Copy'; }, 2000);
            });
        } else {
            document.execCommand('copy');
            btn.innerText = 'Copied!';
            setTimeout(function() { btn.innerText = 'Copy'; }, 2000);
        }
    }
</script>
@endpush
@endsection
