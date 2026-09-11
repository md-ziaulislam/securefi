@extends('layouts.admin')

@section('title', 'Dashboard Overview')
@section('header_title', 'System Telemetry & Content Overview')

@section('content')
<div class="space-y-8">

    {{-- Stats Cards Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white border border-[#111111]/15 p-6 flex flex-col justify-between">
            <div class="text-[11px] font-mono text-[#808080] uppercase tracking-wider">Total Articles</div>
            <div class="text-3xl font-bold font-mono text-[#111111] my-2">{{ number_format($stats['total_articles']) }}</div>
            <div class="text-xs font-mono text-emerald-600">{{ $stats['published_articles'] }} Published</div>
        </div>

        <div class="bg-white border border-[#111111]/15 p-6 flex flex-col justify-between">
            <div class="text-[11px] font-mono text-[#808080] uppercase tracking-wider">Total Readership</div>
            <div class="text-3xl font-bold font-mono text-[#111111] my-2">{{ number_format($stats['total_views']) }}</div>
            <div class="text-xs font-mono text-[#808080]">Direct Article Views</div>
        </div>

        <div class="bg-white border border-[#111111]/15 p-6 flex flex-col justify-between">
            <div class="text-[11px] font-mono text-[#808080] uppercase tracking-wider">Active Live Visitors</div>
            <div class="text-3xl font-bold font-mono text-emerald-600 my-2 flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-emerald-500 animate-ping"></span>
                <span>{{ number_format($stats['live_visitors']) }}</span>
            </div>
            <div class="text-xs font-mono text-[#808080]">Real-time Last 5 Mins</div>
        </div>

        <div class="bg-white border border-[#111111]/15 p-6 flex flex-col justify-between">
            <div class="text-[11px] font-mono text-[#808080] uppercase tracking-wider">Subscribers</div>
            <div class="text-3xl font-bold font-mono text-[#111111] my-2">{{ number_format($stats['total_subscribers']) }}</div>
            <div class="text-xs font-mono text-[#808080]">{{ $stats['unread_contacts'] }} Pending Inquiries</div>
        </div>
    </div>

    {{-- Uptime Monitoring Telemetry Strip (PRD-ADDNEW 2.2) --}}
    @if(!empty($uptimeSummary))
        <div class="bg-white border border-[#111111]/15 px-6 py-3.5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <span class="w-2.5 h-2.5 rounded-full {{ $uptimeSummary['is_up'] ? 'bg-emerald-500 animate-pulse' : 'bg-red-500' }}"></span>
                <span class="text-xs font-mono font-bold text-[#111111] uppercase tracking-wider">System Availability:</span>
                <span class="text-xs font-mono {{ $uptimeSummary['is_up'] ? 'text-emerald-700' : 'text-red-700' }} font-bold">
                    {{ $uptimeSummary['status_label'] }}
                </span>
            </div>
            <div class="flex items-center gap-6 text-xs font-mono text-[#808080]">
                <span>24h: <strong class="text-[#111111]">{{ $uptimeSummary['ratio_24h'] }}%</strong></span>
                <span>30d: <strong class="text-[#111111]">{{ $uptimeSummary['ratio_30d'] }}%</strong></span>
                @if($uptimeSummary['avg_response_time'] > 0)
                    <span>Latency: <strong class="text-[#111111]">{{ $uptimeSummary['avg_response_time'] }}ms</strong></span>
                @endif
                <a href="{{ route('admin.uptime.index') }}" class="text-[#111111] hover:underline font-bold text-[11px] uppercase tracking-wider">
                    Full Telemetry →
                </a>
            </div>
        </div>
    @endif

    {{-- Content Split: Recent Articles & Activity Audit Log --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        {{-- Recent Articles --}}
        <div class="lg:col-span-8 bg-white border border-[#111111]/15 p-6">
            <div class="flex items-center justify-between border-b border-[#111111]/10 pb-4 mb-4">
                <div>
                    <h3 class="font-bold text-sm text-[#111111]">Recent Articles</h3>
                    <p class="text-xs font-mono text-[#808080]">Latest editorial entries.</p>
                </div>
                <a href="{{ route('admin.articles.create') }}" class="btn-primary text-xs font-mono py-1.5 px-3">
                    + New Article
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs font-mono">
                    <thead>
                        <tr class="border-b border-[#111111]/10 text-[#808080] uppercase tracking-wider text-[10px]">
                            <th class="py-2.5">Title</th>
                            <th class="py-2.5">Category</th>
                            <th class="py-2.5">Status</th>
                            <th class="py-2.5">Views</th>
                            <th class="py-2.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#111111]/5">
                        @forelse ($recentArticles as $art)
                            <tr>
                                <td class="py-3 font-medium text-[#111111]">
                                    <a href="{{ route('admin.articles.edit', $art->id) }}" class="hover:underline">
                                        {{ Str::limit($art->title, 45) }}
                                    </a>
                                </td>
                                <td class="py-3 text-[#808080]">{{ $art->category->name }}</td>
                                <td class="py-3">
                                    <span class="px-2 py-0.5 text-[10px] uppercase font-bold {{ $art->status === 'published' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                        {{ $art->status }}
                                    </span>
                                </td>
                                <td class="py-3 text-[#808080]">{{ number_format($art->view_count) }}</td>
                                <td class="py-3 text-right">
                                    <a href="{{ route('admin.articles.edit', $art->id) }}" class="text-[#111111] hover:underline font-bold">Edit</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-6 text-center text-[#808080]">No articles created yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Activity Audit Trail --}}
        <div class="lg:col-span-4 bg-white border border-[#111111]/15 p-6">
            <div class="border-b border-[#111111]/10 pb-4 mb-4">
                <h3 class="font-bold text-sm text-[#111111]">Activity Audit Trail</h3>
                <p class="text-xs font-mono text-[#808080]">System modification events.</p>
            </div>

            <div class="space-y-4">
                @forelse ($recentActivities as $act)
                    <div class="border-l-2 border-[#111111] pl-3 text-xs font-mono space-y-0.5">
                        <div class="font-semibold text-[#111111]">
                            {{ $act->causer->name ?? 'System' }} • {{ ucfirst($act->description) }}
                        </div>
                        <div class="text-[10px] text-[#808080]">
                            {{ class_basename($act->subject_type ?? 'Entity') }} #{{ $act->subject_id }}
                        </div>
                        <div class="text-[10px] text-[#808080]">
                            {{ $act->created_at->diffForHumans() }}
                        </div>
                    </div>
                @empty
                    <p class="text-xs font-mono text-[#808080]">No recorded actions yet.</p>
                @endforelse
            </div>
        </div>
    </div>

</div>
@endsection
