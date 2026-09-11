@extends('layouts.admin')
@section('title', 'Email Subscribers')
@section('header_title', 'Newsletter Subscribers')

@section('content')
<div class="space-y-6">

    {{-- Stats Row --}}
    <div class="grid grid-cols-3 gap-4">
        <div class="bg-white border border-[#111111]/15 p-5">
            <div class="text-[10px] font-mono uppercase tracking-widest text-[#808080]">Total Subscribers</div>
            <div class="text-3xl font-bold text-[#111111] mt-1">{{ number_format($total) }}</div>
        </div>
        <div class="bg-white border border-[#111111]/15 p-5">
            <div class="text-[10px] font-mono uppercase tracking-widest text-[#808080]">Active</div>
            <div class="text-3xl font-bold text-emerald-600 mt-1">{{ number_format($active) }}</div>
        </div>
        <div class="bg-white border border-[#111111]/15 p-5">
            <div class="text-[10px] font-mono uppercase tracking-widest text-[#808080]">Unsubscribed</div>
            <div class="text-3xl font-bold text-[#808080] mt-1">{{ number_format($unsubscribed) }}</div>
        </div>
    </div>

    {{-- Actions Bar --}}
    <div class="bg-white border border-[#111111]/15 p-4 flex flex-col sm:flex-row gap-3 items-start sm:items-center justify-between">
        <form method="GET" class="flex gap-2 flex-wrap">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search email or name..."
                class="px-3 py-1.5 text-xs font-mono border border-[#111111]/20 bg-[#F8F8F6] focus:outline-none focus:border-[#111111] w-56">
            <select name="status" class="px-3 py-1.5 text-xs font-mono border border-[#111111]/20 bg-[#F8F8F6] focus:outline-none">
                <option value="">All Status</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="unsubscribed" {{ request('status') === 'unsubscribed' ? 'selected' : '' }}>Unsubscribed</option>
            </select>
            <button type="submit" class="btn-primary text-xs px-4 py-1.5">Filter</button>
            @if(request()->hasAny(['search','status']))
                <a href="{{ route('admin.email.subscribers.index') }}" class="text-xs font-mono text-[#808080] hover:text-[#111111] px-2 py-1.5">Clear</a>
            @endif
        </form>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.email.compose.index') }}" class="btn-primary text-xs px-3.5 py-1.5 font-mono">
                + Compose Email
            </a>
            <a href="{{ route('admin.email.subscribers.export') }}" class="text-xs font-mono border border-[#111111]/20 px-3.5 py-1.5 hover:bg-[#F5F1E8] transition-colors">
                ↓ Export CSV
            </a>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white border border-[#111111]/15">
        <form id="bulk-form" action="{{ route('admin.email.subscribers.bulk-destroy') }}" method="POST">
            @csrf
            <div class="flex items-center justify-between px-5 py-3 border-b border-[#111111]/10">
                <h3 class="text-sm font-bold text-[#111111]">Subscribers</h3>
                <div class="flex items-center gap-3">
                    <button type="button" onclick="composeToSelected()"
                        class="text-xs font-mono text-blue-600 hover:underline hidden" id="bulk-mail-btn">
                        ✉️ Send Email to Selected
                    </button>
                    <button type="submit" onclick="return confirm('Delete selected subscribers?')"
                        class="text-xs font-mono text-rose-600 hover:underline hidden" id="bulk-delete-btn">
                        Delete Selected
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs font-mono">
                    <thead class="border-b border-[#111111]/10">
                        <tr class="text-[10px] uppercase tracking-wider text-[#808080]">
                            <th class="py-3 px-5 w-8">
                                <input type="checkbox" id="select-all" class="rounded-none">
                            </th>
                            <th class="py-3 px-4 text-left">Email</th>
                            <th class="py-3 px-4 text-left">Name</th>
                            <th class="py-3 px-4 text-left">Status</th>
                            <th class="py-3 px-4 text-left">Source</th>
                            <th class="py-3 px-4 text-left">Subscribed</th>
                            <th class="py-3 px-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#111111]/8">
                        @forelse ($subscribers as $sub)
                            <tr class="hover:bg-[#F8F8F6] transition-colors">
                                <td class="py-3 px-5">
                                    <input type="checkbox" name="ids[]" value="{{ $sub->id }}" class="row-check rounded-none">
                                </td>
                                <td class="py-3 px-4 font-medium text-[#111111]">{{ $sub->email }}</td>
                                <td class="py-3 px-4 text-[#808080]">{{ $sub->name ?? '—' }}</td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 text-[10px] uppercase font-bold
                                        {{ $sub->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-neutral-100 text-[#808080]' }}">
                                        {{ $sub->status }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-[#808080]">{{ $sub->source ?? 'web' }}</td>
                                <td class="py-3 px-4 text-[#808080]">{{ $sub->subscribed_at?->format('M d, Y') ?? '—' }}</td>
                                <td class="py-3 px-4 text-right space-x-2 whitespace-nowrap">
                                    <a href="{{ route('admin.email.compose.index', ['to' => $sub->email, 'name' => $sub->name]) }}"
                                        class="text-[11px] font-mono border border-[#111111]/20 px-2 py-0.5 hover:bg-[#F5F1E8] transition-colors inline-block" title="Send direct email to {{ $sub->email }}">
                                        ✉️ Mail
                                    </a>
                                    <form action="{{ route('admin.email.subscribers.destroy', $sub->id) }}" method="POST" class="inline"
                                          onsubmit="return confirm('Delete {{ $sub->email }}?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-rose-500 hover:underline">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-10 text-center text-[#808080]">No subscribers found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </form>

        {{-- Pagination --}}
        @if ($subscribers->hasPages())
            <div class="px-5 py-3 border-t border-[#111111]/10">
                {{ $subscribers->links() }}
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    const selectAll = document.getElementById('select-all');
    const checkboxes = document.querySelectorAll('.row-check');
    const bulkBtn = document.getElementById('bulk-delete-btn');
    const bulkMailBtn = document.getElementById('bulk-mail-btn');

    function updateBulkBtn() {
        const anyChecked = [...checkboxes].some(c => c.checked);
        bulkBtn.classList.toggle('hidden', !anyChecked);
        if (bulkMailBtn) bulkMailBtn.classList.toggle('hidden', !anyChecked);
    }

    function composeToSelected() {
        const checkedIds = [...checkboxes].filter(c => c.checked).map(c => c.value);
        if (checkedIds.length === 0) return;
        window.location.href = '{{ route("admin.email.compose.index") }}?subscriber_ids=' + checkedIds.join(',');
    }

    selectAll.addEventListener('change', () => {
        checkboxes.forEach(c => c.checked = selectAll.checked);
        updateBulkBtn();
    });
    checkboxes.forEach(c => c.addEventListener('change', updateBulkBtn));
</script>
@endpush
@endsection
