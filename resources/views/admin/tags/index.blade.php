@extends('layouts.admin')

@section('title', 'Taxonomy Tags')
@section('header_title', 'Tags Inventory')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
    <div class="lg:col-span-4 bg-white border border-[#111111]/15 p-6 space-y-4">
        <h3 class="font-bold text-sm text-[#111111] border-b border-[#111111]/10 pb-3">Create Tag</h3>

        <form action="{{ route('admin.tags.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label for="name" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1 font-medium">Tag Name *</label>
                <input type="text" id="name" name="name" required
                    class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]"
                    placeholder="e.g. Zero Trust">
            </div>

            <div>
                <label for="slug" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1">Slug (Optional)</label>
                <input type="text" id="slug" name="slug"
                    class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]"
                    placeholder="zero-trust">
            </div>

            <div class="pt-2">
                <button type="submit" class="btn-primary w-full text-xs font-mono uppercase tracking-wider py-2.5">
                    Create Tag →
                </button>
            </div>
        </form>
    </div>

    <div class="lg:col-span-8 bg-white border border-[#111111]/15 p-6">
        <h3 class="font-bold text-sm text-[#111111] border-b border-[#111111]/10 pb-3 mb-4">Existing Tags</h3>

        <div class="flex flex-wrap gap-2">
            @forelse ($tags as $tag)
                <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-[#F8F8F6] border border-[#111111]/20 text-xs font-mono">
                    <a href="{{ route('tag.show', $tag->slug) }}" target="_blank" class="text-[#111111] font-bold hover:underline">
                        #{{ $tag->name }}
                    </a>
                    <span class="text-[10px] text-[#808080]">({{ $tag->articles_count }})</span>
                    <form action="{{ route('admin.tags.destroy', $tag->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete tag #{{ $tag->name }}?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-rose-500 hover:text-rose-800 font-bold ml-1">&times;</button>
                    </form>
                </div>
            @empty
                <p class="text-xs font-mono text-[#808080]">No tags created yet.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
