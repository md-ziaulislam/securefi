@extends('layouts.admin')

@section('title', 'Edit: ' . $article->title)
@section('header_title', 'Edit Article #' . $article->id)

@push('styles')
    {{-- TinyMCE 6 Community (self-hosted via CDN — no API key required) --}}
    <script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js" referrerpolicy="origin"></script>
    <style>
        /* ── Tag Chip Input ─────────────────────────────── */
        .tag-chip-wrap {
            display: flex; flex-wrap: wrap; gap: 5px;
            padding: 6px; border: 1px solid rgba(17,17,17,.3);
            background: #fff; min-height: 38px; cursor: text;
            align-items: center;
        }
        .tag-chip-wrap:focus-within { border-color: #111111; }
        .tag-chip {
            display: inline-flex; align-items: center; gap: 4px;
            background: #111111; color: #fff;
            font-size: 10px; font-family: monospace;
            padding: 2px 7px; border-radius: 2px;
        }
        .tag-chip button { background: none; border: none; color: #ccc; cursor: pointer; padding: 0; font-size: 12px; line-height: 1; }
        .tag-chip button:hover { color: #fff; }
        #tag-text-input {
            border: none; outline: none; font-size: 11px; font-family: monospace;
            flex: 1; min-width: 120px; padding: 2px; background: transparent;
        }
        .tag-search-row { display: flex; align-items: center; gap: 6px; margin-bottom: 6px; }
        .tag-search-input {
            flex: 1; padding: 4px 8px; font-size: 11px; font-family: monospace;
            border: 1px solid rgba(17,17,17,.25); background: #fafafa; outline: none;
        }
        .tag-search-input:focus { border-color: #111111; }
        .tag-reserved-list { max-height: 140px; overflow-y: auto; }
        .tag-reserved-item {
            display: flex; align-items: center; gap: 6px;
            padding: 3px 4px; cursor: pointer; font-size: 11px; font-family: monospace;
        }
        .tag-reserved-item:hover { background: #F5F1E8; }
        .tag-reserved-item.hidden { display: none; }
        .tox-tinymce { border: 1px solid rgba(17,17,17,.3) !important; border-radius: 0 !important; }
    </style>
@endpush

@section('content')
<form action="{{ route('admin.articles.update', $article->id) }}" method="POST" enctype="multipart/form-data" class="space-y-8">
    @csrf
    @method('PUT')

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        {{-- Left Column: Title, Content, SEO --}}
        <div class="lg:col-span-8 space-y-6">
            <div class="bg-white border border-[#111111]/15 p-6 space-y-5">
                <div>
                    <label for="title" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-2 font-medium">Article Title *</label>
                    <input type="text" id="title" name="title" value="{{ old('title', $article->title) }}" required
                        class="w-full px-4 py-2.5 text-base font-medium bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                </div>

                <div>
                    <label for="slug" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-2 font-medium">Slug *</label>
                    <input type="text" id="slug" name="slug" value="{{ old('slug', $article->slug) }}" required
                        class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                </div>

                {{-- ── Premium WYSIWYG Content Editor ────────────────────────── --}}
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-xs font-mono uppercase tracking-wider text-[#111111] font-medium">
                            Article Body *
                            <span class="ml-2 text-[9px] font-mono text-emerald-700 border border-emerald-300 bg-emerald-50 px-1.5 py-0.5">WYSIWYG Editor</span>
                        </label>
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-mono text-[#808080]">Shortcodes:</span>
                            <button type="button" onclick="tinyInsert('[affiliate_product id=&quot;1&quot;]')" class="text-[10px] font-mono px-2 py-0.5 border border-[#111111]/30 hover:bg-[#F5F1E8]">+ Single Affiliate</button>
                            <button type="button" onclick="tinyInsert('[affiliate_comparison ids=&quot;1,2&quot;]')" class="text-[10px] font-mono px-2 py-0.5 border border-[#111111]/30 hover:bg-[#F5F1E8]">+ Comparison</button>
                        </div>
                    </div>
                    {{-- Hidden textarea that TinyMCE binds to for form submission --}}
                    <textarea id="content" name="content" required>{{ old('content', $article->content) }}</textarea>
                </div>

                <div>
                    <label for="excerpt" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-2 font-medium">Short Excerpt / Lead Summary</label>
                    <textarea id="excerpt" name="excerpt" rows="3"
                        class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111] leading-relaxed">{{ old('excerpt', $article->excerpt) }}</textarea>
                </div>
            </div>

            {{-- On-Page SEO Section --}}
            @php $seo = $article->seoMeta; @endphp
            <div class="bg-white border border-[#111111]/15 p-6 space-y-4">
                <div class="border-b border-[#111111]/10 pb-3">
                    <h3 class="font-bold text-sm text-[#111111]">On-Page SEO Configuration</h3>
                    <p class="text-xs font-mono text-[#808080]">Search engine optimization tags for this article.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="meta_title" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1">Meta Title</label>
                        <input type="text" id="meta_title" name="meta_title" value="{{ old('meta_title', $seo->meta_title ?? '') }}"
                            class="w-full px-3 py-1.5 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                    </div>

                    <div>
                        <label for="focus_keyword" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1">Focus Keyword</label>
                        <input type="text" id="focus_keyword" name="focus_keyword" value="{{ old('focus_keyword', $seo->focus_keyword ?? '') }}"
                            class="w-full px-3 py-1.5 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                    </div>
                </div>

                <div>
                    <label for="meta_description" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1">Meta Description</label>
                    <textarea id="meta_description" name="meta_description" rows="2"
                        class="w-full px-3 py-1.5 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">{{ old('meta_description', $seo->meta_description ?? '') }}</textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="canonical_url" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1">Canonical URL</label>
                        <input type="url" id="canonical_url" name="canonical_url" value="{{ old('canonical_url', $seo->canonical_url ?? '') }}"
                            class="w-full px-3 py-1.5 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                    </div>

                    <div>
                        <label for="robots" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1">Robots Tag</label>
                        <select id="robots" name="robots" class="w-full px-3 py-1.5 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                            <option value="index,follow" {{ ($seo->robots ?? '') === 'index,follow' ? 'selected' : '' }}>index, follow (Standard)</option>
                            <option value="noindex,follow" {{ ($seo->robots ?? '') === 'noindex,follow' ? 'selected' : '' }}>noindex, follow</option>
                            <option value="noindex,nofollow" {{ ($seo->robots ?? '') === 'noindex,nofollow' ? 'selected' : '' }}>noindex, nofollow</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column: Controls, Category, Media --}}
        <div class="lg:col-span-4 space-y-6">
            {{-- Publish Controls --}}
            <div class="bg-white border border-[#111111]/15 p-6 space-y-4">
                <h3 class="font-bold text-sm text-[#111111] border-b border-[#111111]/10 pb-3">Publish Settings</h3>

                <div>
                    <label for="status" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1 font-medium">Status *</label>
                    <select id="status" name="status" required class="w-full px-3 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                        <option value="published" {{ old('status', $article->status) == 'published' ? 'selected' : '' }}>Published (Live)</option>
                        <option value="draft" {{ old('status', $article->status) == 'draft' ? 'selected' : '' }}>Draft (Private)</option>
                        <option value="scheduled" {{ old('status', $article->status) == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                    </select>
                </div>

                <div>
                    <label for="published_at" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1">Published Timestamp</label>
                    <input type="datetime-local" id="published_at" name="published_at"
                        value="{{ old('published_at', $article->published_at ? $article->published_at->format('Y-m-d\TH:i') : '') }}"
                        class="w-full px-3 py-1.5 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                </div>

                <div class="pt-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $article->is_featured) ? 'checked' : '' }}
                            class="w-4 h-4 rounded-none border-[#111111] text-[#111111] focus:ring-0">
                        <span class="text-xs font-mono text-[#111111]">Feature on Homepage Hero / Ribbon</span>
                    </label>
                </div>

                <div class="pt-4 border-t border-[#111111]/10">
                    <button type="submit" class="btn-primary w-full text-xs font-mono uppercase tracking-wider py-3">
                        Update &amp; Deploy Changes →
                    </button>
                </div>
            </div>

            {{-- Category --}}
            <div class="bg-white border border-[#111111]/15 p-6 space-y-3">
                <label for="category_id" class="block text-xs font-mono uppercase tracking-wider text-[#111111] font-bold">Category *</label>
                <select id="category_id" name="category_id" required class="w-full px-3 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('category_id', $article->category_id) == $cat->id ? 'selected' : '' }}>
                            {{ $cat->displayName }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Featured Image --}}
            <div class="bg-white border border-[#111111]/15 p-6 space-y-4">
                <h3 class="font-bold text-xs font-mono uppercase tracking-wider text-[#111111] border-b border-[#111111]/10 pb-2">Featured Image</h3>

                @if ($article->featured_image)
                    <div class="border border-[#111111]/20 overflow-hidden mb-2">
                        <img src="{{ $article->featured_image }}" alt="Preview" class="w-full h-32 object-cover" />
                    </div>
                @endif

                <div>
                    <label for="featured_image_file" class="block text-xs font-mono text-[#808080] mb-1">Replace Image File</label>
                    <input type="file" id="featured_image_file" name="featured_image_file" accept="image/*"
                        class="w-full text-xs font-mono text-[#808080] file:mr-3 file:py-1.5 file:px-3 file:border-0 file:text-xs file:font-mono file:bg-primary file:text-secondary">
                </div>

                <div class="text-[10px] font-mono text-[#808080] text-center">— OR EXTERNAL URL —</div>

                <div>
                    <label for="featured_image" class="block text-xs font-mono text-[#808080] mb-1">Image URL</label>
                    <input type="url" id="featured_image" name="featured_image" value="{{ old('featured_image', $article->featured_image) }}"
                        class="w-full px-3 py-1.5 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                </div>
            </div>

            {{-- Country Targeting & Geo-Fencing (PRD-ADDNEW 5.1) --}}
            <div class="bg-white border border-[#111111]/15 p-6 space-y-4">
                <div class="border-b border-[#111111]/10 pb-2 flex items-center justify-between">
                    <h3 class="font-bold text-xs font-mono uppercase tracking-wider text-[#111111] flex items-center gap-1.5">
                        <span>🌍</span> Geo-Targeting &amp; Variants
                    </h3>
                    <span class="text-[9px] font-mono text-[#808080]">PRD 5.1</span>
                </div>

                <div>
                    <label for="country_rule" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1">Targeting Rule</label>
                    <select id="country_rule" name="country_rule" onchange="toggleArticleCountryBox(this.value)"
                        class="w-full px-3 py-1.5 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                        <option value="all" {{ old('country_rule', $article->country_rule ?? 'all') === 'all' ? 'selected' : '' }}>All Countries (Global Public)</option>
                        <option value="include" {{ old('country_rule', $article->country_rule) === 'include' ? 'selected' : '' }}>Only Include Selected Countries</option>
                        <option value="exclude" {{ old('country_rule', $article->country_rule) === 'exclude' ? 'selected' : '' }}>Exclude / Block Selected Countries</option>
                    </select>
                </div>

                @php
                    $selCountries = (array) old('countries', $article->countries ?? []);
                    $quickCountries = [
                        'US' => 'United States',
                        'GB' => 'United Kingdom',
                        'CA' => 'Canada',
                        'AU' => 'Australia',
                        'DE' => 'Germany',
                        'FR' => 'France',
                        'IN' => 'India',
                        'BD' => 'Bangladesh',
                    ];
                @endphp

                <div id="article-country-box" class="space-y-2 {{ old('country_rule', $article->country_rule ?? 'all') === 'all' ? 'hidden' : '' }}">
                    <div class="flex items-center gap-1.5 text-[10px] font-mono">
                        <button type="button" onclick="selectArticleGroup('tier1')" class="px-2 py-0.5 border border-[#111111]/20 hover:bg-[#F5F1E8]">Tier-1</button>
                        <button type="button" onclick="selectArticleGroup('eu')" class="px-2 py-0.5 border border-[#111111]/20 hover:bg-[#F5F1E8]">EU (27)</button>
                        <button type="button" onclick="clearArticleCountries()" class="px-2 py-0.5 border border-rose-200 text-rose-700 hover:bg-rose-50">Clear</button>
                    </div>

                    <div class="max-h-36 overflow-y-auto border border-[#111111]/15 p-2 space-y-1 bg-[#F8F8F6] text-xs font-mono">
                        @foreach ($quickCountries as $code => $name)
                            <label class="flex items-center gap-2 cursor-pointer hover:bg-white p-0.5">
                                <input type="checkbox" name="countries[]" value="{{ $code }}" data-country="{{ $code }}"
                                    {{ in_array($code, $selCountries) ? 'checked' : '' }}
                                    class="article-country-cb w-3.5 h-3.5 rounded-none border-[#111111]">
                                <span>{{ $name }} ({{ $code }})</span>
                            </label>
                        @endforeach
                    </div>

                    <div>
                        <label for="restriction_fallback_message" class="block text-[10px] font-mono uppercase tracking-wider text-[#808080] mb-1">Custom Fallback Notice (Optional)</label>
                        <textarea id="restriction_fallback_message" name="restriction_fallback_message" rows="2"
                            class="w-full px-2 py-1 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]"
                            placeholder="e.g. This guide applies exclusively to US regulatory filings.">{{ old('restriction_fallback_message', $article->restriction_fallback_message) }}</textarea>
                    </div>
                </div>

                {{-- Regional Variant Linking --}}
                <div class="pt-3 border-t border-[#111111]/10 space-y-2">
                    <label class="block text-xs font-mono uppercase tracking-wider text-[#111111] font-bold">Country Variant of Master</label>
                    <p class="text-[10px] font-mono text-[#808080]">Link this article as a regional edition of another article (for hreflang tags).</p>

                    <div>
                        <select name="master_article_id" class="w-full px-2 py-1.5 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                            <option value="">— Standalone / Master Article —</option>
                            @foreach ($potentialMasters as $pm)
                                <option value="{{ $pm->id }}" {{ old('master_article_id', $article->master_article_id) == $pm->id ? 'selected' : '' }}>
                                    {{ Str::limit($pm->title, 35) }} (#{{ $pm->id }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="variant_country" class="block text-[10px] font-mono uppercase tracking-wider text-[#808080] mb-1">Edition Country Code (2 letters)</label>
                        <input type="text" id="variant_country" name="variant_country" maxlength="2"
                            value="{{ old('variant_country', $article->variant_country) }}"
                            class="w-24 px-2 py-1 text-xs font-mono uppercase bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]"
                            placeholder="US">
                    </div>
                </div>
            </div>

            {{-- ── Advanced Tag Panel ──────────────────────────────────── --}}
            @php $selectedTagIds = $article->tags->pluck('id')->toArray(); @endphp
            <div class="bg-white border border-[#111111]/15 p-6 space-y-4" id="tag-panel">
                <div class="border-b border-[#111111]/10 pb-2 flex items-center justify-between">
                    <h3 class="font-bold text-xs font-mono uppercase tracking-wider text-[#111111]">🏷 Tags</h3>
                    <span class="text-[9px] font-mono text-[#808080]">Type to create custom</span>
                </div>

                {{-- Chip input for custom / inline tag creation --}}
                <div>
                    <label class="block text-[10px] font-mono uppercase tracking-wider text-[#808080] mb-1">Add Custom Tag</label>
                    <div class="tag-chip-wrap" id="tag-chip-wrap" onclick="document.getElementById('tag-text-input').focus()">
                        <input type="text" id="tag-text-input" placeholder="Type tag name, press Enter…"
                            autocomplete="off" spellcheck="false">
                    </div>
                    <p class="text-[9px] font-mono text-[#808080] mt-1">Press <kbd class="px-1 border border-[#111111]/20 text-[9px]">Enter</kbd> or <kbd class="px-1 border border-[#111111]/20 text-[9px]">,</kbd> to add. New tags are created automatically.</p>
                </div>

                {{-- Reserved / existing tags with search --}}
                <div>
                    <div class="tag-search-row">
                        <span class="text-[10px] font-mono uppercase tracking-wider text-[#808080]">Reserved Tags</span>
                        <input type="text" id="tag-search" class="tag-search-input" placeholder="Filter tags…" oninput="filterReservedTags(this.value)">
                    </div>
                    <div class="tag-reserved-list border border-[#111111]/10 bg-[#F8F8F6] p-1" id="reserved-tag-list">
                        @foreach ($tags as $tag)
                            <label class="tag-reserved-item" data-tag-name="{{ strtolower($tag->name) }}">
                                <input type="checkbox" name="tags[]" value="{{ $tag->id }}"
                                    {{ in_array($tag->id, old('tags', $selectedTagIds)) ? 'checked' : '' }}
                                    class="reserved-tag-cb w-3.5 h-3.5 rounded-none border-[#111111] text-[#111111]">
                                <span>#{{ $tag->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Hidden inputs for dynamically created tags will be appended here by JS --}}
                <div id="dynamic-tag-inputs"></div>
            </div>
        </div>
    </div>
</form>

<script>
// ══════════════════════════════════════════════════
//  TinyMCE 6 — Premium WYSIWYG Editor Init
// ══════════════════════════════════════════════════
tinymce.init({
    selector: '#content',
    height: 520,
    menubar: 'file edit view insert format tools table help',
    plugins: [
        'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
        'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
        'insertdatetime', 'media', 'table', 'help', 'wordcount', 'emoticons',
        'codesample', 'hr'
    ],
    toolbar:
        'undo redo | blocks | bold italic underline strikethrough | ' +
        'forecolor backcolor | alignleft aligncenter alignright alignjustify | ' +
        'bullist numlist outdent indent | link image media table | ' +
        'codesample code | hr charmap emoticons | removeformat | fullscreen preview | help',
    block_formats: 'Paragraph=p; Heading 2=h2; Heading 3=h3; Heading 4=h4; Blockquote=blockquote; Code=pre',
    content_style: `
        body { font-family: Georgia, 'Times New Roman', serif; font-size: 15px; line-height: 1.8; color: #111; max-width: 780px; margin: 0 auto; padding: 16px; }
        h2 { font-size: 1.45em; font-weight: 700; margin-top: 1.6em; }
        h3 { font-size: 1.2em; font-weight: 600; margin-top: 1.4em; }
        code, pre { font-family: 'Courier New', monospace; background: #f4f4f2; padding: 2px 5px; border-radius: 3px; }
        a { color: #1a6fc4; }
        blockquote { border-left: 3px solid #ddd; padding-left: 16px; color: #555; margin-left: 0; }
    `,
    branding: false,
    promotion: false,
    resize: true,
    statusbar: true,
    elementpath: false,
    setup: function (editor) {
        editor.on('change input keyup', function () {
            editor.save();
        });
    }
});

function tinyInsert(text) {
    if (tinymce.activeEditor) {
        tinymce.activeEditor.insertContent(text);
    } else {
        const ta = document.getElementById('content');
        const s = ta.selectionStart, e = ta.selectionEnd;
        ta.value = ta.value.substring(0, s) + '\n' + text + '\n' + ta.value.substring(e);
        ta.focus();
    }
}

// ══════════════════════════════════════════════════
//  Tag Chip Input — Custom Inline Tag Creation
// ══════════════════════════════════════════════════
const chipWrap   = document.getElementById('tag-chip-wrap');
const tagInput   = document.getElementById('tag-text-input');
const dynInputs  = document.getElementById('dynamic-tag-inputs');
const addedSlugs = new Set();

function slugify(str) {
    return str.toLowerCase().trim().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
}

function addChip(name, tagId) {
    const slug = slugify(name);
    if (!name.trim() || addedSlugs.has(slug)) return;
    addedSlugs.add(slug);

    const chip = document.createElement('span');
    chip.className = 'tag-chip';
    chip.dataset.slug = slug;
    chip.innerHTML = `#${name} <button type="button" aria-label="Remove" onclick="removeChip('${slug}')">&times;</button>`;
    chipWrap.insertBefore(chip, tagInput);

    if (tagId) {
        const existing = document.querySelector(`input[name="tags[]"][value="${tagId}"]`);
        if (existing) { existing.checked = true; return; }
        const hidden = document.createElement('input');
        hidden.type = 'hidden'; hidden.name = 'tags[]'; hidden.value = tagId;
        hidden.dataset.slug = slug;
        dynInputs.appendChild(hidden);
    } else {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content
                  || '{{ csrf_token() }}';
        fetch('{{ route("admin.tags.quick-create") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: JSON.stringify({ name: name.trim() })
        })
        .then(r => r.json())
        .then(data => {
            const hidden = document.createElement('input');
            hidden.type = 'hidden'; hidden.name = 'tags[]'; hidden.value = data.id;
            hidden.dataset.slug = slug;
            dynInputs.appendChild(hidden);
            const cb = document.querySelector(`.reserved-tag-cb[value="${data.id}"]`);
            if (cb) cb.checked = true;
        })
        .catch(() => removeChip(slug));
    }
}

function removeChip(slug) {
    const chip = chipWrap.querySelector(`[data-slug="${slug}"]`);
    if (chip) chip.remove();
    const hidden = dynInputs.querySelector(`[data-slug="${slug}"]`);
    if (hidden) hidden.remove();
    addedSlugs.delete(slug);
}

tagInput.addEventListener('keydown', function (e) {
    if (e.key === 'Enter' || e.key === ',') {
        e.preventDefault();
        const val = this.value.replace(/,/g, '').trim();
        if (val) {
            const match = [...document.querySelectorAll('.tag-reserved-item')].find(el =>
                el.dataset.tagName === val.toLowerCase()
            );
            const tagId = match ? match.querySelector('input').value : null;
            addChip(val, tagId);
            this.value = '';
        }
    }
    if (e.key === 'Backspace' && !this.value) {
        const chips = chipWrap.querySelectorAll('.tag-chip');
        if (chips.length > 0) {
            const lastSlug = chips[chips.length - 1].dataset.slug;
            removeChip(lastSlug);
        }
    }
});

// ══════════════════════════════════════════════════
//  Reserved Tag — Live Search Filter
// ══════════════════════════════════════════════════
function filterReservedTags(query) {
    const q = query.toLowerCase().trim();
    document.querySelectorAll('.tag-reserved-item').forEach(function (item) {
        const name = item.dataset.tagName || '';
        item.classList.toggle('hidden', q.length > 0 && !name.includes(q));
    });
}

// ══════════════════════════════════════════════════
//  Geo-Fencing helpers (unchanged)
// ══════════════════════════════════════════════════
function toggleArticleCountryBox(val) {
    const box = document.getElementById('article-country-box');
    if (box) box.classList.toggle('hidden', val === 'all');
}

function selectArticleGroup(grp) {
    const tier1 = ['US', 'GB', 'CA', 'AU', 'NZ', 'DE', 'FR'];
    const eu = ['AT','BE','BG','HR','CY','CZ','DK','EE','FI','FR','DE','GR','HU','IE','IT','LV','LT','LU','MT','NL','PL','PT','RO','SK','SI','ES','SE'];
    const targets = grp === 'tier1' ? tier1 : eu;
    document.querySelectorAll('.article-country-cb').forEach(cb => {
        if (targets.includes(cb.getAttribute('data-country'))) cb.checked = true;
    });
}

function clearArticleCountries() {
    document.querySelectorAll('.article-country-cb').forEach(cb => { cb.checked = false; });
}
</script>
@endsection
