@extends('layouts.admin')

@section('title', 'Security & Audit Trail')
@section('header_title', 'Security Hardening & Audit Trail')

@section('header_badge')
    <span class="text-[11px] font-mono uppercase px-2 py-0.5 border border-[#111111]/20 bg-[#F5F1E8] text-[#111111]">
        Phase 8 Compliance
    </span>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Top Navigation Tabs -->
    <div class="flex items-center justify-between border-b border-[#111111]/15 pb-0">
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.security.index', ['tab' => 'audit']) }}" 
               class="px-4 py-2.5 text-xs font-mono uppercase tracking-wider transition-colors border-b-2 {{ $activeTab === 'audit' ? 'border-[#111111] text-[#111111] font-bold bg-white' : 'border-transparent text-[#808080] hover:text-[#111111]' }}">
                Login Audit Trail ({{ $loginHistory->total() }})
            </a>
            <a href="{{ route('admin.security.index', ['tab' => 'health']) }}" 
               class="px-4 py-2.5 text-xs font-mono uppercase tracking-wider transition-colors border-b-2 {{ $activeTab === 'health' ? 'border-[#111111] text-[#111111] font-bold bg-white' : 'border-transparent text-[#808080] hover:text-[#111111]' }}">
                System Security Health
            </a>
            <a href="{{ route('admin.security.index', ['tab' => 'backups']) }}" 
               class="px-4 py-2.5 text-xs font-mono uppercase tracking-wider transition-colors border-b-2 {{ $activeTab === 'backups' ? 'border-[#111111] text-[#111111] font-bold bg-white' : 'border-transparent text-[#808080] hover:text-[#111111]' }}">
                Database Backups & Cloud Storage ({{ count($backups) }})
            </a>
            <a href="{{ route('admin.security.index', ['tab' => 'activity']) }}" 
               class="px-4 py-2.5 text-xs font-mono uppercase tracking-wider transition-colors border-b-2 {{ $activeTab === 'activity' ? 'border-[#111111] text-[#111111] font-bold bg-white' : 'border-transparent text-[#808080] hover:text-[#111111]' }}">
                Activity Logs ({{ $activityLogs->total() }})
            </a>
        </div>

        <div class="flex items-center gap-2 pb-2">
            @if ($activeTab === 'backups')
                <div class="flex items-center gap-2">
                    <form action="{{ route('admin.security.backup') }}" method="POST">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 border border-[#111111] bg-white text-[#111111] text-xs font-mono uppercase tracking-wider hover:bg-[#111111] hover:text-white transition-colors" title="Generate fast SQL database dump">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2 1 3 3 3h10c2 0 3-1 3-3V7c0-2-1-3-3-3H7C5 4 4 5 4 7z"/></svg>
                            <span>Quick DB Dump</span>
                        </button>
                    </form>

                    <form action="{{ route('admin.security.backup_cloud') }}" method="POST">
                        @csrf
                        <input type="hidden" name="backup_type" value="full">
                        <input type="hidden" name="upload_cloud" value="{{ $cloudConfig['enabled'] ? '1' : '0' }}">
                        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-[#111111] text-white text-xs font-mono uppercase tracking-wider hover:bg-neutral-800 transition-colors" title="Run full DB + Media files backup">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/></svg>
                            <span>Full Backup (DB + Media)</span>
                        </button>
                    </form>

                    <form action="{{ route('admin.security.backup_clean') }}" method="POST" onsubmit="return confirm('Prune aged backups according to retention policy (7 daily + 4 weekly)?');">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1.5 border border-[#111111]/20 bg-[#F5F1E8] text-[#111111] text-xs font-mono uppercase tracking-wider hover:bg-[#111111] hover:text-white transition-colors" title="Prune backups older than retention policy">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            <span>Prune Retention</span>
                        </button>
                    </form>
                </div>
            @elseif ($activeTab === 'audit' && $loginHistory->total() > 0)
                <form action="{{ route('admin.security.clear_logins') }}" method="POST" onsubmit="return confirm('Purge all historical login logs? This action cannot be undone.');">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 border border-red-200 text-red-700 bg-red-50 text-xs font-mono uppercase tracking-wider hover:bg-red-100 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        <span>Clear Login History</span>
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- TAB 1: LOGIN AUDIT TRAIL -->
    @if ($activeTab === 'audit')
        <div class="bg-white border border-[#111111]/15">
            <div class="p-4 border-b border-[#111111]/10 bg-[#F5F1E8]/40 flex items-center justify-between">
                <div>
                    <h3 class="text-xs font-mono uppercase font-bold text-[#111111]">Login Activity Audit Log</h3>
                    <p class="text-xs text-[#808080] mt-0.5">Captures every successful and failed login attempt with device, IP, and location.</p>
                </div>
                <span class="text-xs font-mono text-[#808080]">Rate Limit: 5 attempts / 300s decay</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-[#111111]/15 bg-[#F5F1E8] font-mono uppercase tracking-wider text-[#808080]">
                            <th class="py-3 px-4">User</th>
                            <th class="py-3 px-4">IP Address</th>
                            <th class="py-3 px-4">Device & OS</th>
                            <th class="py-3 px-4">Browser</th>
                            <th class="py-3 px-4">Location</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Timestamp</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#111111]/10">
                        @forelse ($loginHistory as $log)
                            <tr class="hover:bg-[#F5F1E8]/30 transition-colors">
                                <td class="py-3 px-4">
                                    <div class="font-bold text-[#111111]">{{ $log->user->name ?? 'Unknown / Deleted' }}</div>
                                    <div class="font-mono text-[11px] text-[#808080]">{{ $log->user->email ?? 'N/A' }}</div>
                                </td>
                                <td class="py-3 px-4 font-mono font-medium text-[#111111]">
                                    {{ $log->ip_address ?? '127.0.0.1' }}
                                </td>
                                <td class="py-3 px-4 text-[#111111]">
                                    <span class="inline-flex items-center gap-1 font-mono text-[11px]">
                                        <svg class="w-3 h-3 text-[#808080]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                        {{ $log->device ?: 'Desktop' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-[#111111] font-mono text-[11px]">
                                    {{ $log->browser ?: 'Standard Web Browser' }}
                                </td>
                                <td class="py-3 px-4 text-[#808080] font-mono text-[11px]">
                                    {{ $log->location ?: 'Local / Secure' }}
                                </td>
                                <td class="py-3 px-4">
                                    @if ($log->status === 'success' || $log->status === 'success_2fa')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-emerald-50 text-emerald-800 border border-emerald-300 font-mono text-[10px] uppercase font-bold">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            {{ $log->status === 'success_2fa' ? '2FA Verified' : 'Success' }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-rose-50 text-rose-800 border border-rose-300 font-mono text-[10px] uppercase font-bold">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                            Failed
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right font-mono text-[11px] text-[#808080]">
                                    {{ $log->created_at->format('M d, Y H:i:s') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-[#808080] font-mono text-xs">
                                    No login history records found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($loginHistory->hasPages())
                <div class="p-4 border-t border-[#111111]/10">
                    {{ $loginHistory->appends(['tab' => 'audit'])->links() }}
                </div>
            @endif
        </div>
    @endif

    <!-- TAB 2: SYSTEM SECURITY HEALTH -->
    @if ($activeTab === 'health')
        <div class="space-y-6">
            <div class="bg-white border border-[#111111]/15 p-6">
                <div class="flex items-center justify-between pb-4 border-b border-[#111111]/10">
                    <div>
                        <h3 class="text-sm font-bold tracking-tight text-[#111111]">SecuroFi Security Architecture Audit</h3>
                        <p class="text-xs text-[#808080] mt-0.5">Verification of critical security layers, protection headers, and runtime constraints.</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-1 bg-emerald-100 text-emerald-900 border border-emerald-300 font-mono text-xs uppercase font-bold">
                            Health: Hardened
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
                    @foreach ($securityChecks as $key => $check)
                        <div class="p-4 border border-[#111111]/15 bg-[#F5F1E8]/20 flex items-start gap-3">
                            <div class="mt-0.5">
                                @if ($check['status'])
                                    <div class="w-5 h-5 bg-emerald-500 text-white flex items-center justify-center">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    </div>
                                @else
                                    <div class="w-5 h-5 bg-amber-500 text-white flex items-center justify-center">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-xs font-bold text-[#111111] uppercase font-mono">{{ $check['name'] }}</div>
                                <div class="text-xs text-[#808080] mt-1 font-mono break-words">{{ $check['detail'] }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Server Specs Card -->
            <div class="bg-white border border-[#111111]/15 p-6">
                <h3 class="text-xs font-mono uppercase font-bold text-[#111111] mb-4">Environment & Runtime Specifications</h3>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 font-mono text-xs">
                    <div class="p-3 border border-[#111111]/10 bg-[#F5F1E8]/30">
                        <div class="text-[#808080] text-[10px] uppercase">PHP Version</div>
                        <div class="font-bold text-[#111111] text-sm mt-0.5">{{ PHP_VERSION }}</div>
                    </div>
                    <div class="p-3 border border-[#111111]/10 bg-[#F5F1E8]/30">
                        <div class="text-[#808080] text-[10px] uppercase">Laravel Version</div>
                        <div class="font-bold text-[#111111] text-sm mt-0.5">{{ app()->version() }}</div>
                    </div>
                    <div class="p-3 border border-[#111111]/10 bg-[#F5F1E8]/30">
                        <div class="text-[#808080] text-[10px] uppercase">Server Software</div>
                        <div class="font-bold text-[#111111] text-sm mt-0.5 truncate">{{ $_SERVER['SERVER_SOFTWARE'] ?? 'PHP CLI / XAMPP' }}</div>
                    </div>
                    <div class="p-3 border border-[#111111]/10 bg-[#F5F1E8]/30">
                        <div class="text-[#808080] text-[10px] uppercase">DB Driver</div>
                        <div class="font-bold text-[#111111] text-sm mt-0.5">MySQL / MariaDB</div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- TAB 3: BACKUPS & CLOUD STORAGE -->
    @if ($activeTab === 'backups')
        <div class="space-y-6">

            <!-- Cloud Storage Status & Retention Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- 1. Cloud Storage Status Card -->
                <div class="p-4 bg-white border border-[#111111]/15 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-mono uppercase tracking-widest text-[#808080]">Storage Destination</span>
                            @if ($cloudConfig['enabled'] && !empty($cloudConfig['bucket']))
                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 bg-emerald-50 text-emerald-800 border border-emerald-300 font-mono text-[9px] uppercase font-bold">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    S3 Active
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 bg-[#F5F1E8] text-[#808080] border border-[#111111]/15 font-mono text-[9px] uppercase font-bold">
                                    Local Only
                                </span>
                            @endif
                        </div>
                        <div class="text-base font-mono font-bold text-[#111111] mt-2 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-[#808080]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 00-9.78 2.096A4.001 4.001 0 003 15z"/></svg>
                            <span>{{ strtoupper($cloudConfig['provider']) }} Storage</span>
                        </div>
                        <p class="text-xs font-mono text-[#808080] mt-1 truncate">
                            @if ($cloudConfig['enabled'] && !empty($cloudConfig['bucket']))
                                Bucket: <strong class="text-[#111111]">{{ $cloudConfig['bucket'] }}</strong> ({{ $cloudConfig['region'] }})
                            @else
                                Offsite sync is inactive. Backups stored locally in <code class="text-[11px] bg-[#F5F1E8] px-1">storage/app/backups</code>.
                            @endif
                        </p>
                    </div>

                    <div class="mt-4 pt-3 border-t border-[#111111]/10 flex items-center justify-between">
                        <button type="button" onclick="document.getElementById('cloud-settings-drawer').classList.toggle('hidden')" class="text-xs font-mono uppercase tracking-wider text-[#111111] hover:underline font-bold flex items-center gap-1">
                            <span>Configure Cloud S3</span>
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>

                        @if (!empty($cloudConfig['key']) && !empty($cloudConfig['bucket']))
                            <form action="{{ route('admin.security.test_connection') }}" method="POST">
                                @csrf
                                <button type="submit" class="text-[11px] font-mono uppercase px-2 py-0.5 border border-[#111111] bg-white text-[#111111] hover:bg-[#111111] hover:text-white transition-colors">
                                    Test Connection
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                <!-- 2. Retention Policy Summary -->
                <div class="p-4 bg-white border border-[#111111]/15 flex flex-col justify-between">
                    <div>
                        <span class="text-[10px] font-mono uppercase tracking-widest text-[#808080]">Automated Retention Policy</span>
                        <div class="text-base font-mono font-bold text-[#111111] mt-2 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-[#808080]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>7 Daily + 4 Weekly</span>
                        </div>
                        <p class="text-xs text-[#808080] mt-1 font-mono">
                            Keeps all archives for 7 days, daily archives for 7 days, weekly for 4 weeks, and monthly for 2 months.
                        </p>
                    </div>

                    <div class="mt-4 pt-3 border-t border-[#111111]/10 flex items-center justify-between text-xs font-mono text-[#808080]">
                        <span>Pruner: <strong class="text-[#111111]">backup:clean</strong></span>
                        <span class="text-emerald-700 font-bold">Daily @ 01:00 UTC</span>
                    </div>
                </div>

                <!-- 3. Scheduled Scope Card -->
                <div class="p-4 bg-white border border-[#111111]/15 flex flex-col justify-between">
                    <div>
                        <span class="text-[10px] font-mono uppercase tracking-widest text-[#808080]">Backup Scope & Engine</span>
                        <div class="text-base font-mono font-bold text-[#111111] mt-2 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-[#808080]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span>Database + Media</span>
                        </div>
                        <p class="text-xs text-[#808080] mt-1 font-mono">
                            Engine: <strong class="text-[#111111]">spatie/laravel-backup</strong> with native PDO fallback. Dumps MySQL and archives <code class="text-[11px] bg-[#F5F1E8] px-1">storage/app/public</code>.
                        </p>
                    </div>

                    <div class="mt-4 pt-3 border-t border-[#111111]/10 flex items-center justify-between text-xs font-mono text-[#808080]">
                        <span>Scheduler: <strong class="text-[#111111]">backup:run</strong></span>
                        <span class="text-emerald-700 font-bold">Daily @ 02:00 UTC</span>
                    </div>
                </div>
            </div>

            <!-- Cloud S3-Compatible Settings Drawer -->
            <div id="cloud-settings-drawer" class="{{ $cloudConfig['enabled'] ? 'hidden' : '' }} bg-white border border-[#111111]/15 transition-all">
                <div class="p-4 border-b border-[#111111]/10 bg-[#F5F1E8]/40 flex items-center justify-between">
                    <div>
                        <h3 class="text-xs font-mono uppercase font-bold text-[#111111]">S3-Compatible Cloud Storage Settings</h3>
                        <p class="text-xs text-[#808080] mt-0.5">Supports Amazon S3, Wasabi Hot Cloud, Backblaze B2, Cloudflare R2, and MinIO.</p>
                    </div>
                    <span class="text-xs font-mono text-[#808080]">PRD-ADDNEW-FEATURE §2.3</span>
                </div>

                <form action="{{ route('admin.security.cloud_settings') }}" method="POST" class="p-6 space-y-4">
                    @csrf
                    
                    <div class="flex items-center gap-3 p-3 bg-[#F5F1E8]/50 border border-[#111111]/10">
                        <input type="checkbox" id="backup_s3_enabled" name="backup_s3_enabled" value="1" {{ $cloudConfig['enabled'] ? 'checked' : '' }} class="w-4 h-4 text-[#111111] focus:ring-0 border-[#111111]/20 rounded-none">
                        <label for="backup_s3_enabled" class="text-xs font-mono uppercase font-bold text-[#111111] cursor-pointer">
                            Enable Automated Cloud Offsite Backup (S3-Compatible)
                        </label>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-mono uppercase text-[#808080] mb-1">Storage Provider</label>
                            <select name="backup_s3_provider" id="backup_s3_provider" class="w-full text-xs font-mono border border-[#111111]/20 p-2 bg-white focus:outline-none focus:border-[#111111]">
                                <option value="aws" {{ $cloudConfig['provider'] === 'aws' ? 'selected' : '' }}>Amazon AWS S3</option>
                                <option value="wasabi" {{ $cloudConfig['provider'] === 'wasabi' ? 'selected' : '' }}>Wasabi Hot Cloud Storage</option>
                                <option value="backblaze" {{ $cloudConfig['provider'] === 'backblaze' ? 'selected' : '' }}>Backblaze B2</option>
                                <option value="cloudflare" {{ $cloudConfig['provider'] === 'cloudflare' ? 'selected' : '' }}>Cloudflare R2</option>
                                <option value="minio" {{ $cloudConfig['provider'] === 'minio' ? 'selected' : '' }}>Self-Hosted MinIO</option>
                                <option value="custom" {{ $cloudConfig['provider'] === 'custom' ? 'selected' : '' }}>Custom S3-Compatible</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-mono uppercase text-[#808080] mb-1">Bucket Name</label>
                            <input type="text" name="backup_s3_bucket" value="{{ $cloudConfig['bucket'] }}" placeholder="e.g. securofi-backups" class="w-full text-xs font-mono border border-[#111111]/20 p-2 bg-white focus:outline-none focus:border-[#111111]">
                        </div>

                        <div>
                            <label class="block text-xs font-mono uppercase text-[#808080] mb-1">Region</label>
                            <input type="text" name="backup_s3_region" value="{{ $cloudConfig['region'] }}" placeholder="e.g. us-east-1 or auto" class="w-full text-xs font-mono border border-[#111111]/20 p-2 bg-white focus:outline-none focus:border-[#111111]">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-mono uppercase text-[#808080] mb-1">Access Key ID</label>
                            <input type="text" name="backup_s3_key" value="{{ $cloudConfig['key'] }}" placeholder="AKIA..." class="w-full text-xs font-mono border border-[#111111]/20 p-2 bg-white focus:outline-none focus:border-[#111111]">
                        </div>

                        <div>
                            <label class="block text-xs font-mono uppercase text-[#808080] mb-1">Secret Access Key</label>
                            <input type="password" name="backup_s3_secret" placeholder="{{ $cloudConfig['secret'] ?: 'Enter Secret Access Key' }}" class="w-full text-xs font-mono border border-[#111111]/20 p-2 bg-white focus:outline-none focus:border-[#111111]">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-mono uppercase text-[#808080] mb-1">Custom Endpoint URL (Optional for Wasabi/R2/MinIO)</label>
                            <input type="url" name="backup_s3_endpoint" value="{{ $cloudConfig['endpoint'] }}" placeholder="e.g. https://s3.wasabisys.com or https://<id>.r2.cloudflarestorage.com" class="w-full text-xs font-mono border border-[#111111]/20 p-2 bg-white focus:outline-none focus:border-[#111111]">
                        </div>

                        <div class="flex items-center pt-6">
                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="backup_s3_use_path_style" value="1" {{ $cloudConfig['use_path_style'] ? 'checked' : '' }} class="w-4 h-4 text-[#111111] focus:ring-0 border-[#111111]/20 rounded-none">
                                <span class="text-xs font-mono uppercase text-[#111111]">Use Path-Style Endpoint (Required for MinIO)</span>
                            </label>
                        </div>
                    </div>

                    <div class="pt-2 flex items-center justify-end gap-3 border-t border-[#111111]/10">
                        <button type="button" onclick="document.getElementById('cloud-settings-drawer').classList.add('hidden')" class="px-3 py-1.5 border border-[#111111]/20 bg-white text-xs font-mono uppercase tracking-wider text-[#808080] hover:text-[#111111]">
                            Close
                        </button>
                        <button type="submit" class="px-4 py-1.5 bg-[#111111] text-white text-xs font-mono uppercase tracking-wider hover:bg-neutral-800 transition-colors">
                            Save Cloud Settings
                        </button>
                    </div>
                </form>
            </div>

            <!-- Backups Archives List Table -->
            <div class="bg-white border border-[#111111]/15">
                <div class="p-4 border-b border-[#111111]/10 bg-[#F5F1E8]/40 flex items-center justify-between">
                    <div>
                        <h3 class="text-xs font-mono uppercase font-bold text-[#111111]">SecuroFi Backup Archive Repository</h3>
                        <p class="text-xs text-[#808080] mt-0.5">All local snapshots and remote cloud archives ready for instant download or restoration.</p>
                    </div>
                    <div class="text-xs font-mono text-[#808080]">
                        Local Store: <code class="bg-white px-1.5 py-0.5 border border-[#111111]/15">storage/app/backups/</code>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-[#111111]/15 bg-[#F5F1E8] font-mono uppercase tracking-wider text-[#808080]">
                                <th class="py-3 px-4">Archive Filename</th>
                                <th class="py-3 px-4">Type & Scope</th>
                                <th class="py-3 px-4">Storage Disk</th>
                                <th class="py-3 px-4">File Size</th>
                                <th class="py-3 px-4">Created At</th>
                                <th class="py-3 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#111111]/10">
                            @forelse ($backups as $backup)
                                <tr class="hover:bg-[#F5F1E8]/30 transition-colors">
                                    <td class="py-3 px-4 font-mono font-bold text-[#111111] flex items-center gap-2">
                                        @if (str_ends_with($backup['filename'], '.zip'))
                                            <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                                        @else
                                            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2 1 3 3 3h10c2 0 3-1 3-3V7c0-2-1-3-3-3H7C5 4 4 5 4 7z"/></svg>
                                        @endif
                                        <span class="truncate max-w-xs md:max-w-md" title="{{ $backup['filename'] }}">{{ $backup['filename'] }}</span>
                                    </td>
                                    <td class="py-3 px-4">
                                        @if ($backup['type'] === 'full_archive')
                                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 bg-purple-50 text-purple-800 border border-purple-200 font-mono text-[10px] uppercase font-bold">
                                                Full (DB + Media)
                                            </span>
                                        @elseif ($backup['type'] === 'database_zip')
                                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 bg-blue-50 text-blue-800 border border-blue-200 font-mono text-[10px] uppercase font-bold">
                                                Database ZIP
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 bg-emerald-50 text-emerald-800 border border-emerald-200 font-mono text-[10px] uppercase font-bold">
                                                SQL Dump
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4">
                                        @if ($backup['disk'] === 's3')
                                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 bg-sky-50 text-sky-800 border border-sky-300 font-mono text-[10px] uppercase font-bold">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 00-9.78 2.096A4.001 4.001 0 003 15z"/></svg>
                                                S3 Cloud
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 bg-stone-100 text-stone-800 border border-stone-300 font-mono text-[10px] uppercase font-bold">
                                                Local Disk
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 font-mono text-[#111111]">
                                        {{ $backup['size'] }}
                                    </td>
                                    <td class="py-3 px-4 font-mono text-[#808080]">
                                        {{ $backup['created_at']->format('M d, Y H:i:s') }}
                                        <div class="text-[10px] text-[#808080]">{{ $backup['created_at']->diffForHumans() }}</div>
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <div class="inline-flex items-center gap-2">
                                            <a href="{{ route('admin.security.download', ['filename' => $backup['filename'], 'disk' => $backup['disk']]) }}" 
                                               class="inline-flex items-center gap-1 px-2 py-1 border border-[#111111] bg-white text-[#111111] font-mono text-[11px] uppercase tracking-wider hover:bg-[#111111] hover:text-white transition-colors" title="Download backup archive">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                                <span>Download</span>
                                            </a>

                                            <form action="{{ route('admin.security.delete_backup', $backup['filename']) }}" method="POST" onsubmit="return confirm('Permanently delete this backup archive?');">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="disk" value="{{ $backup['disk'] }}">
                                                <button type="submit" class="p-1 border border-red-200 text-red-600 hover:bg-red-50 transition-colors" title="Delete Archive">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-[#808080] font-mono text-xs">
                                        No backup archives exist yet. Use "Quick DB Dump" or "Full Backup (DB + Media)" above to generate one.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    @endif

    <!-- TAB 4: SPATIE ACTIVITY LOGS -->
    @if ($activeTab === 'activity')
        <div class="bg-white border border-[#111111]/15">
            <div class="p-4 border-b border-[#111111]/10 bg-[#F5F1E8]/40">
                <h3 class="text-xs font-mono uppercase font-bold text-[#111111]">Platform Audit Trail & Action Logs</h3>
                <p class="text-xs text-[#808080] mt-0.5">Automated logging of content mutations, settings updates, user accounts, and backups.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-[#111111]/15 bg-[#F5F1E8] font-mono uppercase tracking-wider text-[#808080]">
                            <th class="py-3 px-4">Action / Event</th>
                            <th class="py-3 px-4">Initiator (Causer)</th>
                            <th class="py-3 px-4">Target Entity</th>
                            <th class="py-3 px-4 text-right">Timestamp</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#111111]/10">
                        @forelse ($activityLogs as $act)
                            <tr class="hover:bg-[#F5F1E8]/30 transition-colors">
                                <td class="py-3 px-4">
                                    <div class="font-bold text-[#111111]">{{ $act->description }}</div>
                                    @if ($act->event)
                                        <span class="inline-block mt-1 font-mono text-[10px] uppercase px-1.5 py-0.5 bg-[#F5F1E8] border border-[#111111]/15 text-[#111111]">
                                            {{ $act->event }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 font-mono text-[#111111]">
                                    {{ $act->causer->name ?? ($act->causer_id ? 'User #' . $act->causer_id : 'System / Auto') }}
                                </td>
                                <td class="py-3 px-4 font-mono text-[11px] text-[#808080]">
                                    @if ($act->subject_type)
                                        {{ class_basename($act->subject_type) }} #{{ $act->subject_id }}
                                    @else
                                        Platform Core
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right font-mono text-[11px] text-[#808080]">
                                    {{ $act->created_at->format('M d, Y H:i:s') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-8 text-center text-[#808080] font-mono text-xs">
                                    No activity logs recorded yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($activityLogs->hasPages())
                <div class="p-4 border-t border-[#111111]/10">
                    {{ $activityLogs->appends(['tab' => 'activity'])->links() }}
                </div>
            @endif
        </div>
    @endif

</div>
@endsection
