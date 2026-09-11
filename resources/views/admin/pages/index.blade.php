@extends('layouts.admin')

@section('title', 'Dynamic Pages & Inquiries')
@section('header_title', 'Dynamic Content Pages & Inquiries')

@section('content')
<div class="space-y-6">

    {{-- Tabs Header & Action --}}
    <div class="bg-white border border-[#111111]/15 p-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex items-center gap-2 text-xs font-mono">
            <a href="{{ route('admin.pages.index', ['tab' => 'pages']) }}"
                class="px-4 py-2 border {{ $tab === 'pages' ? 'bg-[#111111] text-white border-[#111111] font-bold' : 'border-[#111111]/20 text-[#111111] hover:bg-[#F5F1E8]' }}">
                Pages ({{ $pages->count() }})
            </a>
            <a href="{{ route('admin.pages.index', ['tab' => 'contact']) }}"
                class="px-4 py-2 border flex items-center gap-2 {{ $tab === 'contact' ? 'bg-[#111111] text-white border-[#111111] font-bold' : 'border-[#111111]/20 text-[#111111] hover:bg-[#F5F1E8]' }}">
                <span>Contact Inquiries</span>
                @php
                    $unreadCount = \App\Models\ContactSubmission::where('is_read', false)->count();
                @endphp
                @if ($unreadCount > 0)
                    <span class="px-1.5 py-0.2 text-[9px] bg-rose-500 text-white rounded-full font-bold">{{ $unreadCount }}</span>
                @endif
            </a>
        </div>

        @if ($tab === 'pages')
            <a href="{{ route('admin.pages.create') }}" class="btn-primary text-xs font-mono py-2 px-4">
                + Create New Page
            </a>
        @endif
    </div>

    @if ($tab === 'pages')
        {{-- Pages Table --}}
        <div class="bg-white border border-[#111111]/15 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs font-mono">
                    <thead class="bg-[#F8F8F6] border-b border-[#111111]/15 text-[#808080] uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="py-3 px-4">Order</th>
                            <th class="py-3 px-4">Title & URL Slug</th>
                            <th class="py-3 px-4">Template</th>
                            <th class="py-3 px-4">Placement</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4">Last Updated</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#111111]/10">
                        @forelse ($pages as $p)
                            <tr class="hover:bg-[#F5F1E8]/40 transition-colors">
                                <td class="py-3 px-4 text-[#808080]">{{ $p->menu_order }}</td>
                                <td class="py-3 px-4 font-medium text-[#111111]">
                                    <div class="font-bold">{{ $p->title }}</div>
                                    <div class="text-[10px] text-[#808080]">/page/{{ $p->slug }}</div>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-1.5 py-0.5 text-[9px] uppercase font-bold bg-[#F8F8F6] border border-[#111111]/20">
                                        {{ $p->template }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-[10px] text-[#808080] space-x-1">
                                    @if ($p->show_in_menu)
                                        <span class="px-1 py-0.2 bg-blue-50 text-blue-800 border border-blue-200">Header</span>
                                    @endif
                                    @if ($p->show_in_footer)
                                        <span class="px-1 py-0.2 bg-slate-50 text-slate-800 border border-slate-200">Footer</span>
                                    @endif
                                    @if (!$p->show_in_menu && !$p->show_in_footer)
                                        <span class="text-neutral">Standalone</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 text-[10px] uppercase font-bold {{ $p->status === 'published' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                        {{ $p->status }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-[#808080]">{{ $p->updated_at->format('M d, Y') }}</td>
                                <td class="py-3 px-4 text-right space-x-2">
                                    <a href="{{ route('page.show', $p->slug) }}" target="_blank" class="text-[#808080] hover:text-[#111111]">View</a>
                                    <span>•</span>
                                    <a href="{{ route('admin.pages.edit', $p->id) }}" class="text-[#111111] font-bold hover:underline">Edit</a>
                                    <span>•</span>
                                    <form action="{{ route('admin.pages.destroy', $p->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete page {{ $p->title }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-rose-600 hover:underline">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-10 text-center text-[#808080]">No pages created yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @else
        {{-- Contact Submissions Table --}}
        <div class="bg-white border border-[#111111]/15 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs font-mono">
                    <thead class="bg-[#F8F8F6] border-b border-[#111111]/15 text-[#808080] uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="py-3 px-4">Sender</th>
                            <th class="py-3 px-4">Subject</th>
                            <th class="py-3 px-4">Message Preview</th>
                            <th class="py-3 px-4">Date & IP</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#111111]/10">
                        @forelse ($submissions as $sub)
                            <tr class="hover:bg-[#F5F1E8]/40 {{ !$sub->is_read ? 'bg-amber-50/40' : '' }}">
                                <td class="py-3 px-4 font-medium text-[#111111]">
                                    <div class="font-bold">{{ $sub->name }}</div>
                                    <div class="text-[10px] text-[#808080]">{{ $sub->email }}</div>
                                </td>
                                <td class="py-3 px-4 text-[#111111] font-semibold">{{ $sub->subject ?? 'General Inquiry' }}</td>
                                <td class="py-3 px-4 text-[#808080] max-w-xs">
                                    <p class="truncate" title="{{ $sub->message }}">{{ $sub->message }}</p>
                                </td>
                                <td class="py-3 px-4 text-[#808080] text-[10px]">
                                    <div>{{ $sub->submitted_at ? $sub->submitted_at->format('M d, Y H:i') : $sub->created_at->format('M d, Y') }}</div>
                                    <div class="text-neutral">{{ $sub->ip_address }}</div>
                                </td>
                                <td class="py-3 px-4">
                                    <form action="{{ route('admin.pages.contact.toggle', $sub->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-2 py-0.5 text-[9px] font-bold uppercase {{ $sub->is_read ? 'bg-neutral/20 text-[#808080]' : 'bg-emerald-100 text-emerald-800' }}">
                                            {{ $sub->is_read ? 'Read' : '● New' }}
                                        </button>
                                    </form>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <form action="{{ route('admin.pages.contact.destroy', $sub->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this inquiry from {{ $sub->name }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-rose-600 hover:underline">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-10 text-center text-[#808080]">No contact inquiries received yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($submissions->hasPages())
                <div class="p-4 border-t border-[#111111]/10">
                    {{ $submissions->links() }}
                </div>
            @endif
        </div>
    @endif

</div>
@endsection
