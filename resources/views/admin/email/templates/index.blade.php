@extends('layouts.admin')
@section('title', 'Email Templates')
@section('header_title', 'Email Templates Library')

@section('content')
<div class="space-y-6">

    {{-- Top Bar with Add Button --}}
    <div class="bg-white border border-[#111111]/15 p-4 flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
        <div>
            <h3 class="text-sm font-bold text-[#111111]">Reusable Email Templates</h3>
            <p class="text-xs font-mono text-[#808080]">Create and customize responsive HTML templates for newsletters, product announcements, and alerts.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.email.templates.create') }}" class="btn-primary text-xs px-4 py-2 font-mono flex items-center gap-1.5">
                <span>+ Create Template</span>
            </a>
        </div>
    </div>

    {{-- Merge Tags Guide --}}
    <div class="bg-[#F8F8F6] border border-[#111111]/15 p-4 text-xs font-mono">
        <div class="font-bold text-[#111111] mb-2 uppercase tracking-wider text-[11px]">Supported Dynamic Merge Tags</div>
        <div class="flex flex-wrap gap-2">
            <span class="px-2 py-1 bg-white border border-[#111111]/15"><code class="text-rose-600 font-bold">&#123;&#123;name&#125;&#125;</code> — Subscriber Name</span>
            <span class="px-2 py-1 bg-white border border-[#111111]/15"><code class="text-rose-600 font-bold">&#123;&#123;email&#125;&#125;</code> — Subscriber Email</span>
            <span class="px-2 py-1 bg-white border border-[#111111]/15"><code class="text-rose-600 font-bold">&#123;&#123;site_name&#125;&#125;</code> — SecuroFi.Tech</span>
            <span class="px-2 py-1 bg-white border border-[#111111]/15"><code class="text-rose-600 font-bold">&#123;&#123;site_url&#125;&#125;</code> — Website URL</span>
            <span class="px-2 py-1 bg-white border border-[#111111]/15"><code class="text-rose-600 font-bold">&#123;&#123;unsubscribe_url&#125;&#125;</code> — 1-Click Unsubscribe Link</span>
        </div>
    </div>

    {{-- Templates Table / Grid --}}
    <div class="bg-white border border-[#111111]/15">
        @if($templates->isEmpty())
            <div class="p-12 text-center space-y-3">
                <div class="text-[#808080] font-mono text-sm">No email templates created yet.</div>
                <a href="{{ route('admin.email.templates.create') }}" class="btn-primary text-xs px-4 py-2 font-mono inline-block">
                    Create Your First Template
                </a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-xs font-mono">
                    <thead class="border-b border-[#111111]/10 bg-[#F8F8F6]">
                        <tr class="text-[10px] uppercase tracking-wider text-[#808080]">
                            <th class="py-3 px-5 text-left">Template Name</th>
                            <th class="py-3 px-5 text-left">Subject Line</th>
                            <th class="py-3 px-5 text-left">Type</th>
                            <th class="py-3 px-5 text-left">Last Updated</th>
                            <th class="py-3 px-5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#111111]/10">
                        @foreach($templates as $template)
                            <tr class="hover:bg-[#F8F8F6] transition-colors">
                                <td class="py-3.5 px-5">
                                    <div class="font-bold text-[#111111] text-xs font-sans">{{ $template->name }}</div>
                                    @if($template->preview_text)
                                        <div class="text-[11px] text-[#808080] truncate max-w-xs">{{ $template->preview_text }}</div>
                                    @endif
                                </td>
                                <td class="py-3.5 px-5 text-[#333333] font-sans">
                                    {{ $template->subject }}
                                </td>
                                <td class="py-3.5 px-5">
                                    <span class="inline-block px-2 py-0.5 text-[10px] uppercase font-mono font-bold
                                        {{ $template->type === 'newsletter' ? 'bg-blue-50 text-blue-700 border border-blue-200' :
                                           ($template->type === 'transactional' ? 'bg-amber-50 text-amber-700 border border-amber-200' :
                                           'bg-purple-50 text-purple-700 border border-purple-200') }}">
                                        {{ $template->type }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-5 text-[#808080]">
                                    {{ $template->updated_at->format('M d, Y H:i') }}
                                </td>
                                <td class="py-3.5 px-5 text-right space-x-2">
                                    <a href="{{ route('admin.email.templates.preview', $template) }}" target="_blank"
                                        class="px-2.5 py-1 border border-[#111111]/20 hover:bg-[#F5F1E8] transition-colors text-[11px]">
                                        Preview ↗
                                    </a>
                                    <a href="{{ route('admin.email.templates.edit', $template) }}"
                                        class="px-2.5 py-1 bg-[#111111] text-white hover:bg-[#222222] transition-colors text-[11px]">
                                        Edit
                                    </a>
                                    <form action="{{ route('admin.email.templates.destroy', $template) }}" method="POST" class="inline"
                                        onsubmit="return confirm('Are you sure you want to delete template &quot;{{ $template->name }}&quot;?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-2 py-1 text-rose-600 hover:text-rose-800 hover:bg-rose-50 transition-colors text-[11px]">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
