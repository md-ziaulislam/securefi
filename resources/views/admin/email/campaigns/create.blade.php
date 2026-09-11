@extends('layouts.admin')
@section('title', 'Create Campaign')
@section('header_title', 'Create Email Campaign')

@section('content')
<div class="max-w-4xl space-y-6">

    <div class="flex items-center justify-between">
        <a href="{{ route('admin.email.campaigns.index') }}" class="text-xs font-mono text-[#808080] hover:text-[#111111] flex items-center gap-1">
            ← Back to Campaigns
        </a>
    </div>

    {{-- Audience Counter Banner --}}
    <div class="bg-[#F8F8F6] border border-[#111111]/15 p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h3 class="text-sm font-bold text-[#111111]">Audience Overview</h3>
            <p class="text-xs font-mono text-[#808080] mt-0.5">Campaigns are delivered via your configured SMTP host.</p>
        </div>
        <div class="text-xs font-mono bg-white border border-[#111111]/15 px-3 py-2">
            <span class="text-[#808080]">Active Subscribers Available:</span>
            <strong class="text-[#111111] ml-1">{{ number_format($subscribers) }}</strong>
        </div>
    </div>

    <form action="{{ route('admin.email.campaigns.store') }}" method="POST" class="space-y-6">
        @csrf

        <div class="bg-white border border-[#111111]/15 p-6 space-y-5">
            <div class="border-b border-[#111111]/10 pb-3">
                <h3 class="font-bold text-sm text-[#111111]">Campaign Setup</h3>
                <p class="text-xs font-mono text-[#808080]">Targeting parameters and subject line definition.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="name" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1 font-bold">Campaign Name (Internal) *</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required
                        placeholder="e.g. October Security Briefing #4"
                        class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                </div>

                <div>
                    <label for="audience" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1 font-bold">Target Audience *</label>
                    <select id="audience" name="audience" required onchange="toggleCustomAudience(this.value)"
                        class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                        <option value="active" {{ old('audience') === 'active' ? 'selected' : '' }}>Active Subscribers Only ({{ $subscribers }} contacts)</option>
                        <option value="all" {{ old('audience') === 'all' ? 'selected' : '' }}>All Subscribers ({{ $totalSubscribers }} contacts)</option>
                        <option value="users" {{ old('audience') === 'users' ? 'selected' : '' }}>Registered Users & Staff ({{ $usersCount }} members)</option>
                        <option value="custom" {{ old('audience') === 'custom' ? 'selected' : '' }}>Custom Email List (Paste emails)</option>
                    </select>
                </div>
            </div>

            <div id="custom-emails-container" class="{{ old('audience') === 'custom' ? '' : 'hidden' }} space-y-1">
                <label for="custom_emails" class="block text-xs font-mono uppercase tracking-wider text-[#111111] font-bold">Custom Recipient Emails *</label>
                <textarea id="custom_emails" name="custom_emails" rows="3"
                    placeholder="user1@example.com, user2@domain.com"
                    class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">{{ old('custom_emails') }}</textarea>
                <p class="text-[11px] font-mono text-[#808080]">Separate by comma, space, or new lines.</p>
            </div>

            <div>
                <label for="subject" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1 font-bold">Email Subject Line *</label>
                <input type="text" id="subject" name="subject" value="{{ old('subject') }}" required
                    placeholder="e.g. 🚨 Urgent Security Advisory: Update Your MFA Now"
                    class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
            </div>

            <div>
                <label for="template_id" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1 font-bold">Select Email Template</label>
                <select id="template_id" name="template_id" onchange="toggleTemplateMode(this.value)"
                    class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                    <option value="">-- Or use Custom HTML body below --</option>
                    @foreach($templates as $tpl)
                        <option value="{{ $tpl->id }}" data-subject="{{ $tpl->subject }}" {{ old('template_id') == $tpl->id ? 'selected' : '' }}>
                            {{ $tpl->name }} ({{ $tpl->type }})
                        </option>
                    @endforeach
                </select>
                <p class="text-[11px] font-mono text-[#808080] mt-1">If a template is selected, its layout and design will be used automatically.</p>
            </div>
        </div>

        {{-- Custom Body Section (shown if no template or customized) --}}
        <div id="custom-body-section" class="bg-white border border-[#111111]/15 p-6 space-y-4">
            <div class="border-b border-[#111111]/10 pb-3">
                <h3 class="font-bold text-sm text-[#111111]">Custom Campaign HTML Content</h3>
                <p class="text-xs font-mono text-[#808080]">Optional when a template is selected, or required if no template is chosen.</p>
            </div>

            <div>
                <textarea id="body" name="body" rows="10"
                    placeholder="Enter custom HTML body here (e.g. &lt;h2&gt;Hello &#123;&#123;name&#125;&#125;&lt;/h2&gt;)..."
                    class="w-full px-4 py-3 text-xs font-mono bg-[#FAFAFA] border border-[#111111]/30 focus:outline-none focus:border-[#111111] leading-relaxed">{{ old('body') }}</textarea>
            </div>
        </div>

        <div class="flex items-center justify-between">
            <a href="{{ route('admin.email.campaigns.index') }}" class="text-xs font-mono text-[#808080] hover:text-[#111111]">
                Cancel
            </a>
            <button type="submit" class="btn-primary text-xs px-6 py-2.5 font-mono font-bold">
                Save Campaign as Draft →
            </button>
        </div>
    </form>

</div>

<script>
function toggleTemplateMode(templateId) {
    const select = document.getElementById('template_id');
    const selectedOption = select.options[select.selectedIndex];
    const subjectInput = document.getElementById('subject');
    
    if (templateId && selectedOption.dataset.subject && !subjectInput.value) {
        subjectInput.value = selectedOption.dataset.subject;
    }
}

function toggleCustomAudience(val) {
    const container = document.getElementById('custom-emails-container');
    if (val === 'custom') {
        container.classList.remove('hidden');
    } else {
        container.classList.add('hidden');
    }
}
</script>
@endsection
