@extends('layouts.admin')

@section('title', 'Create Page')
@section('header_title', 'Create Dynamic Content Page')

@section('content')
<form action="{{ route('admin.pages.store') }}" method="POST" class="space-y-8">
    @csrf

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        {{-- Left: Content Body (8 cols) --}}
        <div class="lg:col-span-8 space-y-6">
            <div class="bg-white border border-[#111111]/15 p-6 space-y-5">
                <div>
                    <label for="title" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1 font-medium">Page Title *</label>
                    <input type="text" id="title" name="title" value="{{ old('title') }}" required
                        class="w-full px-4 py-2.5 text-base font-medium bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]"
                        placeholder="e.g. Terms & Conditions or About Us">
                </div>

                <div>
                    <label for="slug" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1">Custom Slug (Optional)</label>
                    <input type="text" id="slug" name="slug" value="{{ old('slug') }}"
                        class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]"
                        placeholder="auto-generated-if-empty">
                </div>

                <div>
                    <label for="content" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1 font-medium">Page Body Content (Rich HTML) *</label>
                    <textarea id="content" name="content" rows="18" required
                        class="w-full px-4 py-3 text-sm font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111] leading-relaxed"
                        placeholder="Write or paste your page content here in HTML format...">{{ old('content') }}</textarea>
                </div>
            </div>

            {{-- On-Page SEO Section --}}
            <div class="bg-white border border-[#111111]/15 p-6 space-y-4">
                <div class="border-b border-[#111111]/10 pb-3">
                    <h3 class="font-bold text-sm text-[#111111]">Page SEO & OpenGraph</h3>
                    <p class="text-xs font-mono text-[#808080]">Search engine directives specific to this URL.</p>
                </div>

                <div>
                    <label for="meta_title" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1">Meta Title</label>
                    <input type="text" id="meta_title" name="meta_title" value="{{ old('meta_title') }}"
                        class="w-full px-3 py-1.5 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]"
                        placeholder="Custom title tag for search engines">
                </div>

                <div>
                    <label for="meta_description" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1">Meta Description</label>
                    <textarea id="meta_description" name="meta_description" rows="2"
                        class="w-full px-3 py-1.5 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]"
                        placeholder="SERP search result snippet...">{{ old('meta_description') }}</textarea>
                </div>

                <div>
                    <label for="og_image" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1">Social Share Image (OG Image URL)</label>
                    <input type="url" id="og_image" name="og_image" value="{{ old('og_image') }}"
                        class="w-full px-3 py-1.5 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]"
                        placeholder="https://securofi.tech/images/page-og.jpg">
                </div>
            </div>
        </div>

        {{-- Right: Publishing & Placement (4 cols) --}}
        <div class="lg:col-span-4 space-y-6">
            <div class="bg-white border border-[#111111]/15 p-6 space-y-4">
                <h3 class="font-bold text-sm text-[#111111] border-b border-[#111111]/10 pb-3">Page Attributes</h3>

                <div>
                    <label for="status" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1 font-medium">Status *</label>
                    <select id="status" name="status" required class="w-full px-3 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                        <option value="published" {{ old('status') == 'published' ? 'selected' : '' }}>Published (Live immediately)</option>
                        <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Draft (Private)</option>
                    </select>
                </div>

                <div>
                    <label for="template" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1 font-medium">Design Template</label>
                    <select id="template" name="template" required class="w-full px-3 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                        <option value="default" {{ old('template') == 'default' ? 'selected' : '' }}>Standard Article/Prose</option>
                        <option value="legal" {{ old('template') == 'legal' ? 'selected' : '' }}>Legal / Policy Document</option>
                        <option value="contact" {{ old('template') == 'contact' ? 'selected' : '' }}>Interactive Contact Form</option>
                    </select>
                </div>

                <div>
                    <label for="menu_order" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1">Display Navigation Order</label>
                    <input type="number" id="menu_order" name="menu_order" value="{{ old('menu_order', 0) }}"
                        class="w-full px-3 py-1.5 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                </div>

                {{-- Geo-Targeting & Fencing (PRD-ADDNEW 5.1) --}}
                <div class="pt-3 border-t border-[#111111]/10 space-y-3">
                    <label class="block text-xs font-mono uppercase tracking-wider text-[#111111] font-bold flex items-center gap-1">
                        <span>🌍</span> Regional Visibility
                    </label>

                    <div>
                        <select id="country_rule" name="country_rule" onchange="togglePageCountryBox(this.value)"
                            class="w-full px-2 py-1.5 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                            <option value="all" {{ old('country_rule', 'all') === 'all' ? 'selected' : '' }}>Worldwide (All Countries)</option>
                            <option value="include" {{ old('country_rule') === 'include' ? 'selected' : '' }}>Only Selected Countries</option>
                            <option value="exclude" {{ old('country_rule') === 'exclude' ? 'selected' : '' }}>Block Selected Countries</option>
                        </select>
                    </div>

                    @php
                        $selCountries = (array) old('countries', []);
                        $quickCountries = [
                            'US' => 'United States',
                            'GB' => 'United Kingdom',
                            'CA' => 'Canada',
                            'AU' => 'Australia',
                            'DE' => 'Germany',
                            'IN' => 'India',
                            'BD' => 'Bangladesh',
                        ];
                    @endphp

                    <div id="page-country-box" class="space-y-2 {{ old('country_rule', 'all') === 'all' ? 'hidden' : '' }}">
                        <div class="max-h-28 overflow-y-auto border border-[#111111]/15 p-2 space-y-1 bg-[#F8F8F6] text-xs font-mono">
                            @foreach ($quickCountries as $code => $name)
                                <label class="flex items-center gap-2 cursor-pointer hover:bg-white p-0.5">
                                    <input type="checkbox" name="countries[]" value="{{ $code }}"
                                        {{ in_array($code, $selCountries) ? 'checked' : '' }}
                                        class="w-3.5 h-3.5 rounded-none border-[#111111]">
                                    <span>{{ $name }} ({{ $code }})</span>
                                </label>
                            @endforeach
                        </div>

                        <div>
                            <label for="restriction_fallback_message" class="block text-[10px] font-mono uppercase tracking-wider text-[#808080] mb-1">Fallback Notice</label>
                            <input type="text" id="restriction_fallback_message" name="restriction_fallback_message"
                                value="{{ old('restriction_fallback_message') }}"
                                class="w-full px-2 py-1 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]"
                                placeholder="e.g. This legal terms applies in US jurisdictions.">
                        </div>
                    </div>
                </div>

                <div class="space-y-2 pt-2 border-t border-[#111111]/10">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="show_in_menu" value="1" {{ old('show_in_menu') ? 'checked' : '' }}
                            class="w-4 h-4 rounded-none border-[#111111] text-[#111111]">
                        <span class="text-xs font-mono text-[#111111]">Show Link in Header Navigation</span>
                    </label>

                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="show_in_footer" value="1" {{ old('show_in_footer', true) ? 'checked' : '' }}
                            class="w-4 h-4 rounded-none border-[#111111] text-[#111111]">
                        <span class="text-xs font-mono text-[#111111]">Show Link in Site Footer</span>
                    </label>
                </div>

                <div class="pt-4 border-t border-[#111111]/10">
                    <button type="submit" class="btn-primary w-full text-xs font-mono uppercase tracking-wider py-3">
                        Deploy Page →
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    function togglePageCountryBox(val) {
        const box = document.getElementById('page-country-box');
        if (box) {
            if (val === 'all') {
                box.classList.add('hidden');
            } else {
                box.classList.remove('hidden');
            }
        }
    }
</script>
@endsection
