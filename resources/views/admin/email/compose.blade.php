@extends('layouts.admin')
@section('title', 'Direct Mail Composer')
@section('header_title', 'Direct Email & Multi-Recipient Composer')

@section('content')
<div class="max-w-6xl space-y-6">

    {{-- Header Banner --}}
    <div class="bg-white border border-[#111111]/15 p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h3 class="text-sm font-bold text-[#111111]">Quick Mail & Multi-Target Broadcast</h3>
            <p class="text-xs font-mono text-[#808080] mt-0.5">Send targeted messages to individual users, custom pasted lists, or entire groups without configuring a formal campaign.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.email.logs.index') }}" class="text-xs font-mono border border-[#111111]/20 px-3 py-1.5 hover:bg-[#F5F1E8] transition-colors">
                View Delivery Logs →
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

        {{-- Left Form: Composer (8 cols) --}}
        <div class="lg:col-span-8 space-y-6">
            <form id="compose-form" action="{{ route('admin.email.compose.send') }}" method="POST" class="space-y-6">
                @csrf

                {{-- Target Audience Selection --}}
                <div class="bg-white border border-[#111111]/15 p-6 space-y-5">
                    <div class="border-b border-[#111111]/10 pb-3">
                        <h4 class="font-bold text-sm text-[#111111]">1. Audience & Recipients</h4>
                        <p class="text-xs font-mono text-[#808080]">Choose who will receive this message.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-2 font-bold">Target Mode *</label>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                            <label class="border border-[#111111]/20 p-3 flex items-start gap-2.5 cursor-pointer hover:bg-[#F8F8F6] transition-colors">
                                <input type="radio" name="target_type" value="single" onchange="switchTargetMode('single')"
                                    {{ old('target_type', $prefillMode) === 'single' ? 'checked' : '' }} class="mt-0.5">
                                <div>
                                    <div class="text-xs font-bold text-[#111111]">Single Recipient</div>
                                    <div class="text-[10px] text-[#808080] font-mono">One-off direct email</div>
                                </div>
                            </label>

                            <label class="border border-[#111111]/20 p-3 flex items-start gap-2.5 cursor-pointer hover:bg-[#F8F8F6] transition-colors">
                                <input type="radio" name="target_type" value="custom_list" onchange="switchTargetMode('custom_list')"
                                    {{ old('target_type', $prefillMode) === 'custom_list' ? 'checked' : '' }} class="mt-0.5">
                                <div>
                                    <div class="text-xs font-bold text-[#111111]">Custom Email List</div>
                                    <div class="text-[10px] text-[#808080] font-mono">Paste comma/newline list</div>
                                </div>
                            </label>

                            <label class="border border-[#111111]/20 p-3 flex items-start gap-2.5 cursor-pointer hover:bg-[#F8F8F6] transition-colors">
                                <input type="radio" name="target_type" value="active_subscribers" onchange="switchTargetMode('active_subscribers')"
                                    {{ old('target_type', $prefillMode) === 'active_subscribers' ? 'checked' : '' }} class="mt-0.5">
                                <div>
                                    <div class="text-xs font-bold text-[#111111]">Active Subscribers</div>
                                    <div class="text-[10px] text-[#808080] font-mono">{{ number_format($subscribersCount) }} contacts</div>
                                </div>
                            </label>

                            <label class="border border-[#111111]/20 p-3 flex items-start gap-2.5 cursor-pointer hover:bg-[#F8F8F6] transition-colors">
                                <input type="radio" name="target_type" value="registered_users" onchange="switchTargetMode('registered_users')"
                                    {{ old('target_type', $prefillMode) === 'registered_users' ? 'checked' : '' }} class="mt-0.5">
                                <div>
                                    <div class="text-xs font-bold text-[#111111]">Registered Users</div>
                                    <div class="text-[10px] text-[#808080] font-mono">{{ number_format($usersCount) }} staff/members</div>
                                </div>
                            </label>

                            @if(!empty($prefillIds))
                            <label class="border border-[#111111]/20 p-3 flex items-start gap-2.5 cursor-pointer bg-[#F5F1E8]">
                                <input type="radio" name="target_type" value="selected_subscribers" onchange="switchTargetMode('selected_subscribers')"
                                    {{ old('target_type', $prefillMode) === 'selected_subscribers' ? 'checked' : '' }} class="mt-0.5">
                                <div>
                                    <div class="text-xs font-bold text-[#111111]">Selected Subscribers</div>
                                    <div class="text-[10px] text-[#808080] font-mono">Checkbox selection</div>
                                </div>
                            </label>
                            @endif
                        </div>
                    </div>

                    {{-- Target: Single --}}
                    <div id="target-single" class="space-y-4 pt-2 {{ old('target_type', $prefillMode) === 'single' ? '' : 'hidden' }}">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="recipient_email" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1 font-bold">Recipient Email *</label>
                                <input type="email" id="recipient_email" name="recipient_email" value="{{ old('recipient_email', $prefillEmail) }}"
                                    placeholder="client@example.com"
                                    class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                            </div>
                            <div>
                                <label for="recipient_name" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1">Recipient Name</label>
                                <input type="text" id="recipient_name" name="recipient_name" value="{{ old('recipient_name', $prefillName) }}"
                                    placeholder="Jane Doe"
                                    class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                            </div>
                        </div>
                    </div>

                    {{-- Target: Custom List --}}
                    <div id="target-custom-list" class="space-y-2 pt-2 {{ old('target_type', $prefillMode) === 'custom_list' ? '' : 'hidden' }}">
                        <div class="flex items-center justify-between">
                            <label for="custom_emails" class="block text-xs font-mono uppercase tracking-wider text-[#111111] font-bold">Paste Email List *</label>
                            <span id="custom-email-count" class="text-[11px] font-mono text-[#808080]">0 emails detected</span>
                        </div>
                        <textarea id="custom_emails" name="custom_emails" rows="4" oninput="updateDetectedEmailsCount(this.value)"
                            placeholder="user1@example.com, user2@domain.com, user3@enterprise.org"
                            class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">{{ old('custom_emails') }}</textarea>
                        <p class="text-[11px] font-mono text-[#808080]">Separate emails by comma, semicolon, space, or new lines. Duplicates will be automatically removed.</p>
                    </div>

                    {{-- Target: Selected Subscribers --}}
                    @if(!empty($prefillIds))
                    <div id="target-selected-subscribers" class="p-3 bg-[#F8F8F6] border border-[#111111]/15 text-xs font-mono {{ old('target_type', $prefillMode) === 'selected_subscribers' ? '' : 'hidden' }}">
                        <input type="hidden" name="subscriber_ids" value="{{ $prefillIds }}">
                        <span class="text-[#111111] font-bold">Subscribers Selected:</span>
                        <span class="text-[#808080]">IDs ({{ $prefillIds }})</span>
                    </div>
                    @endif
                </div>

                {{-- Email Subject & Message --}}
                <div class="bg-white border border-[#111111]/15 p-6 space-y-5">
                    <div class="border-b border-[#111111]/10 pb-3">
                        <h4 class="font-bold text-sm text-[#111111]">2. Subject & Content</h4>
                        <p class="text-xs font-mono text-[#808080]">Define your header parameters and email body.</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="sm:col-span-2">
                            <label for="subject" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1 font-bold">Subject Line *</label>
                            <input type="text" id="subject" name="subject" value="{{ old('subject') }}" required
                                placeholder="e.g. Important Update Regarding Your Account"
                                class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                        </div>

                        <div>
                            <label for="reply_to" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1">Reply-To Email</label>
                            <input type="email" id="reply_to" name="reply_to" value="{{ old('reply_to') }}"
                                placeholder="support@securofi.tech"
                                class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                        </div>
                    </div>

                    <div>
                        <label for="template_id" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1 font-bold">Load From Template (Optional)</label>
                        <select id="template_id" name="template_id" onchange="handleTemplateSelect(this.value)"
                            class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                            <option value="">-- Custom HTML (or start with blank canvas) --</option>
                            @foreach($templates as $tpl)
                                <option value="{{ $tpl->id }}" data-subject="{{ $tpl->subject }}" {{ old('template_id') == $tpl->id ? 'selected' : '' }}>
                                    {{ $tpl->name }} ({{ $tpl->type }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label for="body" class="block text-xs font-mono uppercase tracking-wider text-[#111111] font-bold">Message Body (HTML) *</label>
                            <div class="flex items-center gap-1.5 text-xs font-mono">
                                <span class="text-[10px] text-[#808080]">Insert:</span>
                                <button type="button" data-tag="name" onclick="insertTag(this.dataset.tag)" class="px-2 py-0.5 border border-[#111111]/20 hover:bg-[#F5F1E8] text-[10px] font-mono">&#123;&#123;name&#125;&#125;</button>
                                <button type="button" data-tag="email" onclick="insertTag(this.dataset.tag)" class="px-2 py-0.5 border border-[#111111]/20 hover:bg-[#F5F1E8] text-[10px] font-mono">&#123;&#123;email&#125;&#125;</button>
                                <button type="button" data-tag="site_name" onclick="insertTag(this.dataset.tag)" class="px-2 py-0.5 border border-[#111111]/20 hover:bg-[#F5F1E8] text-[10px] font-mono">&#123;&#123;site_name&#125;&#125;</button>
                            </div>
                        </div>

                        <textarea id="body" name="body" rows="12"
                            placeholder="Enter HTML or plain text message content..."
                            class="w-full px-4 py-3 text-xs font-mono bg-[#FAFAFA] border border-[#111111]/30 focus:outline-none focus:border-[#111111] leading-relaxed">@if(old('body')){{ old('body') }}@else<h2>Hello @{{name}},</h2>
<p>We are reaching out to you with important information from {{ \App\Models\Setting::get('site_name', 'SecuroFi.Tech') }}.</p>

<p>Best regards,<br>The Support Team</p>@endif</textarea>
                    </div>
                </div>

                {{-- Submit Bar --}}
                <div class="flex items-center justify-between pt-2">
                    <a href="{{ route('admin.email.subscribers.index') }}" class="text-xs font-mono text-[#808080] hover:text-[#111111]">
                        Cancel
                    </a>

                    <button type="submit" onclick="return confirm('Ready to broadcast this message to selected recipients via SMTP?')"
                        class="btn-primary text-xs px-8 py-3 font-mono font-bold tracking-wider">
                        Send Direct Email Broadcast →
                    </button>
                </div>
            </form>
        </div>

        {{-- Right Column: Test Email Preview & Tips (4 cols) --}}
        <div class="lg:col-span-4 space-y-6">

            {{-- Instant Test Card --}}
            <div class="bg-white border border-[#111111]/15 p-6 space-y-4">
                <div class="border-b border-[#111111]/10 pb-3">
                    <h4 class="font-bold text-sm text-[#111111]">Send Test Preview</h4>
                    <p class="text-xs font-mono text-[#808080]">Verify layout and rendering in your own inbox before broadcasting.</p>
                </div>

                <form action="{{ route('admin.email.compose.test') }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="subject" id="test_subject_sync">
                    <input type="hidden" name="template_id" id="test_template_sync">
                    <input type="hidden" name="body" id="test_body_sync">
                    <input type="hidden" name="reply_to" id="test_reply_to_sync">

                    <div>
                        <label for="test_email" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1 font-bold">Test Recipient *</label>
                        <input type="email" id="test_email" name="test_email" value="{{ auth()->user()->email ?? '' }}" required
                            class="w-full px-3 py-1.5 text-xs font-mono bg-[#F8F8F6] border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                    </div>

                    <button type="submit" onclick="syncTestData()" class="w-full bg-[#111111] text-white py-2 text-xs font-mono font-bold hover:bg-[#222222] transition-colors">
                        Send Test to My Inbox →
                    </button>
                </form>

                <div class="pt-2 text-[11px] font-mono text-[#808080] leading-relaxed border-t border-[#111111]/10">
                    Sends an instant test copy with dynamic tags substituted using your profile credentials.
                </div>
            </div>

            {{-- Quick Targeting Guide --}}
            <div class="bg-[#F8F8F6] border border-[#111111]/15 p-5 space-y-3">
                <h5 class="text-xs font-bold uppercase tracking-wider text-[#111111]">Targeting Rules</h5>
                <ul class="text-xs font-mono text-[#666666] space-y-2 list-disc pl-4">
                    <li><strong class="text-[#111111]">Single:</strong> Perfect for one-off replies, account notices, or VIP alerts.</li>
                    <li><strong class="text-[#111111]">Custom List:</strong> Paste from spreadsheets or external CRM exports.</li>
                    <li><strong class="text-[#111111]">Subscribers:</strong> Automatically includes one-click unsubscribe links.</li>
                    <li><strong class="text-[#111111]">Logs:</strong> Every delivery outcome is tracked in Delivery Logs.</li>
                </ul>
            </div>

        </div>

    </div>
</div>

<script>
function switchTargetMode(mode) {
    document.getElementById('target-single').classList.add('hidden');
    document.getElementById('target-custom-list').classList.add('hidden');
    const selectedEl = document.getElementById('target-selected-subscribers');
    if (selectedEl) selectedEl.classList.add('hidden');

    if (mode === 'single') {
        document.getElementById('target-single').classList.remove('hidden');
    } else if (mode === 'custom_list') {
        document.getElementById('target-custom-list').classList.remove('hidden');
    } else if (mode === 'selected_subscribers' && selectedEl) {
        selectedEl.classList.remove('hidden');
    }
}

function updateDetectedEmailsCount(val) {
    const emails = val.split(/[\r\n,;]+/).map(s => s.trim()).filter(s => s.length > 3 && s.includes('@'));
    const unique = new Set(emails);
    document.getElementById('custom-email-count').innerText = `${unique.size} valid email(s) detected`;
}

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

function handleTemplateSelect(templateId) {
    const select = document.getElementById('template_id');
    const selectedOption = select.options[select.selectedIndex];
    const subjectInput = document.getElementById('subject');
    if (templateId && selectedOption.dataset.subject && !subjectInput.value) {
        subjectInput.value = selectedOption.dataset.subject;
    }
}

function syncTestData() {
    document.getElementById('test_subject_sync').value = document.getElementById('subject').value;
    document.getElementById('test_template_sync').value = document.getElementById('template_id').value;
    document.getElementById('test_body_sync').value = document.getElementById('body').value;
    document.getElementById('test_reply_to_sync').value = document.getElementById('reply_to').value;
}
</script>
@endsection
