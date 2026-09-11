@extends('layouts.admin')
@section('title', 'SMTP & Mailer Settings')
@section('header_title', 'Email Server (SMTP) Configuration')

@section('content')
<div class="space-y-8 max-w-5xl">

    {{-- Info banner --}}
    <div class="bg-[#F8F8F6] border border-[#111111]/15 p-5 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <h3 class="text-sm font-bold text-[#111111]">Outbound Mail Engine</h3>
            <p class="text-xs font-mono text-[#808080] mt-1">Configure your SMTP server to broadcast newsletter campaigns, transactional notices, and user alerts.</p>
        </div>
        <div class="flex items-center gap-2 text-xs font-mono">
            <span class="inline-flex items-center px-2.5 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200">
                <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full mr-1.5 animate-pulse"></span>
                Status: {{ !empty($smtp['smtp_host']) ? 'Configured (' . $smtp['smtp_host'] . ')' : 'Not Configured' }}
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

        {{-- Left Form: Main SMTP Config (8 cols) --}}
        <div class="lg:col-span-8 space-y-6">
            <div class="bg-white border border-[#111111]/15 p-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-[#111111]/10 pb-4 mb-6">
                    <div>
                        <h4 class="font-bold text-sm text-[#111111]">SMTP Connection Parameters</h4>
                        <p class="text-xs font-mono text-[#808080]">Server credentials and TLS transport settings</p>
                    </div>
                    {{-- Quick provider presets --}}
                    <div class="flex flex-wrap items-center gap-1.5">
                        <span class="text-[10px] font-mono uppercase text-[#808080] mr-1">Presets:</span>
                        <button type="button" onclick="applyPreset('gmail')" class="px-2 py-1 text-[10px] font-mono border border-[#111111]/20 hover:bg-[#F5F1E8] transition-colors">Gmail</button>
                        <button type="button" onclick="applyPreset('mailtrap')" class="px-2 py-1 text-[10px] font-mono border border-[#111111]/20 hover:bg-[#F5F1E8] transition-colors">Mailtrap</button>
                        <button type="button" onclick="applyPreset('brevo')" class="px-2 py-1 text-[10px] font-mono border border-[#111111]/20 hover:bg-[#F5F1E8] transition-colors">Brevo</button>
                    </div>
                </div>

                <form action="{{ route('admin.email.smtp.update') }}" method="POST" class="space-y-5">
                    @csrf

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="sm:col-span-2">
                            <label for="smtp_host" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1.5 font-bold">SMTP Host *</label>
                            <input type="text" id="smtp_host" name="smtp_host" value="{{ old('smtp_host', $smtp['smtp_host']) }}" required
                                placeholder="smtp.gmail.com / smtp.mailtrap.io"
                                class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                        </div>

                        <div>
                            <label for="smtp_port" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1.5 font-bold">Port *</label>
                            <input type="number" id="smtp_port" name="smtp_port" value="{{ old('smtp_port', $smtp['smtp_port']) }}" required
                                placeholder="587"
                                class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="smtp_username" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1.5">Username / API Key</label>
                            <input type="text" id="smtp_username" name="smtp_username" value="{{ old('smtp_username', $smtp['smtp_username']) }}"
                                placeholder="username or api key"
                                class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                        </div>

                        <div>
                            <label for="smtp_password" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1.5">Password / Secret Key</label>
                            <div class="relative">
                                <input type="password" id="smtp_password" name="smtp_password" value="{{ old('smtp_password', $smtp['smtp_password']) }}"
                                    placeholder="••••••••••••••••"
                                    class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111] pr-10">
                                <button type="button" onclick="togglePasswordVisibility()" class="absolute inset-y-0 right-0 pr-3 flex items-center text-xs text-[#808080] hover:text-[#111111]">
                                    <span id="pw-toggle-text">Show</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="smtp_encryption" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1.5 font-bold">Encryption Protocol *</label>
                        <select id="smtp_encryption" name="smtp_encryption" required
                            class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                            <option value="tls" {{ old('smtp_encryption', $smtp['smtp_encryption']) === 'tls' ? 'selected' : '' }}>TLS (Recommended - Port 587)</option>
                            <option value="ssl" {{ old('smtp_encryption', $smtp['smtp_encryption']) === 'ssl' ? 'selected' : '' }}>SSL (Port 465)</option>
                            <option value="none" {{ old('smtp_encryption', $smtp['smtp_encryption']) === 'none' ? 'selected' : '' }}>None (Unencrypted / Local - Port 25/1025)</option>
                        </select>
                    </div>

                    <div class="border-t border-[#111111]/10 pt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="smtp_from_name" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1.5 font-bold">Sender Name (From Name) *</label>
                            <input type="text" id="smtp_from_name" name="smtp_from_name" value="{{ old('smtp_from_name', $smtp['smtp_from_name']) }}" required
                                placeholder="SecuroFi.Tech"
                                class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                        </div>

                        <div>
                            <label for="smtp_from_email" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1.5 font-bold">Sender Email (From Email) *</label>
                            <input type="email" id="smtp_from_email" name="smtp_from_email" value="{{ old('smtp_from_email', $smtp['smtp_from_email']) }}" required
                                placeholder="newsletter@securofi.tech"
                                class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                        </div>
                    </div>

                    <div class="pt-2 flex items-center justify-between">
                        <button type="submit" class="btn-primary text-xs px-6 py-2.5 font-mono font-bold tracking-wider">
                            Save SMTP Configuration
                        </button>
                        <span class="text-[11px] font-mono text-[#808080]">Applied in real-time across system mailers.</span>
                    </div>
                </form>
            </div>
        </div>

        {{-- Right Column: Live Test & Diagnostics (4 cols) --}}
        <div class="lg:col-span-4 space-y-6">

            {{-- Test Email Card --}}
            <div class="bg-white border border-[#111111]/15 p-6 space-y-4">
                <div class="border-b border-[#111111]/10 pb-3">
                    <h4 class="font-bold text-sm text-[#111111]">Test SMTP Connection</h4>
                    <p class="text-xs font-mono text-[#808080]">Send an instant verification email to test credentials.</p>
                </div>

                <form action="{{ route('admin.email.smtp.test') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="test_email" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1 font-bold">Recipient Email *</label>
                        <input type="email" id="test_email" name="test_email" value="{{ auth()->user()->email ?? '' }}" required
                            placeholder="your-email@example.com"
                            class="w-full px-3.5 py-2 text-xs font-mono bg-[#F8F8F6] border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                    </div>

                    <button type="submit" class="w-full bg-[#111111] text-white py-2 text-xs font-mono font-bold hover:bg-[#222222] transition-colors">
                        Send Verification Email →
                    </button>
                </form>

                <div class="pt-2 text-[11px] font-mono text-[#808080] leading-relaxed border-t border-[#111111]/10">
                    Saves you from launching campaigns with faulty ports or authentication errors.
                </div>
            </div>

            {{-- Setup Tips --}}
            <div class="bg-[#F8F8F6] border border-[#111111]/15 p-5 space-y-3">
                <h5 class="text-xs font-bold uppercase tracking-wider text-[#111111]">Common Setup Guidelines</h5>
                <ul class="text-xs font-mono text-[#666666] space-y-2 list-disc pl-4">
                    <li><strong class="text-[#111111]">Gmail:</strong> Use <code class="bg-white px-1 border border-black/10">smtp.gmail.com</code>, Port <code class="bg-white px-1 border border-black/10">587</code>, TLS. Generate a Google <em>App Password</em> (not your normal account password).</li>
                    <li><strong class="text-[#111111]">Mailtrap:</strong> Use <code class="bg-white px-1 border border-black/10">sandbox.smtp.mailtrap.io</code>, Port <code class="bg-white px-1 border border-black/10">2525</code> or <code class="bg-white px-1 border border-black/10">587</code> for safe staging/inbox testing.</li>
                    <li><strong class="text-[#111111]">Brevo / Sendgrid:</strong> Use Port <code class="bg-white px-1 border border-black/10">587</code> and verify sender domain to maximize delivery rates.</li>
                </ul>
            </div>
        </div>

    </div>
</div>

<script>
function togglePasswordVisibility() {
    const input = document.getElementById('smtp_password');
    const toggle = document.getElementById('pw-toggle-text');
    if (input.type === 'password') {
        input.type = 'text';
        toggle.innerText = 'Hide';
    } else {
        input.type = 'password';
        toggle.innerText = 'Show';
    }
}

function applyPreset(provider) {
    if (provider === 'gmail') {
        document.getElementById('smtp_host').value = 'smtp.gmail.com';
        document.getElementById('smtp_port').value = '587';
        document.getElementById('smtp_encryption').value = 'tls';
    } else if (provider === 'mailtrap') {
        document.getElementById('smtp_host').value = 'sandbox.smtp.mailtrap.io';
        document.getElementById('smtp_port').value = '2525';
        document.getElementById('smtp_encryption').value = 'tls';
    } else if (provider === 'brevo') {
        document.getElementById('smtp_host').value = 'smtp-relay.brevo.com';
        document.getElementById('smtp_port').value = '587';
        document.getElementById('smtp_encryption').value = 'tls';
    }
}
</script>
@endsection
