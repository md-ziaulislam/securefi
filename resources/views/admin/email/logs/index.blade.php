@extends('layouts.admin')
@section('title', 'Email Delivery Logs')
@section('header_title', 'Email Audit & Delivery Logs')

@section('content')
<div class="space-y-6">

    {{-- Stats Row --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white border border-[#111111]/15 p-5">
            <div class="text-[10px] font-mono uppercase tracking-widest text-[#808080]">Total Transmissions</div>
            <div class="text-3xl font-bold text-[#111111] mt-1">{{ number_format($totalLogs) }}</div>
        </div>
        <div class="bg-white border border-[#111111]/15 p-5">
            <div class="text-[10px] font-mono uppercase tracking-widest text-[#808080]">Delivered (Sent)</div>
            <div class="text-3xl font-bold text-emerald-600 mt-1">{{ number_format($sentCount) }}</div>
        </div>
        <div class="bg-white border border-[#111111]/15 p-5">
            <div class="text-[10px] font-mono uppercase tracking-widest text-[#808080]">Failed Delivery</div>
            <div class="text-3xl font-bold text-rose-600 mt-1">{{ number_format($failedCount) }}</div>
        </div>
    </div>

    {{-- Filter & Actions Bar --}}
    <div class="bg-white border border-[#111111]/15 p-4 flex flex-col lg:flex-row gap-3 items-start lg:items-center justify-between">
        <form method="GET" class="flex gap-2 flex-wrap items-center">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search recipient or subject..."
                class="px-3 py-1.5 text-xs font-mono border border-[#111111]/20 bg-[#F8F8F6] focus:outline-none focus:border-[#111111] w-64">

            <select name="status" class="px-3 py-1.5 text-xs font-mono border border-[#111111]/20 bg-[#F8F8F6] focus:outline-none">
                <option value="">All Statuses</option>
                <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Sent Only</option>
                <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed Only</option>
            </select>

            <select name="type" class="px-3 py-1.5 text-xs font-mono border border-[#111111]/20 bg-[#F8F8F6] focus:outline-none">
                <option value="">All Types</option>
                <option value="direct" {{ request('type') === 'direct' ? 'selected' : '' }}>Direct Mail</option>
                <option value="campaign" {{ request('type') === 'campaign' ? 'selected' : '' }}>Campaign</option>
                <option value="test" {{ request('type') === 'test' ? 'selected' : '' }}>Test Email</option>
            </select>

            <button type="submit" class="btn-primary text-xs px-4 py-1.5 font-mono">Filter</button>

            @if(request()->hasAny(['search', 'status', 'type']))
                <a href="{{ route('admin.email.logs.index') }}" class="text-xs font-mono text-[#808080] hover:text-[#111111] px-2 py-1.5">Reset</a>
            @endif
        </form>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.email.compose.index') }}" class="btn-primary text-xs px-3.5 py-1.5 font-mono">
                + Compose Mail
            </a>

            @if($totalLogs > 0)
            <form action="{{ route('admin.email.logs.clear') }}" method="POST" class="inline"
                onsubmit="return confirm('Clear email delivery logs?')">
                @csrf
                <button type="submit" name="clear_type" value="all" class="text-xs font-mono border border-[#111111]/20 px-3 py-1.5 hover:bg-[#F5F1E8] text-[#808080] hover:text-[#111111] transition-colors">
                    Purge All Logs
                </button>
            </form>
            @endif
        </div>
    </div>

    {{-- Logs Table --}}
    <div class="bg-white border border-[#111111]/15">
        @if($logs->isEmpty())
            <div class="p-12 text-center space-y-3">
                <div class="text-[#808080] font-mono text-sm">No email transmission logs recorded yet.</div>
                <a href="{{ route('admin.email.compose.index') }}" class="btn-primary text-xs px-4 py-2 font-mono inline-block">
                    Send Your First Email
                </a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-xs font-mono">
                    <thead class="border-b border-[#111111]/10 bg-[#F8F8F6]">
                        <tr class="text-[10px] uppercase tracking-wider text-[#808080]">
                            <th class="py-3 px-4 text-left">Recipient</th>
                            <th class="py-3 px-4 text-left">Subject & Preview</th>
                            <th class="py-3 px-4 text-left">Type</th>
                            <th class="py-3 px-4 text-left">Status</th>
                            <th class="py-3 px-4 text-left">Sender</th>
                            <th class="py-3 px-4 text-left">Timestamp</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#111111]/10">
                        @foreach($logs as $log)
                            <tr class="hover:bg-[#F8F8F6] transition-colors">
                                <td class="py-3 px-4">
                                    <div class="font-bold text-[#111111]">{{ $log->recipient_email }}</div>
                                    @if($log->recipient_name)
                                        <div class="text-[10px] text-[#808080]">{{ $log->recipient_name }}</div>
                                    @endif
                                </td>
                                <td class="py-3 px-4 max-w-xs">
                                    <div class="font-bold text-[#111111] font-sans truncate">{{ $log->subject }}</div>
                                    @if($log->body_preview)
                                        <div class="text-[11px] text-[#808080] font-sans truncate">{{ $log->body_preview }}</div>
                                    @endif
                                    @if($log->error_message)
                                        <div class="text-[10px] text-rose-600 font-mono mt-0.5 bg-rose-50 p-1 border border-rose-200 truncate" title="{{ $log->error_message }}">
                                            Error: {{ $log->error_message }}
                                        </div>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    <span class="inline-block px-2 py-0.5 text-[10px] font-bold uppercase
                                        {{ $log->type === 'campaign' ? 'bg-purple-50 text-purple-700 border border-purple-200' :
                                           ($log->type === 'direct' ? 'bg-blue-50 text-blue-700 border border-blue-200' :
                                           'bg-gray-100 text-gray-700 border border-gray-200') }}">
                                        {{ $log->type }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    @if($log->status === 'sent')
                                        <span class="inline-flex items-center px-2 py-0.5 text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            ✓ Sent
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200" title="{{ $log->error_message }}">
                                            ✕ Failed
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-[#808080]">
                                    {{ $log->sender ? $log->sender->name : 'System' }}
                                </td>
                                <td class="py-3 px-4 text-[#808080]">
                                    {{ $log->created_at->format('M d, Y H:i:s') }}
                                </td>
                                <td class="py-3 px-4 text-right space-x-2">
                                    <a href="{{ route('admin.email.compose.index', ['to' => $log->recipient_email, 'name' => $log->recipient_name]) }}"
                                        class="px-2 py-1 border border-[#111111]/20 hover:bg-[#F5F1E8] text-[11px] transition-colors" title="Send new email to recipient">
                                        Reply
                                    </a>
                                    <form action="{{ route('admin.email.logs.destroy', $log) }}" method="POST" class="inline"
                                        onsubmit="return confirm('Delete this log entry?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-rose-600 hover:text-rose-800 text-[11px] px-1">
                                            &times;
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($logs->hasPages())
                <div class="p-4 border-t border-[#111111]/10">
                    {{ $logs->links() }}
                </div>
            @endif
        @endif
    </div>

</div>
@endsection
