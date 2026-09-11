@extends('layouts.admin')

@section('title', 'Affiliate Monetization Suite')
@section('header_title', 'Affiliate Products, Shortcodes & Click Telemetry')

@section('content')
<div class="space-y-8">

    {{-- Stats Bar & Disclosure Setting --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        {{-- Total Metrics --}}
        <div class="lg:col-span-4 bg-white border border-[#111111]/15 p-6 flex flex-col justify-between h-full">
            <div>
                <div class="text-[10px] font-mono text-[#808080] uppercase tracking-wider">Monetization Telemetry</div>
                <div class="text-3xl font-bold font-mono text-[#111111] my-2">
                    {{ number_format($totalClicks) }}
                </div>
                <div class="text-xs font-mono text-[#808080]">
                    Total outbound clicks across <strong class="text-[#111111]">{{ $activeCount }}</strong> active / {{ $totalProducts }} registered products
                </div>
            </div>
            <div class="mt-4 pt-4 border-t border-[#111111]/10 flex items-center justify-between text-xs font-mono">
                <span class="text-[#808080]">Cloak Base:</span>
                <span class="font-bold text-primary">/go/{slug}</span>
            </div>
        </div>

        {{-- Auto-Injected Affiliate Disclosure Settings --}}
        <div class="lg:col-span-8 bg-white border border-[#111111]/15 p-6 space-y-3">
            <div class="flex items-center justify-between border-b border-[#111111]/10 pb-2">
                <h4 class="font-bold text-xs font-mono uppercase tracking-wider text-[#111111]">Affiliate Transparency Disclosure (FTC Compliant)</h4>
                <span class="text-[10px] font-mono text-emerald-700 bg-emerald-50 px-2 py-0.5 border border-emerald-200">Auto-Injected</span>
            </div>
            <form action="{{ route('admin.affiliates.disclosure') }}" method="POST" class="space-y-3">
                @csrf
                <textarea name="affiliate_disclosure_text" rows="2" required
                    class="w-full px-3 py-1.5 text-xs font-mono bg-[#F8F8F6] border border-[#111111]/30 focus:outline-none focus:border-[#111111]">{{ old('affiliate_disclosure_text', $disclosureText) }}</textarea>

                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="affiliate_auto_inject_disclosure" value="1" {{ $autoInject === '1' ? 'checked' : '' }}
                            class="w-4 h-4 rounded-none border-[#111111] text-[#111111]">
                        <span class="text-xs font-mono text-[#111111]">Auto-inject disclosure notice above articles containing affiliate shortcodes</span>
                    </label>

                    <button type="submit" class="btn-secondary text-xs font-mono py-1.5 px-3 shrink-0">
                        Update Notice
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Main Working Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

        {{-- Left: Create or Edit Affiliate Product Form (5 cols) --}}
        <div class="lg:col-span-5 bg-white border border-[#111111]/15 p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-[#111111]/10 pb-3">
                <h3 class="font-bold text-sm text-[#111111]">{{ $editingProduct ? 'Edit Product: ' . $editingProduct->name : 'Add Affiliate Offer' }}</h3>
                @if ($editingProduct)
                    <a href="{{ route('admin.affiliates.index') }}" class="text-xs font-mono text-[#808080] hover:text-[#111111]">Cancel</a>
                @endif
            </div>

            <form action="{{ $editingProduct ? route('admin.affiliates.update', $editingProduct->id) : route('admin.affiliates.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                @if ($editingProduct)
                    @method('PUT')
                @endif

                <div>
                    <label for="name" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1 font-medium">Product / Service Name *</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $editingProduct->name ?? '') }}" required
                        class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]"
                        placeholder="e.g. NordVPN Enterprise / Ledger Nano X">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label for="slug" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1">Custom Cloak Slug</label>
                        <input type="text" id="slug" name="slug" value="{{ old('slug', $editingProduct->slug ?? '') }}"
                            class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]"
                            placeholder="e.g. nordvpn">
                        <p class="text-[9px] font-mono text-[#808080] mt-0.5">Will be accessible at /go/slug</p>
                    </div>

                    <div>
                        <label for="category_id" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1">Target Category</label>
                        <select id="category_id" name="category_id" class="w-full px-3 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                            <option value="">General / All Categories</option>
                            @php $selectedCat = old('category_id', $editingProduct->category_id ?? ''); @endphp
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}" {{ (string)$selectedCat === (string)$cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="source_site" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1">Partner Platform</label>
                        <input type="text" id="source_site" name="source_site" value="{{ old('source_site', $editingProduct->source_site ?? '') }}"
                            class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]"
                            placeholder="Amazon, Vultr, Direct">
                    </div>

                    <div>
                        <label for="price" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1">Price / Plan Callout</label>
                        <input type="text" id="price" name="price" value="{{ old('price', $editingProduct->price ?? '') }}"
                            class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]"
                            placeholder="$4.50/mo or Free Tier">
                    </div>
                </div>

                {{-- Rating & Badge & CTA Button --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 p-3 bg-[#F8F8F6] border border-[#111111]/15">
                    <div>
                        <label for="rating" class="block text-[10px] font-mono uppercase tracking-wider text-[#808080] mb-1">Star Rating (1-5)</label>
                        <input type="number" step="0.1" min="1" max="5" id="rating" name="rating"
                            value="{{ old('rating', $editingProduct->rating ?? '4.8') }}"
                            class="w-full px-2.5 py-1.5 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]"
                            placeholder="4.8">
                    </div>

                    <div>
                        <label for="badge_text" class="block text-[10px] font-mono uppercase tracking-wider text-[#808080] mb-1">Badge Callout</label>
                        <input type="text" id="badge_text" name="badge_text"
                            value="{{ old('badge_text', $editingProduct->badge_text ?? '') }}"
                            class="w-full px-2.5 py-1.5 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]"
                            placeholder="Editor's Choice">
                    </div>

                    <div>
                        <label for="button_text" class="block text-[10px] font-mono uppercase tracking-wider text-[#808080] mb-1">CTA Button Text</label>
                        <input type="text" id="button_text" name="button_text"
                            value="{{ old('button_text', $editingProduct->button_text ?? '') }}"
                            class="w-full px-2.5 py-1.5 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]"
                            placeholder="Check Deal →">
                    </div>
                </div>

                <div>
                    <label for="affiliate_url" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1 font-medium">Primary / Default Affiliate URL *</label>
                    <input type="url" id="affiliate_url" name="affiliate_url" value="{{ old('affiliate_url', $editingProduct->affiliate_url ?? '') }}" required
                        class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]"
                        placeholder="https://partner.com/?ref=securofi">
                    <p class="text-[10px] font-mono text-[#808080] mt-1">Visitors click cloaked URL and get seamlessly redirected with no-referrer.</p>
                </div>

                {{-- Geo-Targeted Affiliate Links (PRD-ADDNEW 5.3) --}}
                <div class="p-3 bg-amber-50/50 border border-amber-200/80 space-y-3">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-mono uppercase tracking-wider text-amber-950 font-bold flex items-center gap-1.5">
                            <span>🌍</span> Country-Specific Affiliate Links
                        </label>
                        <span class="text-[10px] text-amber-800 font-mono">Auto-switches based on visitor location</span>
                    </div>
                    <p class="text-[11px] text-[#808080] leading-tight">
                        Provide localized store links (e.g. Amazon US, UK, DE, CA, IN). When a user from that country clicks the cloaked link, they are automatically sent to the appropriate localized store.
                    </p>

                    @php
                        $cLinks = old('country_links', $editingProduct->country_links ?? []);
                        $commonCountries = [
                            'US' => 'United States (US)',
                            'GB' => 'United Kingdom (UK)',
                            'CA' => 'Canada (CA)',
                            'DE' => 'Germany (DE)',
                            'IN' => 'India (IN)',
                            'AU' => 'Australia (AU)',
                        ];
                    @endphp

                    <div class="space-y-2">
                        @foreach ($commonCountries as $code => $name)
                            <div class="flex items-center gap-2">
                                <span class="w-36 text-[10px] font-mono font-bold text-[#111111] shrink-0">{{ $name }}:</span>
                                <input type="url" name="country_links[{{ $code }}]" value="{{ $cLinks[$code] ?? '' }}"
                                    class="flex-1 px-2.5 py-1.5 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]"
                                    placeholder="https://amazon.{{ strtolower($code === 'GB' ? 'co.uk' : ($code === 'AU' ? 'com.au' : ($code === 'US' ? 'com' : $code))) }}/dp/...?tag=...">
                            </div>
                        @endforeach
                    </div>

                    <div class="pt-2 border-t border-amber-200/60">
                        <label for="fallback_affiliate_url" class="block text-[10px] font-mono uppercase tracking-wider text-[#111111] mb-1">Global Fallback URL (Optional)</label>
                        <input type="url" id="fallback_affiliate_url" name="fallback_affiliate_url"
                            value="{{ old('fallback_affiliate_url', $editingProduct->fallback_affiliate_url ?? '') }}"
                            class="w-full px-2.5 py-1.5 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]"
                            placeholder="Used if visitor's country has no localized link above">
                    </div>
                </div>

                <div>
                    <label for="description" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1">Short Description / Verdict</label>
                    <textarea id="description" name="description" rows="3"
                        class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111] leading-relaxed"
                        placeholder="Key specifications, pros, and benchmark rationale...">{{ old('description', $editingProduct->description ?? '') }}</textarea>
                </div>

                {{-- Product Image --}}
                <div class="space-y-2 p-3 bg-[#F8F8F6] border border-[#111111]/15">
                    <label class="block text-xs font-mono uppercase tracking-wider text-[#111111] font-bold">Product Showcase Image</label>
                    <input type="file" id="image_file" name="image_file" accept="image/*"
                        class="w-full text-xs font-mono text-[#808080] file:mr-3 file:py-1 file:px-3 file:border-0 file:text-xs file:font-mono file:bg-primary file:text-secondary hover:file:opacity-90">

                    <div class="text-[10px] font-mono text-[#808080]">— OR DIRECT IMAGE URL —</div>

                    <input type="url" id="image" name="image" value="{{ old('image', $editingProduct->image ?? '') }}"
                        class="w-full px-3 py-1.5 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]"
                        placeholder="https://securofi.tech/images/product.jpg">
                </div>

                <div>
                    <label for="rel_type" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1">Link Rel Attribute</label>
                    <input type="text" id="rel_type" name="rel_type" value="{{ old('rel_type', $editingProduct->rel_type ?? 'sponsored nofollow') }}"
                        class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                </div>

                <div class="grid grid-cols-2 gap-3 pt-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $editingProduct->is_featured ?? false) ? 'checked' : '' }}
                            class="w-4 h-4 rounded-none border-[#111111] text-[#111111]">
                        <span class="text-xs font-mono font-bold text-[#111111]">Featured Product</span>
                    </label>

                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="status" value="1" {{ old('status', $editingProduct->status ?? true) ? 'checked' : '' }}
                            class="w-4 h-4 rounded-none border-[#111111] text-[#111111]">
                        <span class="text-xs font-mono font-bold text-[#111111]">Active Product</span>
                    </label>
                </div>

                <div class="pt-2">
                    <button type="submit" class="btn-primary w-full text-xs font-mono uppercase tracking-wider py-3">
                        {{ $editingProduct ? 'Update Affiliate Offer →' : 'Save Affiliate Offer →' }}
                    </button>
                </div>
            </form>
        </div>

        {{-- Right: Shortcode Helpers & Products Inventory (7 cols) --}}
        <div class="lg:col-span-7 space-y-6">

            {{-- Shortcode Generator Helper Card --}}
            <div class="bg-white border border-[#111111]/15 p-6 space-y-4">
                <div class="border-b border-[#111111]/10 pb-3">
                    <h3 class="font-bold text-sm text-[#111111]">Editorial Shortcode Cheatsheet</h3>
                    <p class="text-xs font-mono text-[#808080]">Paste these shortcodes anywhere in article bodies to render styled cards automatically.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs font-mono">
                    <div class="p-3 bg-[#F8F8F6] border border-[#111111]/15 flex flex-col justify-between">
                        <div>
                            <span class="font-bold text-[#111111] block mb-1">1. Single Box</span>
                            <code class="text-[11px] text-surface bg-white p-1 block border border-neutral/20 select-all">[affiliate_product id="1"]</code>
                        </div>
                        <span class="text-[10px] text-[#808080] mt-2">Full card with star rating, badge, specs & CTA</span>
                    </div>

                    <div class="p-3 bg-[#F8F8F6] border border-[#111111]/15 flex flex-col justify-between">
                        <div>
                            <span class="font-bold text-[#111111] block mb-1">2. Comparison</span>
                            <code class="text-[11px] text-surface bg-white p-1 block border border-neutral/20 select-all">[affiliate_comparison ids="1,2"]</code>
                        </div>
                        <span class="text-[10px] text-[#808080] mt-2">Multi-column side-by-side matrix</span>
                    </div>

                    <div class="p-3 bg-[#F8F8F6] border border-[#111111]/15 flex flex-col justify-between">
                        <div>
                            <span class="font-bold text-[#111111] block mb-1">3. Solution List</span>
                            <code class="text-[11px] text-surface bg-white p-1 block border border-neutral/20 select-all">[affiliate_list ids="1,2"]</code>
                        </div>
                        <span class="text-[10px] text-[#808080] mt-2">Ranked numbered vertical directory</span>
                    </div>
                </div>
            </div>

            {{-- Products Table --}}
            <div class="bg-white border border-[#111111]/15 p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-[#111111]/10 pb-3">
                    <h3 class="font-bold text-sm text-[#111111]">Product Directory & Telemetry</h3>
                    <span class="text-xs font-mono text-[#808080]">{{ $products->count() }} registered</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs font-mono">
                        <thead>
                            <tr class="border-b border-[#111111]/15 text-[#808080] uppercase tracking-wider text-[10px]">
                                <th class="py-2.5">Product & Cloaked Link</th>
                                <th class="py-2.5">Category & Rating</th>
                                <th class="py-2.5">Clicks</th>
                                <th class="py-2.5">Status</th>
                                <th class="py-2.5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#111111]/10">
                            @forelse ($products as $prod)
                                <tr class="hover:bg-[#F5F1E8]/40 {{ $editingProduct && $editingProduct->id === $prod->id ? 'bg-[#F5F1E8]' : '' }}">
                                    <td class="py-3 font-medium text-[#111111]">
                                        <div class="font-bold flex items-center gap-2">
                                            <span class="text-[10px] text-[#808080]">#{{ $prod->id }}</span>
                                            <span>{{ $prod->name }}</span>
                                            @if ($prod->is_featured)
                                                <span class="px-1 py-0.2 text-[8px] uppercase tracking-wider font-bold bg-amber-100 text-amber-800 border border-amber-300">Featured</span>
                                            @endif
                                        </div>
                                        <div class="text-[10px] text-surface mt-0.5 select-all">
                                            [affiliate_product id="{{ $prod->id }}"]
                                        </div>
                                        <div class="text-[10px] text-[#808080] mt-0.5 flex items-center gap-2">
                                            <a href="{{ $prod->cloakedUrl() }}" target="_blank" class="hover:underline text-primary">
                                                {{ $prod->cloakedUrl() }} ↗
                                            </a>
                                            @if ($prod->countryLinkCount() > 0)
                                                <span class="inline-flex items-center gap-0.5 px-1 py-0.2 text-[9px] bg-amber-100 text-amber-900 border border-amber-300">
                                                    <span>🌍</span> {{ $prod->countryLinkCount() }} localized
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="py-3 text-[#808080]">
                                        <div class="font-bold text-[#111111]">{{ $prod->category->name ?? 'Universal' }}</div>
                                        <div class="text-[10px] flex items-center gap-1 mt-0.5">
                                            @if ($prod->rating)
                                                <span class="text-amber-500 font-bold">★ {{ $prod->rating }}</span>
                                            @endif
                                            @if ($prod->badge_text)
                                                <span class="px-1 py-0.2 text-[8px] bg-neutral/10 font-bold text-[#111111]">{{ $prod->badge_text }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="py-3 font-bold text-primary">
                                        <a href="{{ $prod->cloakedUrl() }}" target="_blank" title="Test tracking redirect" class="px-2 py-0.5 bg-primary text-secondary text-[10px] hover:opacity-90 inline-block">
                                            {{ number_format($prod->click_count) }} clicks
                                        </a>
                                    </td>
                                    <td class="py-3">
                                        <form action="{{ route('admin.affiliates.toggle', $prod->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" title="Toggle active status" class="px-2 py-0.5 text-[10px] font-bold uppercase transition-colors {{ $prod->status ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-neutral/20 text-[#808080] hover:bg-neutral/30' }}">
                                                {{ $prod->status ? 'Active' : 'Paused' }}
                                            </button>
                                        </form>
                                    </td>
                                    <td class="py-3 text-right space-y-1">
                                        <div class="space-x-2">
                                            <a href="{{ route('admin.affiliates.index', ['edit' => $prod->id]) }}" class="text-[#111111] font-bold hover:underline">Edit</a>
                                            <span>•</span>
                                            <form action="{{ route('admin.affiliates.destroy', $prod->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete affiliate product #{{ $prod->id }}?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-rose-600 hover:underline">Delete</button>
                                            </form>
                                        </div>
                                        @if ($prod->click_count > 0)
                                            <div>
                                                <form action="{{ route('admin.affiliates.reset_clicks', $prod->id) }}" method="POST" class="inline" onsubmit="return confirm('Reset click count for #{{ $prod->id }}?')">
                                                    @csrf
                                                    <button type="submit" class="text-[10px] text-[#808080] hover:text-[#111111] underline">Reset Clicks</button>
                                                </form>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-[#808080]">No affiliate products configured yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
