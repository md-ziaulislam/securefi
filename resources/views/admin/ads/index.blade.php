@extends('layouts.admin')

@section('title', 'Ads Management & Monetization')
@section('header_title', 'Ad Slots & Sponsorship Management')

@section('content')
<div class="space-y-8">

    {{-- Metrics & KPI Analytics Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
        <div class="bg-white border border-[#111111]/15 p-4 flex flex-col justify-between">
            <span class="text-[10px] font-mono text-[#808080] uppercase tracking-wider">Total Inventory</span>
            <div class="text-2xl font-bold font-mono text-[#111111] my-1">{{ $totalSlots }}</div>
            <span class="text-[10px] font-mono text-[#808080]">Configured ad slots</span>
        </div>

        <div class="bg-white border border-[#111111]/15 p-4 flex flex-col justify-between">
            <span class="text-[10px] font-mono text-[#808080] uppercase tracking-wider">Live & Active</span>
            <div class="text-2xl font-bold font-mono text-emerald-600 my-1">{{ $activeSlots }}</div>
            <span class="text-[10px] font-mono text-emerald-700/80">Currently serving</span>
        </div>

        <div class="bg-white border border-[#111111]/15 p-4 flex flex-col justify-between">
            <span class="text-[10px] font-mono text-[#808080] uppercase tracking-wider">Total Impressions</span>
            <div class="text-2xl font-bold font-mono text-[#111111] my-1">{{ number_format($totalImpressions) }}</div>
            <span class="text-[10px] font-mono text-[#808080]">Ad views recorded</span>
        </div>

        <div class="bg-white border border-[#111111]/15 p-4 flex flex-col justify-between">
            <span class="text-[10px] font-mono text-[#808080] uppercase tracking-wider">Total Clicks</span>
            <div class="text-2xl font-bold font-mono text-primary my-1">{{ number_format($totalClicks) }}</div>
            <span class="text-[10px] font-mono text-[#808080]">Tracked ad clicks</span>
        </div>

        <div class="bg-white border border-[#111111]/15 p-4 flex flex-col justify-between sm:col-span-2 md:col-span-1">
            <span class="text-[10px] font-mono text-[#808080] uppercase tracking-wider">Average CTR</span>
            <div class="text-2xl font-bold font-mono text-[#111111] my-1">{{ $avgCtr }}%</div>
            <span class="text-[10px] font-mono text-[#808080]">Click-through rate</span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

        {{-- Left: Create or Edit Ad Slot Form (5 cols) --}}
        <div class="lg:col-span-5 bg-white border border-[#111111]/15 p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-[#111111]/10 pb-3">
                <h3 class="font-bold text-sm text-[#111111]">{{ $editingSlot ? 'Edit Slot: ' . $editingSlot->name : 'Create New Ad Slot' }}</h3>
                @if ($editingSlot)
                    <a href="{{ route('admin.ads.index') }}" class="text-xs font-mono text-[#808080] hover:text-[#111111]">Cancel</a>
                @endif
            </div>

            <form action="{{ $editingSlot ? route('admin.ads.update', $editingSlot->id) : route('admin.ads.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                @if ($editingSlot)
                    @method('PUT')
                @endif

                <div>
                    <label for="name" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1 font-medium">Slot Name *</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $editingSlot->name ?? '') }}" required
                        class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]"
                        placeholder="e.g. Header Leaderboard 728x90">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label for="position" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1 font-medium">Position Slot *</label>
                        <select id="position" name="position" required class="w-full px-3 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                            @php
                                $positions = [
                                    'header'             => 'Header (Top Banner)',
                                    'sidebar'            => 'Sidebar (Sticky Rail)',
                                    'in_article_top'     => 'In-Article: Top',
                                    'in_article_middle'  => 'In-Article: Middle',
                                    'in_article_bottom'  => 'In-Article: Bottom',
                                    'between_articles'   => 'Between Articles (Feed)',
                                    'footer'             => 'Footer (Above Copyright)',
                                    'sticky_footer'      => 'Sticky Floating Footer',
                                ];
                                $selectedPos = old('position', $editingSlot->position ?? '');
                            @endphp
                            @foreach ($positions as $val => $label)
                                <option value="{{ $val }}" {{ $selectedPos === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="device" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1 font-medium">Device Targeting *</label>
                        <select id="device" name="device" required class="w-full px-3 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                            @php $selectedDev = old('device', $editingSlot->device ?? 'all'); @endphp
                            <option value="all" {{ $selectedDev === 'all' ? 'selected' : '' }}>All Devices</option>
                            <option value="desktop" {{ $selectedDev === 'desktop' ? 'selected' : '' }}>Desktop Only</option>
                            <option value="mobile" {{ $selectedDev === 'mobile' ? 'selected' : '' }}>Mobile Only</option>
                        </select>
                    </div>
                </div>

                {{-- Campaign Scheduling --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-3 bg-[#F8F8F6] border border-[#111111]/15">
                    <div>
                        <label for="start_date" class="block text-[10px] font-mono uppercase tracking-wider text-[#808080] mb-1">Schedule Start (Optional)</label>
                        <input type="datetime-local" id="start_date" name="start_date"
                            value="{{ old('start_date', $editingSlot && $editingSlot->start_date ? $editingSlot->start_date->format('Y-m-d\TH:i') : '') }}"
                            class="w-full px-2.5 py-1.5 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                    </div>

                    <div>
                        <label for="end_date" class="block text-[10px] font-mono uppercase tracking-wider text-[#808080] mb-1">Schedule End (Optional)</label>
                        <input type="datetime-local" id="end_date" name="end_date"
                            value="{{ old('end_date', $editingSlot && $editingSlot->end_date ? $editingSlot->end_date->format('Y-m-d\TH:i') : '') }}"
                            class="w-full px-2.5 py-1.5 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                    </div>
                </div>

                <div class="p-3 bg-[#F8F8F6] border border-[#111111]/15">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" id="is_banner" name="is_banner" value="1" {{ old('is_banner', $editingSlot->is_banner ?? false) ? 'checked' : '' }}
                            onchange="toggleAdType()" class="w-4 h-4 rounded-none border-[#111111] text-[#111111]">
                        <span class="text-xs font-mono font-bold text-[#111111]">Use Custom Banner Image (Self-Hosted Ads)</span>
                    </label>
                    <p class="text-[10px] font-mono text-[#808080] mt-1 ml-6">Uncheck to insert raw AdSense / Ad Network JavaScript tags.</p>
                </div>

                {{-- Banner Fields --}}
                <div id="banner-fields" class="space-y-4 {{ old('is_banner', $editingSlot->is_banner ?? false) ? '' : 'hidden' }}">
                    <div>
                        <label for="banner_image_file" class="block text-xs font-mono text-[#808080] mb-1">Banner Image File Upload</label>
                        <input type="file" id="banner_image_file" name="banner_image_file" accept="image/*"
                            class="w-full text-xs font-mono text-[#808080] file:mr-3 file:py-1.5 file:px-3 file:border-0 file:text-xs file:font-mono file:bg-primary file:text-secondary hover:file:opacity-90">
                    </div>

                    <div class="text-[10px] font-mono text-[#808080] text-center">— OR EXTERNAL IMAGE URL —</div>

                    <div>
                        <label for="banner_image" class="block text-xs font-mono text-[#808080] mb-1">Image URL</label>
                        <input type="url" id="banner_image" name="banner_image" value="{{ old('banner_image', $editingSlot->banner_image ?? '') }}"
                            class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]"
                            placeholder="https://securofi.tech/images/banner-728x90.jpg">
                    </div>

                    <div>
                        <label for="target_url" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1">Target Click URL</label>
                        <input type="url" id="target_url" name="target_url" value="{{ old('target_url', $editingSlot->target_url ?? '') }}"
                            class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]"
                            placeholder="https://sponsor.com/partner-ref">
                        <p class="text-[10px] font-mono text-[#808080] mt-1">Clicks are auto-tracked through /ad/click/{id} and forwarded.</p>
                    </div>
                </div>

                {{-- Script / HTML Code Field --}}
                <div id="script-fields" class="{{ old('is_banner', $editingSlot->is_banner ?? false) ? 'hidden' : '' }}">
                    <label for="code" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1 font-medium">Ad HTML / JavaScript Snippet</label>
                    <textarea id="code" name="code" rows="5"
                        class="w-full px-3.5 py-2 text-xs font-mono bg-[#F8F8F6] border border-[#111111]/30 focus:outline-none focus:border-[#111111]"
                        placeholder="<!-- Google AdSense Tag -->&#10;<ins class='adsbygoogle' ...></ins>&#10;<script>(adsbygoogle = window.adsbygoogle || []).push({});</script>">{{ old('code', $editingSlot->code ?? '') }}</textarea>
                </div>

                {{-- Category Targeting --}}
                <div>
                    <label class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1 font-medium">Category Targeting</label>
                    <p class="text-[10px] font-mono text-[#808080] mb-2">Leave all unchecked to show across all categories universally.</p>
                    <div class="max-h-36 overflow-y-auto border border-[#111111]/15 p-2.5 space-y-1.5 bg-[#F8F8F6]">
                        @php
                            $selectedCats = old('category_ids', $editingSlot->category_ids ?? []);
                            if (!is_array($selectedCats)) {
                                $selectedCats = [];
                            }
                        @endphp
                        @foreach ($categories as $cat)
                            <label class="flex items-center gap-2 cursor-pointer text-xs font-mono hover:bg-white p-1">
                                <input type="checkbox" name="category_ids[]" value="{{ $cat->id }}"
                                    {{ in_array($cat->id, $selectedCats) ? 'checked' : '' }}
                                    class="w-3.5 h-3.5 rounded-none border-[#111111] text-[#111111]">
                                <span>{{ $cat->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Country-Based Ad Targeting (PRD-ADDNEW 5.2) --}}
                <div class="space-y-3 pt-2 border-t border-[#111111]/10">
                    <div class="flex items-center justify-between">
                        <label for="country_rule" class="block text-xs font-mono uppercase tracking-wider text-[#111111] font-bold">
                            Country Targeting (Geo-Targeting)
                        </label>
                        <span class="text-[10px] font-mono text-emerald-700 bg-emerald-50 px-1.5 py-0.2 border border-emerald-200">
                            GeoIP Active
                        </span>
                    </div>

                    <div>
                        <select id="country_rule" name="country_rule" onchange="toggleCountryList(this.value)"
                            class="w-full px-3 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                            @php $selectedRule = old('country_rule', $editingSlot->country_rule ?? 'all'); @endphp
                            <option value="all" {{ $selectedRule === 'all' ? 'selected' : '' }}>Show in All Countries (Universal)</option>
                            <option value="include" {{ $selectedRule === 'include' ? 'selected' : '' }}>Show ONLY in Specific Countries (Include)</option>
                            <option value="exclude" {{ $selectedRule === 'exclude' ? 'selected' : '' }}>HIDE in Specific Countries (Exclude)</option>
                        </select>
                    </div>

                    {{-- Countries Checkbox Grid --}}
                    <div id="country-selection-box" class="space-y-2 {{ $selectedRule === 'all' ? 'hidden' : '' }}">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-mono text-[#808080]">Select target countries:</span>
                            <div class="flex gap-2">
                                <button type="button" onclick="selectCountryGroup('tier1')" class="text-[10px] font-mono text-[#111111] hover:underline bg-[#F8F8F6] px-1.5 py-0.5 border border-[#111111]/20">
                                    + Tier-1
                                </button>
                                <button type="button" onclick="selectCountryGroup('eu')" class="text-[10px] font-mono text-[#111111] hover:underline bg-[#F8F8F6] px-1.5 py-0.5 border border-[#111111]/20">
                                    + EU (GDPR)
                                </button>
                                <button type="button" onclick="clearCountrySelection()" class="text-[10px] font-mono text-rose-700 hover:underline">
                                    Clear
                                </button>
                            </div>
                        </div>

                        <div class="max-h-36 overflow-y-auto border border-[#111111]/15 p-2.5 grid grid-cols-2 gap-1.5 bg-[#F8F8F6]">
                            @php
                                $selectedCountries = old('countries', $editingSlot->countries ?? []);
                                if (!is_array($selectedCountries)) $selectedCountries = [];
                            @endphp
                            @foreach ($allCountries ?? [] as $code => $name)
                                <label class="flex items-center gap-2 cursor-pointer text-[11px] font-mono hover:bg-white p-1">
                                    <input type="checkbox" name="countries[]" value="{{ $code }}"
                                        data-country="{{ $code }}"
                                        {{ in_array($code, $selectedCountries) ? 'checked' : '' }}
                                        class="country-checkbox w-3.5 h-3.5 rounded-none border-[#111111] text-[#111111]">
                                    <span class="truncate"><strong>{{ $code }}</strong> - {{ $name }}</span>
                                </label>
                            @endforeach
                        </div>

                        {{-- Fallback Ad Network Code --}}
                        <div>
                            <label for="fallback_code" class="block text-[10px] font-mono uppercase tracking-wider text-[#808080] mb-1 font-medium">
                                Alternative / Fallback Ad Code (Non-Targeted Countries)
                            </label>
                            <textarea id="fallback_code" name="fallback_code" rows="3"
                                class="w-full px-2.5 py-1.5 text-xs font-mono bg-[#F8F8F6] border border-[#111111]/30 focus:outline-none focus:border-[#111111]"
                                placeholder="<!-- Fallback Ad Code for Excluded Countries (Leave blank to hide completely) -->">{{ old('fallback_code', $editingSlot->fallback_code ?? '') }}</textarea>
                            <p class="text-[10px] font-mono text-[#808080] mt-0.5">Shown to visitors outside the targeted country list (e.g. GDPR ad network or alternate CPM network).</p>
                        </div>
                    </div>
                </div>

                <div class="pt-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="status" value="1" {{ old('status', $editingSlot->status ?? true) ? 'checked' : '' }}
                            class="w-4 h-4 rounded-none border-[#111111] text-[#111111]">
                        <span class="text-xs font-mono font-bold text-[#111111]">Active (Immediately Served in Frontend)</span>
                    </label>
                </div>

                <div class="pt-2">
                    <button type="submit" class="btn-primary w-full text-xs font-mono uppercase tracking-wider py-3">
                        {{ $editingSlot ? 'Update Ad Slot →' : 'Save Ad Slot →' }}
                    </button>
                </div>
            </form>
        </div>

        {{-- Right: Configured Slots Table (7 cols) --}}
        <div class="lg:col-span-7 bg-white border border-[#111111]/15 p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-[#111111]/10 pb-3">
                <h3 class="font-bold text-sm text-[#111111]">Active Inventory Slots & Telemetry</h3>
                <span class="text-xs font-mono text-[#808080]">{{ $adSlots->count() }} Slots Configured</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs font-mono">
                    <thead>
                        <tr class="border-b border-[#111111]/15 text-[#808080] uppercase tracking-wider text-[10px]">
                            <th class="py-2.5">Slot & Placement</th>
                            <th class="py-2.5">Type & Target</th>
                            <th class="py-2.5">Performance</th>
                            <th class="py-2.5">Status</th>
                            <th class="py-2.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#111111]/10">
                        @forelse ($adSlots as $slot)
                            <tr class="hover:bg-[#F5F1E8]/40 {{ $editingSlot && $editingSlot->id === $slot->id ? 'bg-[#F5F1E8]' : '' }}">
                                <td class="py-3 font-medium text-[#111111]">
                                    <div class="font-bold">{{ $slot->name }}</div>
                                    <div class="text-[10px] text-[#808080] flex items-center gap-1.5 mt-0.5">
                                        <span class="px-1 py-0.2 bg-[#F8F8F6] border border-[#111111]/15">{{ $slot->position }}</span>
                                        <span>•</span>
                                        <span class="uppercase text-[9px]">{{ $slot->device ?? 'all' }}</span>
                                    </div>
                                    @if ($slot->start_date || $slot->end_date)
                                        <div class="text-[9px] text-[#808080] mt-1">
                                            @if($slot->start_date) From {{ $slot->start_date->format('M d') }} @endif
                                            @if($slot->end_date) Until {{ $slot->end_date->format('M d, Y') }} @endif
                                        </div>
                                    @endif
                                </td>
                                <td class="py-3 text-[#808080]">
                                    <div>
                                        @if ($slot->is_banner)
                                            <span class="px-1.5 py-0.5 text-[9px] font-bold bg-blue-50 text-blue-800 border border-blue-200">Banner</span>
                                        @else
                                            <span class="px-1.5 py-0.5 text-[9px] font-bold bg-slate-50 text-slate-800 border border-slate-200">Script</span>
                                        @endif
                                    </div>
                                    <div class="text-[10px] text-[#808080] mt-1">
                                        @if (!empty($slot->category_ids))
                                            {{ count($slot->category_ids) }} Categories
                                        @else
                                            <span class="text-neutral">All Pages</span>
                                        @endif
                                    </div>
                                    <div class="mt-1">
                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 text-[9px] font-medium {{ $slot->country_rule === 'all' ? 'bg-slate-100 text-slate-700' : 'bg-amber-100 text-amber-800' }} border border-current/10">
                                            <span>🌍</span> {{ $slot->countryRuleLabel() }}
                                        </span>
                                    </div>
                                </td>
                                <td class="py-3">
                                    <div class="font-bold text-[#111111]">{{ number_format($slot->impressions ?? 0) }} <span class="text-[10px] font-normal text-[#808080]">imp</span></div>
                                    <div class="text-[10px] text-primary flex items-center gap-2 mt-0.5">
                                        <span>{{ number_format($slot->clicks ?? 0) }} clicks</span>
                                        <span class="px-1 bg-neutral/10 font-bold text-[#111111]">{{ $slot->ctr() }}% CTR</span>
                                    </div>
                                </td>
                                <td class="py-3">
                                    <div class="flex flex-col gap-1 items-start">
                                        <form action="{{ route('admin.ads.toggle', $slot->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" title="Click to toggle status" class="px-2 py-0.5 text-[10px] font-bold uppercase transition-colors {{ $slot->status ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-neutral/20 text-[#808080] hover:bg-neutral/30' }}">
                                                {{ $slot->status ? '● Enabled' : '○ Paused' }}
                                            </button>
                                        </form>
                                        @if ($slot->status && !$slot->isLive())
                                            <span class="text-[9px] text-amber-700 bg-amber-50 px-1 py-0.2 border border-amber-200">Scheduled / Expired</span>
                                        @elseif ($slot->status && $slot->isLive())
                                            <span class="text-[9px] text-emerald-700">● Live Now</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3 text-right space-y-1">
                                    <div class="space-x-2">
                                        <a href="{{ route('admin.ads.index', ['edit' => $slot->id]) }}" class="text-[#111111] font-bold hover:underline">Edit</a>
                                        <span>•</span>
                                        <form action="{{ route('admin.ads.destroy', $slot->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete ad slot {{ $slot->name }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-rose-600 hover:underline">Delete</button>
                                        </form>
                                    </div>
                                    @if (($slot->impressions > 0 || $slot->clicks > 0))
                                        <div>
                                            <form action="{{ route('admin.ads.reset_stats', $slot->id) }}" method="POST" class="inline" onsubmit="return confirm('Reset analytics telemetry for {{ $slot->name }}?')">
                                                @csrf
                                                <button type="submit" class="text-[10px] text-[#808080] hover:text-[#111111] underline">Reset Stats</button>
                                            </form>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-[#808080]">No ad slots defined yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>

<script>
    function toggleAdType() {
        const isBanner = document.getElementById('is_banner').checked;
        const bannerFields = document.getElementById('banner-fields');
        const scriptFields = document.getElementById('script-fields');
        if (isBanner) {
            bannerFields.classList.remove('hidden');
            scriptFields.classList.add('hidden');
        } else {
            bannerFields.classList.add('hidden');
            scriptFields.classList.remove('hidden');
        }
    }

    function toggleCountryList(val) {
        const box = document.getElementById('country-selection-box');
        if (box) {
            if (val === 'all') {
                box.classList.add('hidden');
            } else {
                box.classList.remove('hidden');
            }
        }
    }

    function selectCountryGroup(grp) {
        const tier1 = ['US', 'GB', 'CA', 'AU', 'NZ', 'DE', 'FR'];
        const eu = ['AT', 'BE', 'BG', 'HR', 'CY', 'CZ', 'DK', 'EE', 'FI', 'FR', 'DE', 'GR', 'HU', 'IE', 'IT', 'LV', 'LT', 'LU', 'MT', 'NL', 'PL', 'PT', 'RO', 'SK', 'SI', 'ES', 'SE'];
        const targets = grp === 'tier1' ? tier1 : eu;
        document.querySelectorAll('.country-checkbox').forEach(cb => {
            if (targets.includes(cb.getAttribute('data-country'))) {
                cb.checked = true;
            }
        });
    }

    function clearCountrySelection() {
        document.querySelectorAll('.country-checkbox').forEach(cb => {
            cb.checked = false;
        });
    }
</script>
@endsection
