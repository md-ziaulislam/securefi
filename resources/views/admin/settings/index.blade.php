@extends('layouts.admin')

@section('title', 'General Settings & Branding')
@section('header_title', 'Global Platform Parameters & Identity')

@section('content')
<form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
    @csrf

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

        {{-- Left: Brand Identity & Logos (6 cols) --}}
        <div class="lg:col-span-6 space-y-6">

            {{-- Brand Identity Card --}}
            <div class="bg-white border border-[#111111]/15 p-6 space-y-4">
                <div class="border-b border-[#111111]/10 pb-3">
                    <h3 class="font-bold text-sm text-[#111111]">1. Brand Identity & Copy</h3>
                    <p class="text-xs font-mono text-[#808080]">Core site naming and platform definition.</p>
                </div>

                <div>
                    <label for="site_name" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1 font-medium">Site Name *</label>
                    <input type="text" id="site_name" name="site_name" value="{{ old('site_name', $settings['site_name'] ?? 'SecuroFi.Tech') }}" required
                        class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                </div>

                <div>
                    <label for="site_tagline" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1">Site Tagline</label>
                    <input type="text" id="site_tagline" name="site_tagline" value="{{ old('site_tagline', $settings['site_tagline'] ?? '') }}"
                        class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                </div>

                <div>
                    <label for="site_description" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1">Meta / Footer Description</label>
                    <textarea id="site_description" name="site_description" rows="3"
                        class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111] leading-relaxed">{{ old('site_description', $settings['site_description'] ?? '') }}</textarea>
                </div>
            </div>

            {{-- Logos & Favicon Upload Card --}}
            <div class="bg-white border border-[#111111]/15 p-6 space-y-5">
                <div class="border-b border-[#111111]/10 pb-3">
                    <h3 class="font-bold text-sm text-[#111111]">2. Visual Assets & Favicon</h3>
                    <p class="text-xs font-mono text-[#808080]">Upload PNG/SVG logos or configure external CDN paths.</p>
                </div>

                {{-- Light Logo --}}
                <div class="p-3 bg-[#F8F8F6] border border-[#111111]/15 space-y-2">
                    <label class="block text-xs font-mono uppercase tracking-wider text-[#111111] font-bold">Logo (Light Background Mode)</label>
                    @if (!empty($settings['site_logo_light']))
                        <div class="p-2 bg-white border border-[#111111]/10 inline-block mb-1">
                            <img src="{{ $settings['site_logo_light'] }}" alt="Current Light Logo" class="h-8 max-w-xs object-contain">
                        </div>
                    @endif
                    <input type="file" name="site_logo_light_file" accept="image/*"
                        class="w-full text-xs font-mono text-[#808080] file:mr-3 file:py-1 file:px-3 file:border-0 file:text-xs file:font-mono file:bg-primary file:text-secondary hover:file:opacity-90">
                    <input type="url" name="site_logo_light" value="{{ old('site_logo_light', $settings['site_logo_light'] ?? '') }}"
                        class="w-full px-3 py-1.5 text-xs font-mono bg-white border border-[#111111]/30" placeholder="or paste direct URL...">
                </div>

                {{-- Dark Logo --}}
                <div class="p-3 bg-[#F8F8F6] border border-[#111111]/15 space-y-2">
                    <label class="block text-xs font-mono uppercase tracking-wider text-[#111111] font-bold">Logo (Dark Background Variant)</label>
                    @if (!empty($settings['site_logo_dark']))
                        <div class="p-2 bg-[#111111] border border-white/20 inline-block mb-1">
                            <img src="{{ $settings['site_logo_dark'] }}" alt="Current Dark Logo" class="h-8 max-w-xs object-contain">
                        </div>
                    @endif
                    <input type="file" name="site_logo_dark_file" accept="image/*"
                        class="w-full text-xs font-mono text-[#808080] file:mr-3 file:py-1 file:px-3 file:border-0 file:text-xs file:font-mono file:bg-primary file:text-secondary hover:file:opacity-90">
                    <input type="url" name="site_logo_dark" value="{{ old('site_logo_dark', $settings['site_logo_dark'] ?? '') }}"
                        class="w-full px-3 py-1.5 text-xs font-mono bg-white border border-[#111111]/30" placeholder="or paste direct URL...">
                </div>

                {{-- Favicon --}}
                <div class="p-3 bg-[#F8F8F6] border border-[#111111]/15 space-y-2">
                    <label class="block text-xs font-mono uppercase tracking-wider text-[#111111] font-bold">Site Favicon (ICO, PNG, SVG)</label>
                    @if (!empty($settings['site_favicon']))
                        <div class="p-2 bg-white border border-[#111111]/10 inline-block mb-1">
                            <img src="{{ $settings['site_favicon'] }}" alt="Current Favicon" class="w-6 h-6 object-contain">
                        </div>
                    @endif
                    <input type="file" name="site_favicon_file" accept=".ico,.png,.svg"
                        class="w-full text-xs font-mono text-[#808080] file:mr-3 file:py-1 file:px-3 file:border-0 file:text-xs file:font-mono file:bg-primary file:text-secondary hover:file:opacity-90">
                    <input type="url" name="site_favicon" value="{{ old('site_favicon', $settings['site_favicon'] ?? '') }}"
                        class="w-full px-3 py-1.5 text-xs font-mono bg-white border border-[#111111]/30" placeholder="or paste direct URL...">
                </div>
            </div>

            {{-- Timezone & Regional Localization Card (PRD-ADDNEW 5.5) --}}
            <div class="bg-white border border-[#111111]/15 p-6 space-y-5">
                <div class="border-b border-[#111111]/10 pb-3 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-sm text-[#111111]">3. Timezone & Regional Localization</h3>
                        <p class="text-xs font-mono text-[#808080]">Configure platform default time and automatic visitor detection.</p>
                    </div>
                    <span class="text-[10px] font-mono text-emerald-700 bg-emerald-50 px-2 py-0.5 border border-emerald-200">
                        Active: {{ $settings['site_timezone'] ?? 'UTC' }}
                    </span>
                </div>

                {{-- Default Platform Timezone --}}
                <div>
                    <label for="site_timezone" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1 font-medium">
                        Platform Default Timezone *
                    </label>
                    <select id="site_timezone" name="site_timezone"
                        class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                        @if(isset($allTimezones))
                            @foreach($allTimezones as $region => $tzList)
                                <optgroup label="{{ $region }}">
                                    @foreach($tzList as $tz)
                                        <option value="{{ $tz['id'] }}" {{ old('site_timezone', $settings['site_timezone'] ?? 'UTC') === $tz['id'] ? 'selected' : '' }}>
                                            {{ $tz['label'] }} ({{ $tz['id'] }})
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        @else
                            <option value="UTC" selected>UTC</option>
                        @endif
                    </select>
                    <p class="text-xs font-mono text-[#808080] mt-1">Fallback timezone used when visitor detection is disabled or undetermined.</p>
                </div>

                {{-- Auto-Detect Visitor Timezone Toggle --}}
                <div class="p-3 bg-[#F8F8F6] border border-[#111111]/15">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="auto_detect_timezone" value="1"
                            {{ ($settings['auto_detect_timezone'] ?? '1') === '1' ? 'checked' : '' }}
                            class="w-4 h-4 rounded-none border-[#111111] text-[#111111] focus:ring-0">
                        <div>
                            <span class="text-xs font-mono font-bold text-[#111111] uppercase tracking-wider">Auto-Detect Visitor Timezone</span>
                            <p class="text-xs font-mono text-[#808080]">Converts article publish dates, comments & relative times into visitor's browser timezone.</p>
                        </div>
                    </label>
                </div>

                {{-- Date & Time Formats --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                    <div>
                        <label for="date_format" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1 font-medium">Date Format</label>
                        <select id="date_format" name="date_format"
                            class="w-full px-3 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                            <option value="M d, Y" {{ ($settings['date_format'] ?? 'M d, Y') === 'M d, Y' ? 'selected' : '' }}>Sep 11, 2026 (M d, Y)</option>
                            <option value="F d, Y" {{ ($settings['date_format'] ?? '') === 'F d, Y' ? 'selected' : '' }}>September 11, 2026 (F d, Y)</option>
                            <option value="Y-m-d" {{ ($settings['date_format'] ?? '') === 'Y-m-d' ? 'selected' : '' }}>2026-09-11 (ISO Y-m-d)</option>
                            <option value="d/m/Y" {{ ($settings['date_format'] ?? '') === 'd/m/Y' ? 'selected' : '' }}>11/09/2026 (d/m/Y)</option>
                        </select>
                    </div>

                    <div>
                        <label for="time_format" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1 font-medium">Time Format</label>
                        <select id="time_format" name="time_format"
                            class="w-full px-3 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                            <option value="g:i A" {{ ($settings['time_format'] ?? 'g:i A') === 'g:i A' ? 'selected' : '' }}>1:30 PM (12-Hour)</option>
                            <option value="H:i" {{ ($settings['time_format'] ?? '') === 'H:i' ? 'selected' : '' }}>13:30 (24-Hour)</option>
                        </select>
                    </div>
                </div>
            </div>

        </div>

        {{-- Right: Social Links, Contact & Cookie Banner (6 cols) --}}
        <div class="lg:col-span-6 space-y-6">

            {{-- Social Media Links --}}
            <div class="bg-white border border-[#111111]/15 p-6 space-y-4">
                <div class="border-b border-[#111111]/10 pb-3">
                    <h3 class="font-bold text-sm text-[#111111]">3. Social Media Presence</h3>
                    <p class="text-xs font-mono text-[#808080]">External profile URLs shown across headers & footers.</p>
                </div>

                <div>
                    <label for="social_twitter" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1">X / Twitter URL</label>
                    <input type="text" id="social_twitter" name="social_twitter" value="{{ old('social_twitter', $settings['social_twitter'] ?? '') }}"
                        class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]"
                        placeholder="https://x.com/securofi">
                </div>

                <div>
                    <label for="social_github" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1">GitHub URL</label>
                    <input type="text" id="social_github" name="social_github" value="{{ old('social_github', $settings['social_github'] ?? '') }}"
                        class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]"
                        placeholder="https://github.com/securofi">
                </div>

                <div>
                    <label for="social_linkedin" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1">LinkedIn URL</label>
                    <input type="text" id="social_linkedin" name="social_linkedin" value="{{ old('social_linkedin', $settings['social_linkedin'] ?? '') }}"
                        class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]"
                        placeholder="https://linkedin.com/company/securofi">
                </div>

                <div>
                    <label for="social_youtube" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1">YouTube Channel URL</label>
                    <input type="text" id="social_youtube" name="social_youtube" value="{{ old('social_youtube', $settings['social_youtube'] ?? '') }}"
                        class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]"
                        placeholder="https://youtube.com/@securofi">
                </div>
            </div>

            {{-- Developer Profile Settings (for dev-info page) --}}
            <div class="bg-white border border-[#111111]/15 p-6 space-y-5">
                <div class="border-b border-[#111111]/10 pb-3">
                    <h3 class="font-bold text-sm text-[#111111]">4. Developer Profile (dev-info page)</h3>
                    <p class="text-xs font-mono text-[#808080]">Configure the developer portrait, bio, skills, and links displayed on the dev-info page.</p>
                </div>

                {{-- Developer Photo Upload --}}
                <div class="p-4 bg-[#F8F8F6] border border-[#111111]/15 space-y-3">
                    <label class="block text-xs font-mono uppercase tracking-wider text-[#111111] font-bold">Developer Portrait / Photo</label>
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 bg-[#111111] text-white flex items-center justify-center font-mono font-bold text-xl border border-[#111111] overflow-hidden flex-shrink-0">
                            @if (!empty($settings['dev_photo']))
                                <img src="{{ $settings['dev_photo'] }}" alt="Developer Photo" class="w-full h-full object-cover">
                            @else
                                <span>{{ strtoupper(substr($settings['dev_name'] ?? 'ZI', 0, 2)) }}</span>
                            @endif
                        </div>
                        <div class="flex-1 space-y-1.5">
                            <input type="file" name="dev_photo_file" accept="image/png,image/jpeg,image/webp"
                                class="text-xs font-mono file:mr-3 file:py-1 file:px-3 file:border file:border-[#111111] file:text-xs file:font-mono file:bg-[#111111] file:text-white hover:file:bg-[#333333]">
                            <p class="text-[10px] text-[#808080] font-mono">Square PNG, JPG, or WebP up to 2MB recommended.</p>
                            @if(!empty($settings['dev_photo']))
                                <label class="inline-flex items-center gap-1.5 cursor-pointer mt-1">
                                    <input type="checkbox" name="remove_dev_photo" value="1" class="rounded-none border-[#111111]/30">
                                    <span class="text-xs font-mono text-rose-700">Remove developer photo (revert to monogram)</span>
                                </label>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="dev_name" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1">Architect Name</label>
                        <input type="text" id="dev_name" name="dev_name" value="{{ old('dev_name', $settings['dev_name'] ?? 'Ziaul Islam') }}"
                            class="w-full px-3 py-1.5 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                    </div>

                    <div>
                        <label for="dev_title" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1">Role Title</label>
                        <input type="text" id="dev_title" name="dev_title" value="{{ old('dev_title', $settings['dev_title'] ?? 'Lead Full-Stack Architect') }}"
                            class="w-full px-3 py-1.5 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                    </div>
                </div>

                <div>
                    <label for="dev_bio" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1">Developer Bio / Statement</label>
                    <textarea id="dev_bio" name="dev_bio" rows="3"
                        class="w-full px-3 py-1.5 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111] leading-relaxed">{{ old('dev_bio', $settings['dev_bio'] ?? 'Full-stack software architect specializing in high-performance Laravel architectures, robust APIs, scalable databases, and security-first web applications.') }}</textarea>
                </div>

                <div>
                    <label for="dev_skills" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1">Core Competencies & Skills (comma-separated)</label>
                    <input type="text" id="dev_skills" name="dev_skills" value="{{ old('dev_skills', $settings['dev_skills'] ?? 'Laravel, PHP, MySQL, Tailwind CSS, Web Security, RESTful APIs, System Architecture, Performance Tuning') }}"
                        class="w-full px-3 py-1.5 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]"
                        placeholder="e.g. Laravel, PHP, MySQL, Vue.js, System Security">
                </div>

                <div class="grid grid-cols-2 gap-3 pt-1">
                    <div>
                        <label for="dev_email" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1">Developer Email</label>
                        <input type="email" id="dev_email" name="dev_email" value="{{ old('dev_email', $settings['dev_email'] ?? '') }}"
                            class="w-full px-3 py-1.5 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]"
                            placeholder="developer@securofi.tech">
                    </div>

                    <div>
                        <label for="dev_github" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1">GitHub Profile URL</label>
                        <input type="url" id="dev_github" name="dev_github" value="{{ old('dev_github', $settings['dev_github'] ?? '') }}"
                            class="w-full px-3 py-1.5 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]"
                            placeholder="https://github.com/username">
                    </div>

                    <div>
                        <label for="dev_linkedin" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1">LinkedIn Profile URL</label>
                        <input type="url" id="dev_linkedin" name="dev_linkedin" value="{{ old('dev_linkedin', $settings['dev_linkedin'] ?? '') }}"
                            class="w-full px-3 py-1.5 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]"
                            placeholder="https://linkedin.com/in/username">
                    </div>

                    <div>
                        <label for="dev_twitter" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1">Twitter / X Profile URL</label>
                        <input type="url" id="dev_twitter" name="dev_twitter" value="{{ old('dev_twitter', $settings['dev_twitter'] ?? '') }}"
                            class="w-full px-3 py-1.5 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]"
                            placeholder="https://x.com/username">
                    </div>
                </div>
            </div>

            {{-- Cookie Consent (GDPR/CCPA) --}}
            <div class="bg-white border border-[#111111]/15 p-6 space-y-4">
                <div class="border-b border-[#111111]/10 pb-3">
                    <h3 class="font-bold text-sm text-[#111111]">5. Cookie Consent Banner (GDPR/CCPA)</h3>
                    <p class="text-xs font-mono text-[#808080]">Privacy banner displayed to first-time visitors.</p>
                </div>

                <div>
                    <label class="flex items-center gap-2 cursor-pointer mb-3">
                        <input type="checkbox" name="cookie_consent_enabled" value="1" {{ ($settings['cookie_consent_enabled'] ?? '1') === '1' ? 'checked' : '' }}
                            class="w-4 h-4 rounded-none border-[#111111] text-[#111111]">
                        <span class="text-xs font-mono font-bold text-[#111111]">Enable Cookie Consent Banner</span>
                    </label>

                    <textarea id="cookie_consent_text" name="cookie_consent_text" rows="2"
                        class="w-full px-3 py-1.5 text-xs font-mono bg-[#F8F8F6] border border-[#111111]/30 focus:outline-none focus:border-[#111111]">{{ old('cookie_consent_text', $settings['cookie_consent_text'] ?? 'We use privacy-conscious analytics and cookies to deliver high-performance browsing and relevant recommendations.') }}</textarea>
                </div>
            </div>

            {{-- CAPTCHA / Bot Protection --}}
            <div class="bg-white border border-[#111111]/15 p-6 space-y-4" id="captcha-settings-card">
                <div class="border-b border-[#111111]/10 pb-3">
                    <h3 class="font-bold text-sm text-[#111111]">6. CAPTCHA & Bot Protection</h3>
                    <p class="text-xs font-mono text-[#808080]">reCAPTCHA v2/v3 or Cloudflare Turnstile to block spam bots on public forms.</p>
                </div>

                {{-- Global Enable Toggle --}}
                <div class="p-3 bg-[#F8F8F6] border border-[#111111]/10">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" id="captcha_enabled" name="captcha_enabled" value="1"
                            {{ ($settings['captcha_enabled'] ?? '0') === '1' ? 'checked' : '' }}
                            class="w-4 h-4 rounded-none border-[#111111] text-[#111111]"
                            onchange="document.getElementById('captcha-config-panel').style.display = this.checked ? 'block' : 'none'">
                        <span class="text-xs font-mono font-bold text-[#111111]">Enable CAPTCHA Protection (Global)</span>
                    </label>
                    <p class="text-xs font-mono text-[#808080] mt-1 ml-6">When disabled, no CAPTCHA widget appears on any form.</p>
                </div>

                <div id="captcha-config-panel" style="{{ ($settings['captcha_enabled'] ?? '0') === '1' ? '' : 'display:none' }}" class="space-y-4">

                    {{-- Provider Select --}}
                    <div>
                        <label for="captcha_provider" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1 font-medium">Provider</label>
                        <select id="captcha_provider" name="captcha_provider"
                            class="w-full px-3 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]"
                            onchange="toggleV3Threshold(this.value)">
                            <option value="recaptcha_v2" {{ ($settings['captcha_provider'] ?? 'recaptcha_v2') === 'recaptcha_v2' ? 'selected' : '' }}>Google reCAPTCHA v2 (Checkbox — "I'm not a robot")</option>
                            <option value="recaptcha_v3" {{ ($settings['captcha_provider'] ?? '') === 'recaptcha_v3' ? 'selected' : '' }}>Google reCAPTCHA v3 (Invisible / Score-based)</option>
                            <option value="turnstile"    {{ ($settings['captcha_provider'] ?? '') === 'turnstile' ? 'selected' : '' }}>Cloudflare Turnstile (Privacy-first CAPTCHA)</option>
                        </select>
                    </div>

                    {{-- Site Key --}}
                    <div>
                        <label for="captcha_site_key" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1 font-medium">Site Key (Public)</label>
                        <input type="text" id="captcha_site_key" name="captcha_site_key"
                            value="{{ old('captcha_site_key', $settings['captcha_site_key'] ?? '') }}"
                            placeholder="Paste your public site key here..."
                            class="w-full px-3 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                        <p class="text-xs font-mono text-[#808080] mt-1">Embedded in frontend HTML — visible to users.</p>
                    </div>

                    {{-- Secret Key --}}
                    <div>
                        <label for="captcha_secret_key" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1 font-medium">Secret Key (Server-side)</label>
                        <input type="password" id="captcha_secret_key" name="captcha_secret_key"
                            value="{{ old('captcha_secret_key', $settings['captcha_secret_key'] ?? '') }}"
                            placeholder="Leave blank to keep existing secret key..."
                            autocomplete="new-password"
                            class="w-full px-3 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                        <p class="text-xs font-mono text-[#808080] mt-1">Never exposed to users. Used for server-side token verification.</p>
                    </div>

                    {{-- reCAPTCHA v3 Threshold --}}
                    <div id="v3-threshold-row" style="{{ ($settings['captcha_provider'] ?? 'recaptcha_v2') === 'recaptcha_v3' ? '' : 'display:none' }}">
                        <label for="captcha_v3_threshold" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1 font-medium">
                            reCAPTCHA v3 Score Threshold <span class="text-[#808080] normal-case">(0.0 – 1.0, default 0.5)</span>
                        </label>
                        <input type="number" id="captcha_v3_threshold" name="captcha_v3_threshold" step="0.1" min="0" max="1"
                            value="{{ old('captcha_v3_threshold', $settings['captcha_v3_threshold'] ?? '0.5') }}"
                            class="w-full px-3 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                        <p class="text-xs font-mono text-[#808080] mt-1">Scores below this value are treated as bots. 1.0 = very strict, 0.3 = lenient.</p>
                    </div>

                    {{-- Per-Form Toggles --}}
                    <div class="space-y-2 pt-2 border-t border-[#111111]/10">
                        <p class="text-xs font-mono font-bold text-[#111111] uppercase tracking-wider">Enable CAPTCHA On:</p>

                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="captcha_contact_enabled" value="1"
                                {{ ($settings['captcha_contact_enabled'] ?? '1') === '1' ? 'checked' : '' }}
                                class="w-4 h-4 rounded-none border-[#111111] text-[#111111]">
                            <span class="text-xs font-mono text-[#111111]">Contact Form <code class="bg-[#F8F8F6] px-1">/contact</code></span>
                        </label>

                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="captcha_newsletter_enabled" value="1"
                                {{ ($settings['captcha_newsletter_enabled'] ?? '1') === '1' ? 'checked' : '' }}
                                class="w-4 h-4 rounded-none border-[#111111] text-[#111111]">
                            <span class="text-xs font-mono text-[#111111]">Newsletter Subscription Form <code class="bg-[#F8F8F6] px-1">/newsletter</code></span>
                        </label>

                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="captcha_comment_enabled" value="1"
                                {{ ($settings['captcha_comment_enabled'] ?? '1') === '1' ? 'checked' : '' }}
                                class="w-4 h-4 rounded-none border-[#111111] text-[#111111]">
                            <span class="text-xs font-mono text-[#111111]">Article Comment Form</span>
                        </label>
                    </div>

                    {{-- Help Links --}}
                    <div class="text-xs font-mono text-[#808080] space-y-1 p-3 bg-[#F8F8F6] border border-[#111111]/10">
                        <p class="font-bold text-[#111111]">Quick Setup Links:</p>
                        <p>• reCAPTCHA: <a href="https://www.google.com/recaptcha/admin" target="_blank" class="underline hover:text-[#111111]">google.com/recaptcha/admin</a></p>
                        <p>• Turnstile: <a href="https://dash.cloudflare.com/?to=/:account/turnstile" target="_blank" class="underline hover:text-[#111111]">dash.cloudflare.com → Turnstile</a></p>
                    </div>
                </div>
            </div>

            {{-- Submit Bar --}}
            <div class="p-4 bg-white border border-[#111111]/15">
                <button type="submit" class="btn-primary w-full text-xs font-mono uppercase tracking-wider py-3.5">
                    Save Global Parameters & Branding →
                </button>
            </div>

            @push('scripts')
            <script>
                function toggleV3Threshold(val) {
                    var row = document.getElementById('v3-threshold-row');
                    if (row) row.style.display = val === 'recaptcha_v3' ? 'block' : 'none';
                }
            </script>
            @endpush

        </div>

    </div>
</form>
@endsection
