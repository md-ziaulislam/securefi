@extends('layouts.admin')

@section('title', 'Comments Moderation')
@section('header_title', 'Reader Comments & Discussions')

@section('header_badge')
    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 border border-[#111111]/20 bg-[#F5F1E8] text-[#111111] font-mono text-[11px] uppercase font-bold">
        <span>{{ $pendingCount }} Pending Approval</span>
    </span>
@endsection

@section('content')
<div class="space-y-6"
     x-data="{
         replyModal: false,
         activeId: null,
         activeName: '',
         replyUrlTemplate: '{{ route('admin.comments.reply', ':id') }}',
         openReply(id, name) {
             this.activeId = id;
             this.activeName = name;
             this.replyModal = true;
             const form = document.getElementById('admin-reply-form');
             if (form) {
                 form.action = this.replyUrlTemplate.replace(':id', id);
             }
             this.$nextTick(() => {
                 document.getElementById('reply-modal-content')?.focus();
             });
         },
         closeReply() {
             this.replyModal = false;
             this.activeId = null;
             this.activeName = '';
             const form = document.getElementById('admin-reply-form');
             if (form) {
                 form.action = '#';
             }
         },
         get replyAction() {
             return this.activeId ? this.replyUrlTemplate.replace(':id', this.activeId) : '#';
         }
     }"
     @keydown.escape.window="closeReply()">

    {{-- Flash Notifications --}}
    @if (session('success'))
        <div class="flex items-center gap-3 p-3.5 bg-emerald-50 border border-emerald-300 text-emerald-800 font-mono text-xs">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="flex items-center gap-3 p-3.5 bg-rose-50 border border-rose-300 text-rose-800 font-mono text-xs">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('error') }}
        </div>
    @endif

    <!-- Filter Bar & Search -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-[#111111]/15 pb-4">
        <!-- Status Tabs -->
        <div class="flex items-center gap-1.5 flex-wrap">
            <a href="{{ route('admin.comments.index', ['status' => 'all', 'q' => $search]) }}"
               class="px-3 py-1.5 text-xs font-mono uppercase tracking-wider transition-colors border {{ $status === 'all' ? 'bg-[#111111] text-white border-[#111111] font-bold' : 'bg-white text-[#111111] border-[#111111]/20 hover:bg-[#F5F1E8]' }}">
                All ({{ $totalCount }})
            </a>
            <a href="{{ route('admin.comments.index', ['status' => 'pending', 'q' => $search]) }}"
               class="px-3 py-1.5 text-xs font-mono uppercase tracking-wider transition-colors border {{ $status === 'pending' ? 'bg-[#111111] text-white border-[#111111] font-bold' : 'bg-white text-[#111111] border-[#111111]/20 hover:bg-[#F5F1E8]' }}">
                Pending ({{ $pendingCount }})
            </a>
            <a href="{{ route('admin.comments.index', ['status' => 'approved', 'q' => $search]) }}"
               class="px-3 py-1.5 text-xs font-mono uppercase tracking-wider transition-colors border {{ $status === 'approved' ? 'bg-[#111111] text-white border-[#111111] font-bold' : 'bg-white text-[#111111] border-[#111111]/20 hover:bg-[#F5F1E8]' }}">
                Approved ({{ $approvedCount }})
            </a>
            <a href="{{ route('admin.comments.index', ['status' => 'spam', 'q' => $search]) }}"
               class="px-3 py-1.5 text-xs font-mono uppercase tracking-wider transition-colors border {{ $status === 'spam' ? 'bg-[#111111] text-white border-[#111111] font-bold' : 'bg-white text-[#111111] border-[#111111]/20 hover:bg-[#F5F1E8]' }}">
                Spam ({{ $spamCount }})
            </a>
        </div>

        <!-- Search Box -->
        <form action="{{ route('admin.comments.index') }}" method="GET" class="flex items-center gap-2">
            <input type="hidden" name="status" value="{{ $status }}">
            <div class="relative">
                <input type="text" name="q" value="{{ $search }}" placeholder="Search author, email, text..."
                       class="w-64 px-3 py-1.5 bg-white border border-[#111111]/20 text-xs font-mono placeholder:text-[#808080] focus:outline-none focus:border-[#111111]">
            </div>
            <button type="submit" class="px-3 py-1.5 bg-[#111111] text-white text-xs font-mono uppercase tracking-wider hover:bg-neutral-800 transition-colors">
                Search
            </button>
            @if ($search)
                <a href="{{ route('admin.comments.index', ['status' => $status]) }}" class="px-2 py-1.5 border border-[#111111]/20 text-xs font-mono hover:bg-[#F5F1E8]">Clear</a>
            @endif
        </form>
    </div>

    <!-- Comments Table -->
    <div class="bg-white border border-[#111111]/15">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-[#111111]/15 bg-[#F5F1E8] font-mono uppercase tracking-wider text-[#808080]">
                        <th class="py-3 px-4">Author</th>
                        <th class="py-3 px-4">Comment Discussion</th>
                        <th class="py-3 px-4">Target Article</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Submitted</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#111111]/10">
                    @forelse ($comments as $item)
                        <tr class="hover:bg-[#F5F1E8]/30 transition-colors {{ $item->status === 'pending' ? 'bg-amber-50/40' : '' }}">

                            <!-- Author Cell -->
                            <td class="py-3.5 px-4 align-top">
                                <div class="flex items-start gap-2.5">
                                    <div class="w-8 h-8 bg-[#111111] text-white flex items-center justify-center font-mono text-xs font-bold shrink-0">
                                        {{ $item->initials }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-[#111111] flex items-center gap-1.5 flex-wrap">
                                            <span>{{ $item->name }}</span>
                                            @if ($item->is_admin_reply)
                                                <span class="px-1.5 py-0.5 bg-[#111111] text-white text-[9px] font-mono uppercase tracking-wider">Staff</span>
                                            @endif
                                        </div>
                                        <div class="font-mono text-[11px] text-[#808080]">{{ $item->email }}</div>
                                        @if ($item->website)
                                            <a href="{{ $item->website }}" target="_blank" class="font-mono text-[10px] text-neutral-400 hover:underline block truncate max-w-[150px]">
                                                {{ $item->website }}
                                            </a>
                                        @endif
                                        <div class="font-mono text-[10px] text-[#808080]/70 mt-0.5">IP: {{ $item->ip_address ?: '—' }}</div>
                                    </div>
                                </div>
                            </td>

                            <!-- Comment Content Cell -->
                            <td class="py-3.5 px-4 align-top max-w-xs">
                                @if ($item->parent)
                                    <div class="mb-1.5 text-[10px] font-mono text-[#808080] flex items-center gap-1">
                                        <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                                        </svg>
                                        <span>Replying to <strong>{{ $item->parent->name }}</strong></span>
                                    </div>
                                @endif
                                <p class="text-xs text-[#111111] leading-relaxed line-clamp-5 break-words whitespace-pre-line">
                                    {{ $item->content }}
                                </p>
                            </td>

                            <!-- Article Cell -->
                            <td class="py-3.5 px-4 align-top max-w-[180px]">
                                @if ($item->article)
                                    <a href="{{ route('article.show', $item->article->slug) }}" target="_blank"
                                       class="font-bold text-xs text-[#111111] hover:underline line-clamp-2 block">
                                        {{ $item->article->title }}
                                    </a>
                                    <div class="font-mono text-[10px] text-[#808080] mt-0.5 truncate">
                                        /{{ $item->article->slug }}
                                    </div>
                                @else
                                    <span class="text-[#808080] font-mono text-xs italic">Article deleted</span>
                                @endif
                            </td>

                            <!-- Status Badge Cell -->
                            <td class="py-3.5 px-4 align-top whitespace-nowrap">
                                @if ($item->status === 'approved')
                                    <span class="inline-flex items-center px-2 py-0.5 bg-emerald-50 text-emerald-800 border border-emerald-300 font-mono text-[10px] uppercase font-bold">Approved</span>
                                @elseif ($item->status === 'pending')
                                    <span class="inline-flex items-center px-2 py-0.5 bg-amber-50 text-amber-800 border border-amber-300 font-mono text-[10px] uppercase font-bold">Pending</span>
                                @elseif ($item->status === 'spam')
                                    <span class="inline-flex items-center px-2 py-0.5 bg-neutral-100 text-neutral-700 border border-neutral-300 font-mono text-[10px] uppercase font-bold">Spam</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 bg-rose-50 text-rose-800 border border-rose-300 font-mono text-[10px] uppercase font-bold">Rejected</span>
                                @endif
                            </td>

                            <!-- Submitted Cell -->
                            <td class="py-3.5 px-4 align-top font-mono text-[11px] text-[#808080] whitespace-nowrap">
                                <div>{{ $item->created_at->format('M d, Y') }}</div>
                                <div class="text-[10px]">{{ $item->created_at->diffForHumans() }}</div>
                            </td>

                            <!-- Actions Cell -->
                            <td class="py-3.5 px-4 align-top">
                                <div class="flex items-center justify-end gap-1.5 flex-wrap">

                                    {{-- Approve --}}
                                    @if ($item->status !== 'approved')
                                        <form action="{{ route('admin.comments.approve', $item->id) }}" method="POST">
                                            @csrf
                                            <button type="submit"
                                                class="inline-flex items-center gap-1 px-2 py-1 bg-emerald-600 text-white font-mono text-[10px] uppercase font-bold hover:bg-emerald-700 transition-colors"
                                                title="Approve">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                Approve
                                            </button>
                                        </form>
                                    @endif

                                    {{-- Reject --}}
                                    @if ($item->status !== 'rejected')
                                        <form action="{{ route('admin.comments.reject', $item->id) }}" method="POST">
                                            @csrf
                                            <button type="submit"
                                                class="inline-flex items-center gap-1 px-2 py-1 border border-rose-400 text-rose-700 bg-rose-50 font-mono text-[10px] uppercase font-bold hover:bg-rose-100 transition-colors"
                                                title="Reject">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                                Reject
                                            </button>
                                        </form>
                                    @endif

                                    {{-- Staff Reply --}}
                                    <button type="button"
                                        @click="openReply({{ $item->id }}, @js($item->name))"
                                        class="inline-flex items-center gap-1 px-2 py-1 border border-[#111111] bg-white text-[#111111] font-mono text-[10px] uppercase hover:bg-[#111111] hover:text-white transition-colors"
                                        title="Reply as Staff">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                                        Reply
                                    </button>

                                    {{-- Mark Spam --}}
                                    @if ($item->status !== 'spam')
                                        <form action="{{ route('admin.comments.spam', $item->id) }}" method="POST">
                                            @csrf
                                            <button type="submit"
                                                class="p-1 border border-amber-300 text-amber-600 bg-amber-50 hover:bg-amber-100 transition-colors"
                                                title="Mark as Spam">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                            </button>
                                        </form>
                                    @endif

                                    {{-- Delete --}}
                                    <form action="{{ route('admin.comments.destroy', $item->id) }}" method="POST"
                                          onsubmit="return confirm('Permanently delete this comment and its replies?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="p-1 border border-rose-200 text-rose-600 hover:bg-rose-50 transition-colors"
                                            title="Delete permanently">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>

                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-16 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <svg class="w-8 h-8 text-[#808080]/40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                    <p class="text-[#808080] font-mono text-xs">No comments found for the selected filter.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($comments->hasPages())
            <div class="p-4 border-t border-[#111111]/10 flex items-center justify-between">
                <span class="text-xs font-mono text-[#808080]">
                    Showing {{ $comments->firstItem() }}–{{ $comments->lastItem() }} of {{ $comments->total() }} comments
                </span>
                <div class="flex items-center gap-1">
                    @if ($comments->onFirstPage())
                        <span class="px-3 py-1.5 border border-[#111111]/10 text-[#808080] font-mono text-xs cursor-not-allowed">← Prev</span>
                    @else
                        <a href="{{ $comments->previousPageUrl() }}" class="px-3 py-1.5 border border-[#111111]/20 text-[#111111] font-mono text-xs hover:bg-[#F5F1E8] transition-colors">← Prev</a>
                    @endif

                    @foreach ($comments->getUrlRange(max(1, $comments->currentPage() - 2), min($comments->lastPage(), $comments->currentPage() + 2)) as $page => $url)
                        @if ($page == $comments->currentPage())
                            <span class="px-3 py-1.5 bg-[#111111] text-white font-mono text-xs font-bold">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="px-3 py-1.5 border border-[#111111]/20 text-[#111111] font-mono text-xs hover:bg-[#F5F1E8] transition-colors">{{ $page }}</a>
                        @endif
                    @endforeach

                    @if ($comments->hasMorePages())
                        <a href="{{ $comments->nextPageUrl() }}" class="px-3 py-1.5 border border-[#111111]/20 text-[#111111] font-mono text-xs hover:bg-[#F5F1E8] transition-colors">Next →</a>
                    @else
                        <span class="px-3 py-1.5 border border-[#111111]/10 text-[#808080] font-mono text-xs cursor-not-allowed">Next →</span>
                    @endif
                </div>
            </div>
        @endif
    </div>

    <!-- Staff Reply Modal -->
    <div x-show="replyModal"
         class="fixed inset-0 bg-black/70 z-50 flex items-center justify-center p-4"
         x-cloak
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @keydown.escape.window="closeReply()">

        <div class="bg-white border border-[#111111] max-w-lg w-full p-6 space-y-4 shadow-2xl"
             @click.outside="closeReply()"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">

            <!-- Modal Header -->
            <div class="flex items-start justify-between border-b border-[#111111]/15 pb-3">
                <div>
                    <h3 class="font-bold text-sm text-[#111111] font-mono uppercase tracking-wider">
                        Official Staff Reply
                    </h3>
                    <p class="text-[11px] font-mono text-[#808080] mt-0.5">
                        Replying to:
                        <span class="font-bold text-[#111111]" x-text="activeName"></span>
                    </p>
                </div>
                <button type="button" @click="closeReply()"
                    class="w-7 h-7 flex items-center justify-center border border-[#111111]/20 text-[#808080] hover:text-[#111111] hover:bg-[#F5F1E8] font-mono text-base transition-colors shrink-0 ml-4"
                    title="Close">
                    ×
                </button>
            </div>

            <p class="text-xs text-[#808080] font-mono leading-relaxed">
                Your reply will be published immediately with the verified
                <span class="px-1 py-0.5 bg-[#111111] text-white text-[9px] font-mono uppercase">Staff</span>
                badge visible on the article discussion thread.
            </p>

            <form :action="replyAction"
                  action="#"
                  method="POST"
                  class="space-y-4"
                  id="admin-reply-form"
                  @submit="if (!activeId) { $event.preventDefault(); return false; }">
                @csrf
                <div>
                    <label class="block text-xs font-mono uppercase font-bold text-[#111111] mb-1.5 tracking-wider">
                        Reply Message <span class="text-rose-500">*</span>
                    </label>
                    <textarea name="content" rows="5" required
                              id="reply-modal-content"
                              placeholder="Write your official staff response..."
                              class="w-full p-3 border border-[#111111]/20 bg-[#F5F1E8]/30 text-xs font-mono focus:outline-none focus:border-[#111111] resize-none transition-colors placeholder:text-[#808080]/60"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-[#111111]/10">
                    <button type="button" @click="closeReply()"
                        class="px-4 py-2 border border-[#111111]/20 text-xs font-mono uppercase tracking-wider hover:bg-[#F5F1E8] transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-5 py-2 bg-[#111111] text-white text-xs font-mono uppercase tracking-wider font-bold hover:bg-neutral-800 transition-colors">
                        Publish Reply
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
