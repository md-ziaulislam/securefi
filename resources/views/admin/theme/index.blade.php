@extends('layouts.admin')

@section('title', 'Theme Customizer & Design Tokens')
@section('header_title', 'Swiss Design System Customizer')

@section('content')
<div class="space-y-8">

    {{-- Explainer & Reset Bar --}}
    <div class="bg-white border border-[#111111]/15 p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h3 class="font-bold text-sm text-[#111111]">Swiss Minimalist Design Engine</h3>
            <p class="text-xs font-mono text-[#808080] mt-1">
                Customize CSS custom properties directly from database tokens without altering Tailwind or CSS code.
            </p>
        </div>

        <form action="{{ route('admin.theme.reset') }}" method="POST" onsubmit="return confirm('Reset all design tokens to the original design.md Swiss specification?')">
            @csrf
            <button type="submit" class="px-3 py-1.5 text-xs font-mono border border-rose-300 text-rose-700 hover:bg-rose-50 transition-colors">
                ↺ Reset to Defaults (design.md)
            </button>
        </form>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

        {{-- Left: Token Configuration Form (7 cols) --}}
        <div class="lg:col-span-7 bg-white border border-[#111111]/15 p-6 space-y-6">
            <form action="{{ route('admin.theme.update') }}" method="POST" class="space-y-6">
                @csrf

                {{-- 1. Color Palette Tokens --}}
                <div class="space-y-4">
                    <div class="border-b border-[#111111]/10 pb-2">
                        <h4 class="font-bold text-xs font-mono uppercase tracking-wider text-[#111111]">1. Color Palette Tokens</h4>
                        <p class="text-[11px] font-mono text-[#808080]">High-contrast, curated Swiss HSL palette.</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- Primary --}}
                        <div class="p-3 border border-[#111111]/15 bg-[#F8F8F6] space-y-1.5">
                            <label class="block text-[10px] font-mono uppercase tracking-wider text-[#111111] font-bold">Primary (Off-Black/Text/CTA)</label>
                            <div class="flex items-center gap-2">
                                <input type="color" id="picker_primary" value="{{ $tokens['color_primary'] ?? '#111111' }}" onchange="syncColor('color_primary', this.value)" class="w-8 h-8 p-0 border border-[#111111]/20 cursor-pointer">
                                <input type="text" id="color_primary" name="color_primary" value="{{ old('color_primary', $tokens['color_primary'] ?? '#111111') }}" required
                                    class="w-full px-2 py-1.5 text-xs font-mono bg-white border border-[#111111]/30 uppercase">
                            </div>
                        </div>

                        {{-- Secondary --}}
                        <div class="p-3 border border-[#111111]/15 bg-[#F8F8F6] space-y-1.5">
                            <label class="block text-[10px] font-mono uppercase tracking-wider text-[#111111] font-bold">Secondary (Surface / White)</label>
                            <div class="flex items-center gap-2">
                                <input type="color" id="picker_secondary" value="{{ $tokens['color_secondary'] ?? '#FFFFFF' }}" onchange="syncColor('color_secondary', this.value)" class="w-8 h-8 p-0 border border-[#111111]/20 cursor-pointer">
                                <input type="text" id="color_secondary" name="color_secondary" value="{{ old('color_secondary', $tokens['color_secondary'] ?? '#FFFFFF') }}" required
                                    class="w-full px-2 py-1.5 text-xs font-mono bg-white border border-[#111111]/30 uppercase">
                            </div>
                        </div>

                        {{-- Tertiary --}}
                        <div class="p-3 border border-[#111111]/15 bg-[#F8F8F6] space-y-1.5">
                            <label class="block text-[10px] font-mono uppercase tracking-wider text-[#111111] font-bold">Tertiary (Beige Accent BG)</label>
                            <div class="flex items-center gap-2">
                                <input type="color" id="picker_tertiary" value="{{ $tokens['color_tertiary'] ?? '#F5F1E8' }}" onchange="syncColor('color_tertiary', this.value)" class="w-8 h-8 p-0 border border-[#111111]/20 cursor-pointer">
                                <input type="text" id="color_tertiary" name="color_tertiary" value="{{ old('color_tertiary', $tokens['color_tertiary'] ?? '#F5F1E8') }}" required
                                    class="w-full px-2 py-1.5 text-xs font-mono bg-white border border-[#111111]/30 uppercase">
                            </div>
                        </div>

                        {{-- Neutral --}}
                        <div class="p-3 border border-[#111111]/15 bg-[#F8F8F6] space-y-1.5">
                            <label class="block text-[10px] font-mono uppercase tracking-wider text-[#111111] font-bold">Neutral (Muted Grey / Borders)</label>
                            <div class="flex items-center gap-2">
                                <input type="color" id="picker_neutral" value="{{ $tokens['color_neutral'] ?? '#808080' }}" onchange="syncColor('color_neutral', this.value)" class="w-8 h-8 p-0 border border-[#111111]/20 cursor-pointer">
                                <input type="text" id="color_neutral" name="color_neutral" value="{{ old('color_neutral', $tokens['color_neutral'] ?? '#808080') }}" required
                                    class="w-full px-2 py-1.5 text-xs font-mono bg-white border border-[#111111]/30 uppercase">
                            </div>
                        </div>

                        {{-- Surface / Taupe --}}
                        <div class="p-3 border border-[#111111]/15 bg-[#F8F8F6] space-y-1.5 sm:col-span-2">
                            <label class="block text-[10px] font-mono uppercase tracking-wider text-[#111111] font-bold">Surface (Taupe Decorative Highlight)</label>
                            <div class="flex items-center gap-2 max-w-sm">
                                <input type="color" id="picker_surface" value="{{ $tokens['color_surface'] ?? '#B38B6D' }}" onchange="syncColor('color_surface', this.value)" class="w-8 h-8 p-0 border border-[#111111]/20 cursor-pointer">
                                <input type="text" id="color_surface" name="color_surface" value="{{ old('color_surface', $tokens['color_surface'] ?? '#B38B6D') }}" required
                                    class="w-full px-2 py-1.5 text-xs font-mono bg-white border border-[#111111]/30 uppercase">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 2. Typography Hierarchy --}}
                <div class="space-y-4">
                    <div class="border-b border-[#111111]/10 pb-2">
                        <h4 class="font-bold text-xs font-mono uppercase tracking-wider text-[#111111]">2. Typography Hierarchy</h4>
                        <p class="text-[11px] font-mono text-[#808080]">Display, body, and technical metadata typefaces.</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label for="font_heading" class="block text-[10px] font-mono uppercase tracking-wider text-[#111111] mb-1 font-bold">Headings Font</label>
                            <input type="text" id="font_heading" name="font_heading" value="{{ old('font_heading', $tokens['font_heading'] ?? 'Inter') }}" required
                                class="w-full px-2.5 py-1.5 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                        </div>

                        <div>
                            <label for="font_body" class="block text-[10px] font-mono uppercase tracking-wider text-[#111111] mb-1 font-bold">Body Font</label>
                            <input type="text" id="font_body" name="font_body" value="{{ old('font_body', $tokens['font_body'] ?? 'Inter') }}" required
                                class="w-full px-2.5 py-1.5 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                        </div>

                        <div>
                            <label for="font_mono" class="block text-[10px] font-mono uppercase tracking-wider text-[#111111] mb-1 font-bold">Monospace / Meta</label>
                            <input type="text" id="font_mono" name="font_mono" value="{{ old('font_mono', $tokens['font_mono'] ?? 'JetBrains Mono') }}" required
                                class="w-full px-2.5 py-1.5 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                        </div>
                    </div>
                </div>

                {{-- 3. Geometric Shape & Radius Scale --}}
                <div class="space-y-4">
                    <div class="border-b border-[#111111]/10 pb-2">
                        <h4 class="font-bold text-xs font-mono uppercase tracking-wider text-[#111111]">3. Shape & Corner Radius Scale</h4>
                        <p class="text-[11px] font-mono text-[#808080]">Swiss design standard mandates 0px sharp base geometry.</p>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div>
                            <label for="radius_base" class="block text-[10px] font-mono uppercase tracking-wider text-[#111111] mb-1 font-bold">Base (0px Sharp)</label>
                            <input type="text" id="radius_base" name="radius_base" value="{{ old('radius_base', $tokens['radius_base'] ?? '0px') }}" required
                                class="w-full px-2.5 py-1.5 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                        </div>

                        <div>
                            <label for="radius_sm" class="block text-[10px] font-mono uppercase tracking-wider text-[#111111] mb-1 font-bold">Small (sm)</label>
                            <input type="text" id="radius_sm" name="radius_sm" value="{{ old('radius_sm', $tokens['radius_sm'] ?? '2px') }}" required
                                class="w-full px-2.5 py-1.5 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                        </div>

                        <div>
                            <label for="radius_md" class="block text-[10px] font-mono uppercase tracking-wider text-[#111111] mb-1 font-bold">Medium (md)</label>
                            <input type="text" id="radius_md" name="radius_md" value="{{ old('radius_md', $tokens['radius_md'] ?? '4px') }}" required
                                class="w-full px-2.5 py-1.5 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                        </div>

                        <div>
                            <label for="radius_lg" class="block text-[10px] font-mono uppercase tracking-wider text-[#111111] mb-1 font-bold">Large (lg)</label>
                            <input type="text" id="radius_lg" name="radius_lg" value="{{ old('radius_lg', $tokens['radius_lg'] ?? '8px') }}" required
                                class="w-full px-2.5 py-1.5 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-[#111111]/10">
                    <button type="submit" class="btn-primary w-full text-xs font-mono uppercase tracking-wider py-3">
                        Deploy Design Tokens to Live Site →
                    </button>
                </div>
            </form>
        </div>

        {{-- Right: Live Component Preview (5 cols) --}}
        <div class="lg:col-span-5 space-y-6">
            <div class="bg-white border border-[#111111]/15 p-6 space-y-5">
                <div class="border-b border-[#111111]/10 pb-3">
                    <h3 class="font-bold text-sm text-[#111111]">Live Component Preview</h3>
                    <p class="text-xs font-mono text-[#808080]">Real-time visual test of configured tokens.</p>
                </div>

                {{-- Preview Card --}}
                <div class="p-6 border border-neutral/30 bg-secondary space-y-4">
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-mono uppercase px-2 py-0.5 bg-primary text-secondary">
                            Badge Token
                        </span>
                        <span class="text-xs font-mono text-neutral">Muted Meta Value</span>
                    </div>

                    <h4 class="text-xl font-bold text-primary leading-tight">
                        Zero Trust Cloud Architecture in 2026
                    </h4>

                    <p class="text-xs text-neutral leading-relaxed">
                        Swiss style layout emphasizing typography hierarchy, objective mathematical grids, and high-contrast color values.
                    </p>

                    <div class="flex items-center gap-3 pt-2">
                        <button type="button" class="btn-primary text-xs font-mono py-2 px-3.5">
                            Primary Button
                        </button>
                        <button type="button" class="btn-secondary text-xs font-mono py-2 px-3.5">
                            Secondary Outline
                        </button>
                    </div>
                </div>

                {{-- Input Preview --}}
                <div class="space-y-1.5">
                    <label class="block text-xs font-mono uppercase tracking-wider text-primary font-medium">Input Component</label>
                    <input type="text" readonly value="Sample input focused state"
                        class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-neutral/30 focus:outline-none focus:border-primary">
                </div>
            </div>
        </div>

    </div>

</div>

<script>
    function syncColor(id, value) {
        document.getElementById(id).value = value;
    }
</script>
@endsection
