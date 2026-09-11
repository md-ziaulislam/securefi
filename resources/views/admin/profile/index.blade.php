@extends('layouts.admin')

@section('title', 'Staff Profile & Security')
@section('header_title', 'Staff Profile & Security Credentials')

@section('content')
<div class="space-y-8" x-data="{ 
    setupModal: {{ session('open_2fa_setup') ? 'true' : 'false' }}, 
    disableModal: false, 
    regenModal: false,
    copyFeedback: false,
    copySecret() {
        navigator.clipboard.writeText('{{ $secret ?? '' }}');
        this.copyFeedback = true;
        setTimeout(() => this.copyFeedback = false, 2500);
    }
}">

    {{-- Top Header Banner --}}
    <div class="bg-white border border-[#111111]/15 p-6 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 bg-[#111111] text-white flex items-center justify-center font-mono font-bold text-lg border border-[#111111] relative overflow-hidden flex-shrink-0">
                @if ($user->avatar && file_exists(public_path('storage/' . $user->avatar)))
                    <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                @else
                    <span>{{ $user->initials }}</span>
                @endif
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-bold text-[#111111]">{{ $user->name }}</h2>
                    @if($user->roles->count() > 0)
                        <span class="px-2 py-0.5 text-[10px] font-mono uppercase font-bold border border-[#111111]/20 bg-[#F5F1E8] text-[#111111]">
                            {{ $user->roles->first()->name }}
                        </span>
                    @endif
                </div>
                <div class="text-xs font-mono text-[#808080] mt-0.5">{{ $user->email }}</div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            @if($has2fa)
                <div class="flex items-center gap-2 px-3 py-1.5 bg-emerald-50 border border-emerald-300 text-emerald-900 text-xs font-mono">
                    <span class="w-2 h-2 rounded-full bg-emerald-600 animate-pulse"></span>
                    <span class="font-bold">2FA ACTIVE</span>
                    <span class="text-emerald-700 text-[10px]">(TOTP Protected)</span>
                </div>
            @else
                <div class="flex items-center gap-2 px-3 py-1.5 bg-amber-50 border border-amber-300 text-amber-900 text-xs font-mono">
                    <span class="w-2 h-2 rounded-full bg-amber-600"></span>
                    <span class="font-bold">2FA DISABLED</span>
                    <span class="text-amber-700 text-[10px]">(Vulnerable)</span>
                </div>
            @endif
        </div>
    </div>

    {{-- Flash Recovery Codes Display (Shown Once Upon Activation / Regeneration) --}}
    @if(session('show_recovery_codes'))
        <div class="bg-amber-50 border-2 border-amber-400 p-6 space-y-4">
            <div class="flex items-start justify-between">
                <div>
                    <div class="flex items-center gap-2 text-amber-900 font-bold text-sm">
                        <svg class="w-5 h-5 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>URGENT: Save Your 2FA Backup Recovery Codes</span>
                    </div>
                    <p class="text-xs font-mono text-amber-800 mt-1">
                        If you lose access to your authenticator app, these emergency one-time codes are the <strong>only way</strong> to recover access to your account. Each code may be used once.
                    </p>
                </div>
                <button onclick="window.print()" class="px-3 py-1 text-xs font-mono border border-amber-500 bg-white hover:bg-amber-100 text-amber-900">
                    Print Codes
                </button>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 bg-white p-4 border border-amber-300 font-mono text-xs">
                @foreach(session('show_recovery_codes') as $code)
                    <div class="p-2 bg-[#F8F8F6] border border-[#111111]/15 text-center font-bold tracking-wider select-all">
                        {{ $code }}
                    </div>
                @endforeach
            </div>

            <div class="text-[11px] font-mono text-amber-700">
                Store these in an encrypted password vault (e.g. 1Password, Bitwarden, Keepass) or print a physical copy.
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

        {{-- Left Column: Profile Info & Password (7 cols) --}}
        <div class="lg:col-span-7 space-y-8">

            {{-- 1. Edit Personal Profile --}}
            <div class="bg-white border border-[#111111]/15 p-6 space-y-6">
                <div class="border-b border-[#111111]/10 pb-4">
                    <h3 class="font-bold text-sm text-[#111111]">1. Personal Identity & Profile</h3>
                    <p class="text-xs font-mono text-[#808080] mt-0.5">Update your editorial name, avatar, and contact email.</p>
                </div>

                <form action="{{ route('admin.profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                    @csrf
                    @method('PUT')

                    <div class="flex items-center gap-6">
                        <div class="w-16 h-16 bg-[#111111] text-white flex items-center justify-center font-mono font-bold text-xl border border-[#111111] overflow-hidden flex-shrink-0">
                            @if ($user->avatar && file_exists(public_path('storage/' . $user->avatar)))
                                <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                            @else
                                <span>{{ $user->initials }}</span>
                            @endif
                        </div>
                        <div class="flex-1 space-y-1.5">
                            <label class="block text-xs font-mono uppercase tracking-wider text-[#111111] font-medium">Profile Avatar</label>
                            <input type="file" name="avatar" accept="image/png,image/jpeg,image/webp"
                                class="text-xs font-mono file:mr-3 file:py-1.5 file:px-3 file:border file:border-[#111111] file:text-xs file:font-mono file:bg-[#111111] file:text-white hover:file:bg-[#333333]">
                            <p class="text-[10px] text-[#808080] font-mono">PNG, JPG, or WebP up to 2MB. Square ratio recommended.</p>
                            @if($user->avatar)
                                <label class="inline-flex items-center gap-2 mt-1 cursor-pointer">
                                    <input type="checkbox" name="remove_avatar" value="1" class="rounded-none border-[#111111]/30">
                                    <span class="text-xs font-mono text-rose-700">Remove custom avatar (revert to initials)</span>
                                </label>
                            @endif
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="name" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1 font-medium">Display Name *</label>
                            <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
                                class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                        </div>

                        <div>
                            <label for="email" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1 font-medium">Email Address *</label>
                            <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required
                                class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                        </div>
                    </div>

                    {{-- Preferred Timezone Selection (PRD-ADDNEW 5.5) --}}
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label for="timezone" class="block text-xs font-mono uppercase tracking-wider text-[#111111] font-medium">
                                Preferred Timezone (Console & Timestamps)
                            </label>
                            <span class="text-[10px] font-mono text-emerald-700 bg-emerald-50 px-2 py-0.5 border border-emerald-200">
                                Current: {{ now()->setTimezone($user->timezone ?? 'UTC')->format('g:i A T') }}
                            </span>
                        </div>
                        <select id="timezone" name="timezone"
                            class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                            @if(isset($allTimezones))
                                @foreach($allTimezones as $region => $tzList)
                                    <optgroup label="{{ $region }}">
                                        @foreach($tzList as $tz)
                                            <option value="{{ $tz['id'] }}" {{ old('timezone', $user->timezone ?? 'UTC') === $tz['id'] ? 'selected' : '' }}>
                                                {{ $tz['label'] }} ({{ $tz['id'] }})
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            @else
                                <option value="UTC" selected>UTC</option>
                            @endif
                        </select>
                        <p class="text-[10px] font-mono text-[#808080] mt-1">
                            All admin console schedules, analytics dates, and login history will automatically render in this timezone.
                        </p>
                    </div>

                    <div>
                        <label for="bio" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1 font-medium">Bio / Author Statement</label>
                        <textarea id="bio" name="bio" rows="3"
                            class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]"
                            placeholder="Brief professional background, cybersecurity specialization, or editorial focus...">{{ old('bio', $user->bio) }}</textarea>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="btn-primary text-xs font-mono uppercase tracking-wider py-2.5 px-6">
                            Save Profile Changes →
                        </button>
                    </div>
                </form>
            </div>

            {{-- 2. Change Password --}}
            <div class="bg-white border border-[#111111]/15 p-6 space-y-6">
                <div class="border-b border-[#111111]/10 pb-4">
                    <h3 class="font-bold text-sm text-[#111111]">2. Account Security & Password</h3>
                    <p class="text-xs font-mono text-[#808080] mt-0.5">Ensure your password uses at least 8 characters with numbers and symbols.</p>
                </div>

                <form action="{{ route('admin.profile.password') }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label for="current_password" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1 font-medium">Current Password *</label>
                        <input type="password" id="current_password" name="current_password" required
                            class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]"
                            placeholder="••••••••••••">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="password" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1 font-medium">New Password *</label>
                            <input type="password" id="password" name="password" required minlength="8"
                                class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]"
                                placeholder="••••••••••••">
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1 font-medium">Confirm New Password *</label>
                            <input type="password" id="password_confirmation" name="password_confirmation" required minlength="8"
                                class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]"
                                placeholder="••••••••••••">
                        </div>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="btn-primary text-xs font-mono uppercase tracking-wider py-2.5 px-6">
                            Update Password →
                        </button>
                    </div>
                </form>
            </div>

        </div>

        {{-- Right Column: Two-Factor Authentication (2FA) & Logins (5 cols) --}}
        <div class="lg:col-span-5 space-y-8">

            {{-- 3. Two-Factor Authentication (2FA) Management --}}
            <div class="bg-white border border-[#111111]/15 p-6 space-y-6">
                <div class="flex items-center justify-between border-b border-[#111111]/10 pb-4">
                    <div>
                        <h3 class="font-bold text-sm text-[#111111]">3. Two-Factor Authentication</h3>
                        <p class="text-xs font-mono text-[#808080] mt-0.5">Time-based One-Time Password (TOTP)</p>
                    </div>
                    @if($has2fa)
                        <span class="w-3 h-3 bg-emerald-500 rounded-full inline-block" title="2FA Active"></span>
                    @else
                        <span class="w-3 h-3 bg-amber-400 rounded-full inline-block" title="2FA Inactive"></span>
                    @endif
                </div>

                @if(!$has2fa)
                    {{-- 2FA is OFF: Turn ON Section --}}
                    <div class="space-y-4">
                        <p class="text-xs font-mono text-[#555555] leading-relaxed">
                            Two-Factor Authentication (2FA) adds a cryptographic second layer of security by requiring a 6-digit code from Google Authenticator, Microsoft Authenticator, or 1Password when signing in.
                        </p>

                        <div class="p-4 bg-[#F8F8F6] border border-[#111111]/15 space-y-2">
                            <div class="text-xs font-bold font-mono text-[#111111] uppercase tracking-wider flex items-center gap-2">
                                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                <span>Status: Not Protected</span>
                            </div>
                            <p class="text-[11px] font-mono text-[#808080]">
                                Your account is currently secured only with a password. Enable 2FA to prevent unauthorized intrusions.
                            </p>
                        </div>

                        <button @click="setupModal = true" type="button" class="btn-primary w-full text-xs font-mono uppercase tracking-wider py-3 flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            <span>Turn ON Two-Factor (2FA) →</span>
                        </button>
                    </div>

                @else
                    {{-- 2FA is ON: Active Management & Turn OFF Section --}}
                    <div class="space-y-5">
                        <div class="p-4 bg-emerald-50 border border-emerald-300 space-y-2">
                            <div class="text-xs font-bold font-mono text-emerald-900 uppercase tracking-wider flex items-center gap-2">
                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                <span>Status: Active & Enforced</span>
                            </div>
                            <p class="text-[11px] font-mono text-emerald-800">
                                Enrolled on {{ $user->two_factor_confirmed_at ? $user->two_factor_confirmed_at->format('M d, Y H:i T') : 'Recently' }}. Every console login requires TOTP confirmation.
                            </p>
                        </div>

                        <div class="border border-[#111111]/15 p-4 bg-[#F8F8F6] space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-mono font-bold text-[#111111]">Emergency Recovery Codes</span>
                                <span class="text-[10px] font-mono px-2 py-0.5 bg-white border border-[#111111]/15 text-[#111111]">
                                    {{ count($recoveryCodes) }} Available
                                </span>
                            </div>
                            <p class="text-[11px] font-mono text-[#808080]">
                                If you lose your phone or authenticator app, unused recovery codes allow emergency single-use sign in.
                            </p>
                            <button @click="regenModal = true" type="button" class="w-full text-xs font-mono py-2 border border-[#111111] bg-white hover:bg-[#F5F1E8] text-[#111111] transition-colors">
                                Regenerate Recovery Codes
                            </button>
                        </div>

                        <div class="pt-2 border-t border-[#111111]/10">
                            <button @click="disableModal = true" type="button" class="w-full text-xs font-mono py-2.5 border border-rose-300 bg-rose-50 hover:bg-rose-100 text-rose-800 transition-colors flex items-center justify-center gap-2">
                                <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                <span>Turn OFF Two-Factor (2FA)</span>
                            </button>
                        </div>
                    </div>
                @endif
            </div>

            {{-- 4. Recent Login History Audit --}}
            <div class="bg-white border border-[#111111]/15 p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-[#111111]/10 pb-3">
                    <h3 class="font-bold text-sm text-[#111111]">4. Recent Login Activity</h3>
                    <span class="text-[10px] font-mono text-[#808080]">Last 10 Logins</span>
                </div>

                <div class="divide-y divide-[#111111]/10 font-mono text-xs">
                    @forelse($loginHistories as $login)
                        <div class="py-2.5 flex items-start justify-between gap-3">
                            <div class="space-y-0.5">
                                <div class="font-bold text-[#111111] flex items-center gap-2">
                                    <span>{{ $login->device ?? 'Desktop' }}</span>
                                    <span class="text-[10px] text-[#808080] font-normal">• {{ $login->browser ?? 'Browser' }}</span>
                                </div>
                                <div class="text-[10px] text-[#808080]">
                                    IP: {{ $login->ip_address }} {{ $login->location ? "({$login->location})" : '' }}
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="text-[10px] text-[#808080] block">{{ $login->created_at->diffForHumans() }}</span>
                                <span class="inline-block mt-0.5 px-1.5 py-0.2 text-[9px] uppercase font-bold {{ str_contains($login->status ?? '', 'success') ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                    {{ $login->status ?? 'OK' }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="py-4 text-center text-[#808080] text-xs font-mono">
                            No recent login records found.
                        </div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>

    {{-- MODAL: Setup / Turn ON 2FA --}}
    @if(!$has2fa && $secret)
    <div x-show="setupModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
        <div @click.away="setupModal = false" class="bg-white border border-[#111111] w-full max-w-lg p-6 sm:p-8 space-y-6 shadow-2xl relative">
            <div class="flex items-start justify-between border-b border-[#111111]/10 pb-4">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="w-3 h-3 bg-[#B38B6D]"></span>
                        <span class="font-mono text-xs uppercase tracking-widest text-[#808080]">Security Configuration</span>
                    </div>
                    <h2 class="text-lg font-bold text-[#111111]">Enroll Authenticator (TOTP)</h2>
                </div>
                <button @click="setupModal = false" class="text-[#808080] hover:text-[#111111] font-mono text-lg font-bold">&times;</button>
            </div>

            <div class="space-y-6">
                {{-- Step 1: Scan QR Code --}}
                <div class="space-y-2">
                    <span class="font-mono text-xs font-bold uppercase tracking-wider text-[#111111]">Step 1: Scan QR Code</span>
                    <p class="text-xs font-mono text-[#808080]">Open Google Authenticator, Microsoft Authenticator, or 1Password and scan this image:</p>
                    <div class="flex justify-center p-4 bg-white border border-[#111111]/20">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&margin=0&data={{ urlencode($qrCodeUrl) }}" 
                             alt="2FA QR Code" 
                             class="w-44 h-44 object-contain border border-[#111111]/10">
                    </div>
                </div>

                {{-- Step 2: Manual Key --}}
                <div class="space-y-1.5">
                    <span class="font-mono text-xs font-bold uppercase tracking-wider text-[#111111]">Step 2: Or Enter Secret Key Manually</span>
                    <div class="flex items-center gap-2">
                        <input type="text" readonly value="{{ chunk_split($secret, 4, ' ') }}" 
                               class="flex-1 px-3 py-2 text-xs font-mono bg-[#F8F8F6] border border-[#111111]/20 font-bold select-all">
                        <button type="button" @click="copySecret()" class="px-3 py-2 text-xs font-mono bg-[#111111] text-white hover:bg-[#333333] transition-colors">
                            <span x-text="copyFeedback ? 'Copied!' : 'Copy Key'"></span>
                        </button>
                    </div>
                </div>

                {{-- Step 3: Enter 6-digit Code to Confirm --}}
                <form action="{{ route('admin.profile.2fa.confirm') }}" method="POST" class="space-y-4 pt-2 border-t border-[#111111]/10">
                    @csrf
                    <input type="hidden" name="secret" value="{{ $secret }}">

                    <div>
                        <label for="two_factor_code" class="block text-xs font-mono font-bold uppercase tracking-wider text-[#111111] mb-1">
                            Step 3: Enter 6-Digit Verification Token *
                        </label>
                        <input type="text" id="two_factor_code" name="code" required maxlength="6" pattern="[0-9]{6}" autofocus
                            class="w-full px-4 py-3 text-center tracking-[0.4em] text-xl font-mono bg-white border-2 border-[#111111] focus:outline-none"
                            placeholder="000000">
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" @click="setupModal = false" class="px-4 py-2 text-xs font-mono text-[#808080] hover:text-[#111111]">
                            Cancel
                        </button>
                        <button type="submit" class="btn-primary text-xs font-mono uppercase tracking-wider py-2.5 px-6">
                            Verify & Turn ON 2FA →
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- MODAL: Turn OFF / Disable 2FA --}}
    @if($has2fa)
    <div x-show="disableModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
        <div @click.away="disableModal = false" class="bg-white border border-[#111111] w-full max-w-md p-6 space-y-5 shadow-2xl relative">
            <div class="flex items-start justify-between border-b border-[#111111]/10 pb-3">
                <div>
                    <h3 class="font-bold text-base text-rose-700">Turn OFF Two-Factor Authentication</h3>
                    <p class="text-xs font-mono text-[#808080] mt-0.5">Enter your account password to confirm deactivation.</p>
                </div>
                <button @click="disableModal = false" class="text-[#808080] hover:text-[#111111] font-mono text-lg font-bold">&times;</button>
            </div>

            <form action="{{ route('admin.profile.2fa.disable') }}" method="POST" class="space-y-4">
                @csrf
                <div class="p-3 bg-rose-50 border border-rose-200 text-xs font-mono text-rose-800">
                    Warning: Turning off 2FA lowers your account security. Anyone with your password will be able to log in without a second factor.
                </div>

                <div>
                    <label for="disable_password" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1 font-medium">Your Current Password *</label>
                    <input type="password" id="disable_password" name="disable_password" required
                        class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-rose-600"
                        placeholder="••••••••••••">
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" @click="disableModal = false" class="px-4 py-2 text-xs font-mono text-[#808080] hover:text-[#111111]">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2.5 text-xs font-mono uppercase tracking-wider bg-rose-600 hover:bg-rose-700 text-white font-bold transition-colors">
                        Confirm & Turn OFF 2FA
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL: Regenerate Recovery Codes --}}
    <div x-show="regenModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
        <div @click.away="regenModal = false" class="bg-white border border-[#111111] w-full max-w-md p-6 space-y-5 shadow-2xl relative">
            <div class="flex items-start justify-between border-b border-[#111111]/10 pb-3">
                <div>
                    <h3 class="font-bold text-base text-[#111111]">Regenerate 2FA Recovery Codes</h3>
                    <p class="text-xs font-mono text-[#808080] mt-0.5">Replaces all previous emergency codes.</p>
                </div>
                <button @click="regenModal = false" class="text-[#808080] hover:text-[#111111] font-mono text-lg font-bold">&times;</button>
            </div>

            <form action="{{ route('admin.profile.2fa.recovery_codes') }}" method="POST" class="space-y-4">
                @csrf
                <div class="p-3 bg-amber-50 border border-amber-200 text-xs font-mono text-amber-800">
                    Existing recovery codes will immediately expire and cannot be used again once new codes are generated.
                </div>

                <div>
                    <label for="regen_password" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1 font-medium">Your Current Password *</label>
                    <input type="password" id="regen_password" name="regen_password" required
                        class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]"
                        placeholder="••••••••••••">
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" @click="regenModal = false" class="px-4 py-2 text-xs font-mono text-[#808080] hover:text-[#111111]">
                        Cancel
                    </button>
                    <button type="submit" class="btn-primary text-xs font-mono uppercase tracking-wider py-2.5 px-5">
                        Generate New Codes →
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

</div>
@endsection
