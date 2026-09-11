@extends('layouts.admin')

@section('title', 'Category Architecture')
@section('header_title', 'Taxonomy & Category Hierarchy')

@section('content')
<div class="space-y-6">

    {{-- Top Info Banner --}}
    <div class="bg-white border border-[#111111]/15 p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h3 class="font-bold text-sm text-[#111111]">Hierarchical Taxonomy Management</h3>
            <p class="text-xs font-mono text-[#808080] mt-1">
                Organize content into top-level Main Categories and nested Subcategories, exactly like leading publishing houses.
            </p>
        </div>
        <div class="flex items-center gap-2 font-mono text-xs">
            <span class="px-2.5 py-1 bg-[#F8F8F6] border border-[#111111]/15 font-bold text-[#111111]">
                {{ $rootCategories->count() }} Main
            </span>
            <span class="px-2.5 py-1 bg-[#F8F8F6] border border-[#111111]/15 text-[#808080]">
                {{ $categories->whereNotNull('parent_id')->count() }} Subcategories
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        {{-- Left: Create or Edit Category Form --}}
        <div class="lg:col-span-4 bg-white border border-[#111111]/15 p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-[#111111]/10 pb-3">
                <h3 class="font-bold text-sm text-[#111111]">{{ $editingCategory ? 'Edit: ' . $editingCategory->name : 'Add New Category' }}</h3>
                @if ($editingCategory)
                    <a href="{{ route('admin.categories.index') }}" class="text-xs font-mono text-[#808080] hover:text-[#111111]">Cancel</a>
                @endif
            </div>

            <form action="{{ $editingCategory ? route('admin.categories.update', $editingCategory->id) : route('admin.categories.store') }}" method="POST" class="space-y-4">
                @csrf
                @if ($editingCategory)
                    @method('PUT')
                @endif

                <div>
                    <label for="name" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1 font-medium">Category Name *</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $editingCategory->name ?? '') }}" required
                        class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]"
                        placeholder="e.g. Threat Intelligence">
                </div>

                <div>
                    <label for="parent_id" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1 font-medium">
                        Parent Category (Hierarchy)
                    </label>
                    <select id="parent_id" name="parent_id"
                        class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                        <option value="">— None (Top-Level Main Category) —</option>
                        @foreach ($parentCategories as $parent)
                            <option value="{{ $parent->id }}" {{ old('parent_id', $editingCategory->parent_id ?? '') == $parent->id ? 'selected' : '' }}>
                                [Main] {{ $parent->name }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-[10px] text-[#808080] font-mono mt-1">
                        Select a parent to nest this as a subcategory, or leave blank to create a main category.
                    </p>
                </div>

                <div>
                    <label for="slug" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1">Custom Slug (Optional)</label>
                    <input type="text" id="slug" name="slug" value="{{ old('slug', $editingCategory->slug ?? '') }}"
                        class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]"
                        placeholder="e.g. threat-intelligence">
                </div>

                <div>
                    <label for="description" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1">Description</label>
                    <textarea id="description" name="description" rows="3"
                        class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]"
                        placeholder="Summary displayed on category archive pages...">{{ old('description', $editingCategory->description ?? '') }}</textarea>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="order" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1">Display Order</label>
                        <input type="number" id="order" name="order" value="{{ old('order', $editingCategory->order ?? 0) }}"
                            class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                    </div>

                    <div class="pt-6">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $editingCategory->is_active ?? true) ? 'checked' : '' }} class="w-4 h-4 rounded-none border-[#111111] text-[#111111]">
                            <span class="text-xs font-mono text-[#111111]">Active Status</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label for="meta_title" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1">Meta Title</label>
                    <input type="text" id="meta_title" name="meta_title" value="{{ old('meta_title', $editingCategory->meta_title ?? '') }}"
                        class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">
                </div>

                <div>
                    <label for="meta_description" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-1">Meta Description</label>
                    <textarea id="meta_description" name="meta_description" rows="2"
                        class="w-full px-3.5 py-2 text-xs font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111]">{{ old('meta_description', $editingCategory->meta_description ?? '') }}</textarea>
                </div>

                <div class="pt-2">
                    <button type="submit" class="btn-primary w-full text-xs font-mono uppercase tracking-wider py-2.5">
                        {{ $editingCategory ? 'Update Category →' : 'Save Category →' }}
                    </button>
                </div>
            </form>
        </div>

        {{-- Right: Categories Tree Hierarchy Table --}}
        <div class="lg:col-span-8 bg-white border border-[#111111]/15 p-6">
            <div class="flex items-center justify-between border-b border-[#111111]/10 pb-3 mb-4">
                <h3 class="font-bold text-sm text-[#111111]">Category Architecture Tree</h3>
                <span class="text-xs font-mono text-[#808080]">Main Categories & Nested Subcategories</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs font-mono">
                    <thead>
                        <tr class="border-b border-[#111111]/15 text-[#808080] uppercase tracking-wider text-[10px]">
                            <th class="py-2.5 w-12">Order</th>
                            <th class="py-2.5">Category Name & Slug</th>
                            <th class="py-2.5">Hierarchy Tier</th>
                            <th class="py-2.5 text-center">Articles</th>
                            <th class="py-2.5">Status</th>
                            <th class="py-2.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#111111]/10">
                        @forelse ($categories as $cat)
                            @php
                                $isSub = !is_null($cat->parent_id);
                                $isEdited = $editingCategory && $editingCategory->id === $cat->id;
                            @endphp
                            <tr class="transition-colors hover:bg-[#F5F1E8]/50 {{ $isEdited ? 'bg-[#F5F1E8]' : ($isSub ? 'bg-[#FCFCFA]' : 'bg-white') }}">
                                {{-- Order --}}
                                <td class="py-3 text-[#808080]">
                                    {{ $cat->order }}
                                </td>

                                {{-- Name & Slug with Tree Indentation --}}
                                <td class="py-3 font-medium text-[#111111]">
                                    @if ($isSub)
                                        <div class="flex items-start pl-6 gap-2">
                                            <span class="text-[#808080] font-mono select-none">└──</span>
                                            <div>
                                                <div class="font-bold text-[#111111] flex items-center gap-1.5">
                                                    <span>{{ $cat->name }}</span>
                                                </div>
                                                <div class="text-[10px] text-[#808080]">/category/{{ $cat->slug }}</div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="flex items-center gap-2">
                                            <span class="w-2.5 h-2.5 bg-[#111111] inline-block flex-shrink-0"></span>
                                            <div>
                                                <div class="font-bold text-sm text-[#111111]">{{ $cat->name }}</div>
                                                <div class="text-[10px] text-[#808080]">/category/{{ $cat->slug }}</div>
                                            </div>
                                        </div>
                                    @endif
                                </td>

                                {{-- Hierarchy Tier Badge --}}
                                <td class="py-3">
                                    @if ($isSub)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 text-[10px] font-mono border border-blue-200 bg-blue-50 text-blue-800">
                                            <span>Subcategory</span>
                                            @if($cat->parent)
                                                <span class="text-blue-500 font-normal">of {{ $cat->parent->name }}</span>
                                            @endif
                                        </span>
                                    @else
                                        @php
                                            $childrenCount = $cat->children->count();
                                        @endphp
                                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 text-[10px] font-mono uppercase font-bold border border-[#111111]/20 bg-[#F5F1E8] text-[#111111]">
                                            <span>Main Category</span>
                                            @if($childrenCount > 0)
                                                <span class="px-1.5 py-0.2 bg-[#111111] text-white text-[9px]">{{ $childrenCount }} sub</span>
                                            @endif
                                        </span>
                                    @endif
                                </td>

                                {{-- Articles Count --}}
                                <td class="py-3 text-center text-[#808080]">
                                    <span class="font-bold text-[#111111]">{{ $cat->articles_count ?? $cat->articles()->count() }}</span>
                                </td>

                                {{-- Status --}}
                                <td class="py-3">
                                    <span class="px-2 py-0.5 text-[10px] uppercase font-bold {{ $cat->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-neutral/20 text-[#808080]' }}">
                                        {{ $cat->is_active ? 'Active' : 'Disabled' }}
                                    </span>
                                </td>

                                {{-- Actions --}}
                                <td class="py-3 text-right space-x-2">
                                    <a href="{{ route('admin.categories.index', ['edit' => $cat->id]) }}" class="text-[#111111] font-bold hover:underline">Edit</a>
                                    <span>•</span>
                                    <a href="{{ route('category.show', $cat->slug) }}" target="_blank" class="text-[#808080] hover:text-[#111111]">View</a>
                                    <span>•</span>
                                    <form action="{{ route('admin.categories.destroy', $cat->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this category?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-rose-600 hover:underline">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-[#808080]">
                                    No categories defined yet. Use the form on the left to add your first main category.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection
