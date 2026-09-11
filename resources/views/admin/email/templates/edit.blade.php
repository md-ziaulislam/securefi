@extends('layouts.admin')
@section('title', 'Edit Email Template')
@section('header_title', 'Edit Template: ' . $template->name)

@section('content')
<div class="max-w-5xl space-y-6">

    <div class="flex items-center justify-between">
        <a href="{{ route('admin.email.templates.index') }}" class="text-xs font-mono text-[#808080] hover:text-[#111111] flex items-center gap-1">
            ← Back to Templates
        </a>
        <a href="{{ route('admin.email.templates.preview', $template) }}" target="_blank"
            class="text-xs font-mono border border-[#111111]/20 px-3 py-1 hover:bg-[#F5F1E8] transition-colors">
            Live Preview ↗
        </a>
    </div>

    <form action="{{ route('admin.email.templates.update', $template) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-white border border-[#111111]/15 p-6 space-y-5">
            <div class="border-b border-[#111111]/10 pb-3">
                <h3 class="font-bold text-sm text-[#111111]">Template Details</h3>
                <p class="text-xs font-mono text-[#808080]">Configure metadata, subject line, and classification.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="sm:col-span-2">
                    <label for="name" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1 font-bold">Template Name *</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $template->name) }}" required
                        class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                </div>

                <div>
                    <label for="type" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1 font-bold">Category *</label>
                    <select id="type" name="type" required
                        class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                        <option value="newsletter" {{ old('type', $template->type) === 'newsletter' ? 'selected' : '' }}>Newsletter Broadcast</option>
                        <option value="transactional" {{ old('type', $template->type) === 'transactional' ? 'selected' : '' }}>Transactional / Alert</option>
                        <option value="custom" {{ old('type', $template->type) === 'custom' ? 'selected' : '' }}>Custom Campaign</option>
                    </select>
                </div>
            </div>

            <div>
                <label for="subject" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1 font-bold">Email Subject Line *</label>
                <input type="text" id="subject" name="subject" value="{{ old('subject', $template->subject) }}" required
                    class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
            </div>

            <div>
                <label for="preview_text" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1">Preheader / Snippet Text</label>
                <input type="text" id="preview_text" name="preview_text" value="{{ old('preview_text', $template->preview_text) }}"
                    class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
            </div>
        </div>

        {{-- Template HTML Body Editor --}}
        <div class="bg-white border border-[#111111]/15 p-6 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-[#111111]/10 pb-3">
                <div>
                    <h3 class="font-bold text-sm text-[#111111]">HTML Template Content *</h3>
                    <p class="text-xs font-mono text-[#808080]">Write responsive HTML with dynamic merge tags.</p>
                </div>

                <div class="flex flex-wrap items-center gap-1.5 text-xs font-mono">
                    <span class="text-[10px] text-[#808080]">Insert:</span>
                    <button type="button" data-tag="name" onclick="insertTag(this.dataset.tag)" class="px-2 py-0.5 border border-[#111111]/20 hover:bg-[#F5F1E8] text-[10px] font-mono">&#123;&#123;name&#125;&#125;</button>
                    <button type="button" data-tag="email" onclick="insertTag(this.dataset.tag)" class="px-2 py-0.5 border border-[#111111]/20 hover:bg-[#F5F1E8] text-[10px] font-mono">&#123;&#123;email&#125;&#125;</button>
                    <button type="button" data-tag="site_name" onclick="insertTag(this.dataset.tag)" class="px-2 py-0.5 border border-[#111111]/20 hover:bg-[#F5F1E8] text-[10px] font-mono">&#123;&#123;site_name&#125;&#125;</button>
                    <button type="button" data-tag="unsubscribe_url" onclick="insertTag(this.dataset.tag)" class="px-2 py-0.5 border border-[#111111]/20 hover:bg-[#F5F1E8] text-[10px] font-mono">&#123;&#123;unsubscribe_url&#125;&#125;</button>
                </div>
            </div>

            <div>
                <textarea id="body" name="body" rows="16" required
                    class="w-full px-4 py-3 text-xs font-mono bg-[#FAFAFA] border border-[#111111]/30 focus:outline-none focus:border-[#111111] leading-relaxed">{{ old('body', $template->body) }}</textarea>
            </div>
        </div>

        <div class="flex items-center justify-between">
            <a href="{{ route('admin.email.templates.index') }}" class="text-xs font-mono text-[#808080] hover:text-[#111111]">
                Cancel
            </a>
            <button type="submit" class="btn-primary text-xs px-6 py-2.5 font-mono font-bold">
                Save Changes →
            </button>
        </div>
    </form>

</div>

<script>
function insertTag(tagName) {
    const tag = '{{' + tagName + '}}';
    const textarea = document.getElementById('body');
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const text = textarea.value;
    textarea.value = text.substring(0, start) + tag + text.substring(end);
    textarea.focus();
    textarea.setSelectionRange(start + tag.length, start + tag.length);
}
</script>
@endsection
