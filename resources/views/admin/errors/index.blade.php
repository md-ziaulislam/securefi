@extends('layouts.admin')

@section('title', 'Error Monitoring & Sentry')
@section('header_title', 'Real-Time Error Tracking & Exception Reporting')

@section('content')
<div class="space-y-8">

    {{-- Telemetry Cards Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        {{-- Status --}}
        <div class="bg-white border border-[#111111]/15 p-4 flex items-center justify-between">
            <div>
                <p class="text-[10px] font-mono uppercase tracking-wider text-[#808080]">Error Tracking</p>
                <p class="text-sm font-mono font-bold mt-1 {{ $isEnabled ? 'text-emerald-600' : 'text-amber-600' }}">
                    {{ $isEnabled ? '● Active / Reporting' : '○ Standby / Disabled' }}
                </p>
            </div>
            <span class="inline-flex items-center px-2 py-0.5 text-[10px] font-mono {{ $isEnabled ? 'bg-emerald-50 text-emerald-700 border border-emerald-300' : 'bg-amber-50 text-amber-700 border border-amber-300' }}">
                {{ $isEnabled ? 'Sentry Hook Active' : 'Unmonitored' }}
            </span>
        </div>

        {{-- Sentry DSN --}}
        <div class="bg-white border border-[#111111]/15 p-4 flex items-center justify-between">
            <div>
                <p class="text-[10px] font-mono uppercase tracking-wider text-[#808080]">Sentry DSN Target</p>
                <p class="text-sm font-mono font-bold mt-1 text-[#111111]">
                    {{ $isConfigured ? 'Project #' . ($parsedDsn['project_id'] ?? 'Connected') : 'Not Configured' }}
                </p>
            </div>
            <span class="inline-flex items-center px-2 py-0.5 text-[10px] font-mono {{ $isConfigured ? 'bg-emerald-50 text-emerald-700 border border-emerald-300' : 'bg-red-50 text-red-700 border border-red-300' }}">
                {{ $isConfigured ? 'DSN Valid' : 'Missing DSN' }}
            </span>
        </div>

        {{-- Environment --}}
        <div class="bg-white border border-[#111111]/15 p-4 flex items-center justify-between">
            <div>
                <p class="text-[10px] font-mono uppercase tracking-wider text-[#808080]">Environment & Runtime</p>
                <p class="text-sm font-mono font-bold text-[#111111] mt-1 uppercase">
                    {{ config('app.env') }} (PHP {{ PHP_VERSION }})
                </p>
            </div>
            <span class="inline-flex items-center px-2 py-0.5 text-[10px] font-mono bg-[#F8F8F6] text-[#111111] border border-[#111111]/20">
                Laravel {{ app()->version() }}
            </span>
        </div>
    </div>

    {{-- Main 2-Column Content Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

        {{-- Left: Configuration & Test Trigger (7 cols) --}}
        <div class="lg:col-span-7 space-y-6">

            {{-- Configuration Form --}}
            <div class="bg-white border border-[#111111]/15 p-6 space-y-6">
                <div class="border-b border-[#111111]/10 pb-3 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-sm text-[#111111]">1. Sentry Ingestion Parameters</h3>
                        <p class="text-xs font-mono text-[#808080]">Connect SecuroFi exception handler to Sentry.io.</p>
                    </div>
                    <span class="text-[10px] font-mono text-[#808080]">Sentry API v7</span>
                </div>

                <form action="{{ route('admin.errors.settings') }}" method="POST" class="space-y-5">
                    @csrf

                    {{-- Enable Toggle --}}
                    <div class="p-3 bg-[#F8F8F6] border border-[#111111]/15">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="error_monitoring_enabled" value="1"
                                {{ ($settings['error_monitoring_enabled'] ?? '0') === '1' ? 'checked' : '' }}
                                class="w-4 h-4 rounded-none border-[#111111] text-[#111111] focus:ring-0">
                            <div>
                                <span class="text-xs font-mono font-bold text-[#111111] uppercase tracking-wider">Enable Real-Time Error Reporting</span>
                                <p class="text-xs font-mono text-[#808080]">Intercepts uncaught production exceptions and dispatches them to Sentry before users encounter them.</p>
                            </div>
                        </label>
                    </div>

                    {{-- Sentry DSN --}}
                    <div>
                        <label for="sentry_dsn" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1 font-medium">
                            Sentry Client Key (DSN) *
                        </label>
                        <input type="text" id="sentry_dsn" name="sentry_dsn"
                            value="{{ old('sentry_dsn', $settings['sentry_dsn'] ?? '') }}"
                            placeholder="https://public_key@o12345.ingest.sentry.io/67890"
                            class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                        <p class="text-xs font-mono text-[#808080] mt-1">Found in your Sentry dashboard under Project Settings → Client Keys (DSN).</p>
                    </div>

                    {{-- User Context Toggle --}}
                    <div class="p-3 bg-[#F8F8F6] border border-[#111111]/15">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="sentry_send_user_context" value="1"
                                {{ ($settings['sentry_send_user_context'] ?? '1') === '1' ? 'checked' : '' }}
                                class="w-4 h-4 rounded-none border-[#111111] text-[#111111] focus:ring-0">
                            <div>
                                <span class="text-xs font-mono font-bold text-[#111111] uppercase tracking-wider">Include User Context</span>
                                <p class="text-xs font-mono text-[#808080]">Attaches authenticated user ID and email to error reports for faster reproduction.</p>
                            </div>
                        </label>
                    </div>

                    {{-- Traces Sample Rate --}}
                    <div>
                        <label for="sentry_traces_sample_rate" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1 font-medium">
                            Traces Sample Rate <span class="text-[#808080] normal-case">(0.0 to 1.0, default 0.2)</span>
                        </label>
                        <input type="number" id="sentry_traces_sample_rate" name="sentry_traces_sample_rate" step="0.05" min="0" max="1"
                            value="{{ old('sentry_traces_sample_rate', $settings['sentry_traces_sample_rate'] ?? '0.2') }}"
                            class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                        <p class="text-xs font-mono text-[#808080] mt-1">Controls the percentage of transactions captured for performance profiling (1.0 = 100%, 0.2 = 20%).</p>
                    </div>

                    <button type="submit" class="btn-primary w-full text-xs font-mono uppercase tracking-wider py-2.5">
                        Save Sentry Parameters →
                    </button>
                </form>
            </div>

            {{-- Live Ingestion Test Card --}}
            <div class="bg-white border border-[#111111]/15 p-6 space-y-4">
                <div class="border-b border-[#111111]/10 pb-3">
                    <h3 class="font-bold text-sm text-[#111111]">2. Live Exception Ingestion Test</h3>
                    <p class="text-xs font-mono text-[#808080]">Verify that Sentry is receiving events from your server.</p>
                </div>

                <div class="p-4 bg-[#F8F8F6] border border-[#111111]/15 text-xs font-mono space-y-2 text-[#111111]">
                    <p class="font-bold">What this test does:</p>
                    <p class="leading-relaxed text-[#808080]">
                        Clicking the button below dispatches a simulated <code>RuntimeException</code> payload with request metadata and stack trace to your configured Sentry DSN. If your DSN is valid, an incident notification will appear immediately in your Sentry console.
                    </p>
                </div>

                <form action="{{ route('admin.errors.test') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full bg-[#111111] text-white hover:bg-black font-mono text-xs uppercase tracking-wider py-3 transition-colors font-medium flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        Send Test Exception to Sentry Now →
                    </button>
                </form>
            </div>

        </div>

        {{-- Right: Telemetry & Guide (5 cols) --}}
        <div class="lg:col-span-5 space-y-6">

            {{-- Telemetry Details Card --}}
            <div class="bg-white border border-[#111111]/15 p-6 space-y-4">
                <div class="border-b border-[#111111]/10 pb-3">
                    <h3 class="font-bold text-sm text-[#111111]">Telemetry & Ingestion Host</h3>
                    <p class="text-xs font-mono text-[#808080]">Parsed Sentry target information.</p>
                </div>

                <div class="space-y-3 text-xs font-mono">
                    <div class="p-3 bg-[#F8F8F6] border border-[#111111]/10 flex justify-between items-center">
                        <span class="text-[#808080]">Project ID</span>
                        <span class="font-bold text-[#111111]">{{ $parsedDsn['project_id'] ?? 'Not set' }}</span>
                    </div>

                    <div class="p-3 bg-[#F8F8F6] border border-[#111111]/10 flex justify-between items-center">
                        <span class="text-[#808080]">Ingestion Endpoint</span>
                        <span class="font-bold text-[#111111] truncate max-w-[200px]" title="{{ $parsedDsn['host'] ?? 'N/A' }}">
                            {{ $parsedDsn['host'] ?? 'N/A' }}
                        </span>
                    </div>

                    <div class="p-3 bg-[#F8F8F6] border border-[#111111]/10 flex justify-between items-center">
                        <span class="text-[#808080]">Last Event Dispatched</span>
                        <span class="font-bold text-[#111111]">
                            {{ $lastErrorAt ? \Carbon\Carbon::parse($lastErrorAt)->diffForHumans() : 'None' }}
                        </span>
                    </div>

                    <div class="p-3 bg-[#F8F8F6] border border-[#111111]/10 flex justify-between items-center">
                        <span class="text-[#808080]">Exception Reporter</span>
                        <span class="font-bold text-emerald-600">bootstrap/app.php Hooked</span>
                    </div>
                </div>
            </div>

            {{-- Sentry Free Setup Instructions --}}
            <div class="bg-white border border-[#111111]/15 p-6 space-y-4">
                <div class="border-b border-[#111111]/10 pb-3">
                    <h3 class="font-bold text-sm text-[#111111]">Sentry Free Setup Guide</h3>
                    <p class="text-xs font-mono text-[#808080]">Complete in under 2 minutes (Free 5K errors/mo).</p>
                </div>

                <ol class="text-xs font-mono text-[#111111] space-y-3 list-decimal list-inside leading-relaxed">
                    <li>
                        Create a free account at <a href="https://sentry.io/signup/" target="_blank" class="underline hover:text-primary font-bold">sentry.io</a>.
                    </li>
                    <li>
                        Click <strong>Create Project</strong> and choose <strong>Laravel</strong> (or <strong>PHP</strong>).
                    </li>
                    <li>
                        Name the project <strong>SecuroFi</strong> and click <strong>Create Project</strong>.
                    </li>
                    <li>
                        Navigate to <strong>Project Settings</strong> → <strong>Client Keys (DSN)</strong> in the left sidebar.
                    </li>
                    <li>
                        Copy the <strong>DSN</strong> string (starts with <code>https://...</code>), paste it in the field on the left, check <em>Enable Real-Time Error Reporting</em>, and click <strong>Save Sentry Parameters</strong>.
                    </li>
                    <li>
                        Click <strong>Send Test Exception</strong> to verify instant delivery.
                    </li>
                </ol>
            </div>

        </div>

    </div>

</div>
@endsection
