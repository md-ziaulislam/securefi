@extends('layouts.admin')

@section('title', 'Article Inventory')
@section('header_title', 'Article Management')

@section('content')
<div class="space-y-6">

    {{-- Filter and Action Bar --}}
    <div class="bg-white border border-[#111111]/15 p-4 flex flex-col md:flex-row items-center justify-between gap-4">
        <form action="{{ route('admin.articles.index') }}" method="GET" class="flex flex-wrap items-center gap-3 w-full md:w-auto">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Filter articles..."
                class="px-3 py-1.5 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111] w-48">

            <select name="category_id" class="px-3 py-1.5 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                <option value="">All Categories</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->displayName }}</option>
                @endforeach
            </select>

            <select name="status" class="px-3 py-1.5 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                <option value="">All Statuses</option>
                <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
            </select>

            <button type="submit" class="btn-secondary text-xs font-mono py-1.5 px-3">Filter</button>
            @if (request()->hasAny(['search', 'category_id', 'status']))
                <a href="{{ route('admin.articles.index') }}" class="text-xs font-mono text-[#808080] hover:text-[#111111]">Clear</a>
            @endif
        </form>

        <a href="{{ route('admin.articles.create') }}" class="btn-primary text-xs font-mono py-2 px-4 shrink-0 w-full md:w-auto text-center">
            + New Article
        </a>
    </div>

    {{-- Bulk Action & Table Form --}}
    <form action="{{ route('admin.articles.bulk') }}" method="POST" id="bulk-action-form" onsubmit="return confirmBulkAction()">
        @csrf

        {{-- Bulk Action Bar --}}
        <div class="mb-3 flex items-center gap-3">
            <span class="text-xs font-mono text-[#808080]">Bulk Actions:</span>
            <select name="action" id="bulk-action-select" class="px-2.5 py-1 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                <option value="">Select Action...</option>
                @can('publish articles')
                    <option value="publish">Publish Selected</option>
                @endcan
                <option value="draft">Move to Draft</option>
                <option value="delete">Delete Selected</option>
            </select>
            <button type="submit" class="btn-secondary text-xs font-mono py-1 px-3">
                Apply Action
            </button>
            <span id="selected-count" class="text-[11px] font-mono text-[#808080]">0 selected</span>
        </div>

        {{-- Articles Table --}}
        <div class="bg-white border border-[#111111]/15 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs font-mono">
                    <thead class="bg-[#F8F8F6] border-b border-[#111111]/15 text-[#808080] uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="py-3 px-3 w-10 text-center">
                                <input type="checkbox" id="select-all" class="rounded-none border-[#111111] text-[#111111]">
                            </th>
                            <th class="py-3 px-4">Title & Slug</th>
                            <th class="py-3 px-4">Category</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4">Read Time</th>
                            <th class="py-3 px-4">Views</th>
                            <th class="py-3 px-4">Score</th>
                            <th class="py-3 px-4">Published</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#111111]/10">
                        @forelse ($articles as $art)
                            <tr class="hover:bg-[#F5F1E8]/40 transition-colors">
                                <td class="py-3.5 px-3 text-center">
                                    <input type="checkbox" name="selected_ids[]" value="{{ $art->id }}" class="article-checkbox rounded-none border-[#111111] text-[#111111]">
                                </td>
                                <td class="py-3.5 px-4 font-medium text-[#111111]">
                                    <div class="font-bold">{{ $art->title }}</div>
                                    <div class="text-[10px] text-[#808080]">/article/{{ $art->slug }}</div>
                                </td>
                                <td class="py-3.5 px-4 text-[#808080]">{{ $art->category->name }}</td>
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-1">
                                        <span class="px-2 py-0.5 text-[10px] uppercase font-bold {{ $art->status === 'published' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                            {{ $art->status }}
                                        </span>
                                        @if ($art->is_featured)
                                            <span class="px-1.5 py-0.5 text-[9px] uppercase bg-purple-100 text-purple-800 font-bold">Featured</span>
                                        @endif
                                    </div>
                                    @if ($art->country_rule !== 'all' || $art->master_article_id || $art->variant_country)
                                        <div class="mt-1 flex items-center gap-1">
                                            <span class="inline-flex items-center gap-0.5 px-1 py-0.2 text-[9px] bg-amber-50 text-amber-900 border border-amber-200" title="{{ $art->countryRuleLabel() }}">
                                                <span>🌍</span> {{ $art->variant_country ? 'Edition [' . $art->variant_country . ']' : $art->countryRuleLabel() }}
                                            </span>
                                        </div>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-[#808080]">{{ $art->reading_time }} min</td>
                                <td class="py-3.5 px-4 text-[#808080]">{{ number_format($art->view_count) }}</td>
                                <td class="py-3.5 px-4">
                                    @php $score = $art->performance_score; @endphp
                                    <span class="inline-flex items-center gap-1 font-mono text-[11px] font-bold {{ $score >= 70 ? 'text-emerald-700' : ($score >= 40 ? 'text-amber-700' : 'text-neutral-500') }}" title="{{ $art->performance_label }}">
                                        <span>{{ $score }}/100</span>
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-[#808080]">
                                    {{ $art->published_at ? $art->published_at->format('M d, Y') : '—' }}
                                </td>
                                <td class="py-3.5 px-4 text-right space-x-2">
                                    <a href="{{ route('article.show', $art->slug) }}" target="_blank" class="text-[#808080] hover:text-[#111111]">View</a>
                                    @if(auth()->user()->can('publish articles') || $art->author_id === auth()->id() || auth()->user()->hasRole(['Super Admin', 'super-admin', 'Admin', 'Editor']))
                                        <span>•</span>
                                        <a href="{{ route('admin.articles.edit', $art->id) }}" class="text-[#111111] font-bold hover:underline">Edit</a>
                                        <span>•</span>
                                        <button type="button" onclick="deleteSingle({{ $art->id }})" class="text-rose-600 hover:underline">Delete</button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-10 text-center text-[#808080]">No articles found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($articles->hasPages())
                <div class="p-4 border-t border-[#111111]/10">
                    {{ $articles->links() }}
                </div>
            @endif
        </div>
    </form>

    {{-- Single Delete Form Helper --}}
    <form id="single-delete-form" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>

    <script>
        const selectAll = document.getElementById('select-all');
        const checkboxes = document.querySelectorAll('.article-checkbox');
        const countSpan = document.getElementById('selected-count');

        function updateCount() {
            const count = document.querySelectorAll('.article-checkbox:checked').length;
            countSpan.textContent = count + ' selected';
        }

        if (selectAll) {
            selectAll.addEventListener('change', function () {
                checkboxes.forEach(cb => cb.checked = selectAll.checked);
                updateCount();
            });
        }

        checkboxes.forEach(cb => cb.addEventListener('change', updateCount));

        function confirmBulkAction() {
            const action = document.getElementById('bulk-action-select').value;
            const count = document.querySelectorAll('.article-checkbox:checked').length;
            if (!action) {
                alert('Please select a bulk action.');
                return false;
            }
            if (count === 0) {
                alert('Please select at least one article.');
                return false;
            }
            return confirm(`Apply "${action}" to ${count} selected articles?`);
        }

        function deleteSingle(id) {
            if (confirm('Delete this article?')) {
                const form = document.getElementById('single-delete-form');
                form.action = `/admin/articles/${id}`;
                form.submit();
            }
        }
    </script>

</div>
@endsection
