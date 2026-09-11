@extends('layouts.admin')

@section('title', 'Media Assets Library')
@section('header_title', 'Centralized Media Assets')

@section('content')
<div class="space-y-8">

    {{-- Upload Drop Area --}}
    <div class="bg-white border border-[#111111]/15 p-6">
        <h3 class="font-bold text-sm text-[#111111] mb-4">Upload Asset to Storage Disk</h3>

        <form action="{{ route('admin.media.store') }}" method="POST" enctype="multipart/form-data" class="flex flex-col sm:flex-row items-end gap-4">
            @csrf

            <div class="flex-1 w-full">
                <label for="file" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1 font-medium">Select Image File (Max 5MB) *</label>
                <input type="file" id="file" name="file" required accept="image/*"
                    class="w-full text-xs font-mono text-[#808080] file:mr-3 file:py-2 file:px-4 file:border-0 file:text-xs file:font-mono file:bg-primary file:text-secondary hover:file:opacity-90">
            </div>

            <div class="w-full sm:w-64">
                <label for="alt_text" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1">Alt Text (Accessibility)</label>
                <input type="text" id="alt_text" name="alt_text" placeholder="Visual description"
                    class="w-full px-3 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
            </div>

            <button type="submit" class="btn-primary text-xs font-mono py-2.5 px-6 shrink-0 w-full sm:w-auto">
                Upload Asset →
            </button>
        </form>
    </div>

    {{-- Media Grid --}}
    <div class="bg-white border border-[#111111]/15 p-6">
        <div class="flex items-center justify-between border-b border-[#111111]/10 pb-3 mb-6">
            <span class="text-xs font-mono uppercase tracking-widest text-[#111111] font-bold">Stored Images & Diagrams</span>
            <span class="text-xs font-mono text-[#808080]">{{ $media->total() }} total assets</span>
        </div>

        @if ($media->count() > 0)
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                @foreach ($media as $item)
                    <div class="swiss-card p-2 bg-[#F8F8F6] flex flex-col justify-between group">
                        <div class="aspect-square bg-white border border-[#111111]/10 overflow-hidden mb-2 relative">
                            <img src="{{ asset('storage/' . $item->file_path) }}" alt="{{ $item->alt_text }}"
                                class="w-full h-full object-cover">
                        </div>

                        <div class="space-y-1">
                            <div class="text-[10px] font-mono text-[#111111] truncate font-medium" title="{{ $item->file_name }}">
                                {{ $item->file_name }}
                            </div>
                            <div class="text-[9px] font-mono text-[#808080]">
                                {{ round($item->file_size / 1024) }} KB • {{ $item->mime_type }}
                            </div>
                        </div>

                        <div class="mt-2 pt-2 border-t border-[#111111]/10 flex items-center justify-between text-[10px] font-mono">
                            <button onclick="copyToClipboard('{{ asset('storage/' . $item->file_path) }}')" class="text-[#111111] font-bold hover:underline">
                                Copy URL
                            </button>
                            <form action="{{ route('admin.media.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Delete this media asset?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-rose-600 hover:underline">Delete</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-8 pt-4 border-t border-[#111111]/10">
                {{ $media->links() }}
            </div>
        @else
            <div class="py-12 text-center text-[#808080] text-xs font-mono">
                No media assets uploaded yet.
            </div>
        @endif
    </div>

</div>

<script>
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text);
        alert('Image URL copied to clipboard: ' + text);
    }
</script>
@endsection
