<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        $query = Article::with(['category', 'author', 'seoMeta'])->latest('created_at');

        $user = auth()->user();
        if (!$user->can('publish articles') && !$user->hasRole(['Super Admin', 'super-admin', 'Admin', 'Editor'])) {
            $query->where('author_id', $user->id);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('content', 'LIKE', "%{$search}%");
            });
        }

        $articles = $query->paginate(15)->withQueryString();
        $categories = Category::all();

        return view('admin.articles.index', compact('articles', 'categories'));
    }

    public function create()
    {
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $tags = Tag::orderBy('name')->get();
        $potentialMasters = Article::whereNull('master_article_id')->orderBy('title')->get();

        return view('admin.articles.create', compact('categories', 'tags', 'potentialMasters'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:articles,slug',
            'category_id' => 'required|exists:categories,id',
            'content' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'featured_image' => 'nullable|string|max:500',
            'featured_image_file' => 'nullable|image|max:4096',
            'status' => 'required|in:draft,scheduled,published',
            'is_featured' => 'boolean',
            'published_at' => 'nullable|date',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
            // Geo-Targeting & Country Restriction (PRD-ADDNEW 5.1)
            'country_rule' => 'nullable|in:all,include,exclude',
            'countries' => 'nullable|array',
            'countries.*' => 'string|size:2',
            'restriction_fallback_message' => 'nullable|string|max:1000',
            'master_article_id' => 'nullable|exists:articles,id',
            'variant_country' => 'nullable|string|size:2',
            // SEO Meta
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'focus_keyword' => 'nullable|string|max:100',
            'canonical_url' => 'nullable|url|max:255',
            'robots' => 'nullable|string|max:50',
        ]);

        $slug = !empty($validated['slug']) ? Str::slug($validated['slug']) : Str::slug($validated['title']);

        // Handle File upload if provided
        $featuredImage = $validated['featured_image'] ?? null;
        if ($request->hasFile('featured_image_file')) {
            $path = $request->file('featured_image_file')->store('uploads/articles', 'public');
            $featuredImage = Storage::url($path);
        }

        // Non-publishers (e.g. Authors) can only create drafts
        if (!auth()->user()->can('publish articles') && !auth()->user()->hasRole(['Super Admin', 'super-admin', 'Admin', 'Editor'])) {
            $validated['status'] = 'draft';
        }

        $publishedAt = null;
        if ($validated['status'] === 'published') {
            $publishedAt = !empty($validated['published_at']) ? $validated['published_at'] : now();
        } elseif ($validated['status'] === 'scheduled') {
            $publishedAt = $validated['published_at'];
        }

        $readingTime = Article::calculateReadingTime($validated['content']);

        $article = Article::create([
            'title' => $validated['title'],
            'slug' => $slug,
            'category_id' => $validated['category_id'],
            'author_id' => auth()->id(),
            'content' => $validated['content'],
            'excerpt' => $validated['excerpt'] ?? null,
            'featured_image' => $featuredImage,
            'status' => $validated['status'],
            'country_rule' => $validated['country_rule'] ?? 'all',
            'countries' => !empty($validated['countries']) ? array_values(array_unique($validated['countries'])) : null,
            'restriction_fallback_message' => $validated['restriction_fallback_message'] ?? null,
            'master_article_id' => $validated['master_article_id'] ?? null,
            'variant_country' => !empty($validated['variant_country']) ? strtoupper(trim($validated['variant_country'])) : null,
            'is_featured' => $request->has('is_featured'),
            'reading_time' => $readingTime,
            'published_at' => $publishedAt,
        ]);

        if (!empty($validated['tags'])) {
            $article->tags()->sync($validated['tags']);
        }

        // On-Page SEO Meta
        $article->seoMeta()->create([
            'meta_title' => $validated['meta_title'] ?? ($article->title . ' | ' . config('app.name')),
            'meta_description' => $validated['meta_description'] ?? $article->excerpt,
            'focus_keyword' => $validated['focus_keyword'] ?? null,
            'canonical_url' => $validated['canonical_url'] ?? url('/article/' . $article->slug),
            'og_image' => $article->featured_image,
            'robots' => $validated['robots'] ?? 'index,follow',
        ]);

        return redirect()->route('admin.articles.index')->with('success', 'Article successfully created.');
    }

    public function edit(Article $article)
    {
        $user = auth()->user();
        if (!$user->can('publish articles') && !$user->hasRole(['Super Admin', 'super-admin', 'Admin', 'Editor'])) {
            if ($article->author_id !== $user->id) {
                abort(403, 'You are only authorized to edit your own articles.');
            }
        }

        $categories = Category::orderBy('name')->get();
        $tags = Tag::orderBy('name')->get();
        $potentialMasters = Article::whereNull('master_article_id')->where('id', '!=', $article->id)->orderBy('title')->get();
        $article->load(['tags', 'seoMeta']);

        return view('admin.articles.edit', compact('article', 'categories', 'tags', 'potentialMasters'));
    }

    public function update(Request $request, Article $article)
    {
        $user = auth()->user();
        if (!$user->can('publish articles') && !$user->hasRole(['Super Admin', 'super-admin', 'Admin', 'Editor'])) {
            if ($article->author_id !== $user->id) {
                abort(403, 'You are only authorized to update your own articles.');
            }
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:articles,slug,' . $article->id,
            'category_id' => 'required|exists:categories,id',
            'content' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'featured_image' => 'nullable|string|max:500',
            'featured_image_file' => 'nullable|image|max:4096',
            'status' => 'required|in:draft,scheduled,published',
            'is_featured' => 'boolean',
            'published_at' => 'nullable|date',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
            // Geo-Targeting & Country Restriction (PRD-ADDNEW 5.1)
            'country_rule' => 'nullable|in:all,include,exclude',
            'countries' => 'nullable|array',
            'countries.*' => 'string|size:2',
            'restriction_fallback_message' => 'nullable|string|max:1000',
            'master_article_id' => 'nullable|exists:articles,id',
            'variant_country' => 'nullable|string|size:2',
            // SEO Meta
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'focus_keyword' => 'nullable|string|max:100',
            'canonical_url' => 'nullable|url|max:255',
            'robots' => 'nullable|string|max:50',
        ]);

        $featuredImage = $article->featured_image;
        if ($request->hasFile('featured_image_file')) {
            $path = $request->file('featured_image_file')->store('uploads/articles', 'public');
            $featuredImage = Storage::url($path);
        } elseif (!empty($validated['featured_image'])) {
            $featuredImage = $validated['featured_image'];
        }

        // Non-publishers (e.g. Authors) can only save as draft
        if (!auth()->user()->can('publish articles') && !auth()->user()->hasRole(['Super Admin', 'super-admin', 'Admin', 'Editor'])) {
            $validated['status'] = 'draft';
        }

        $publishedAt = $article->published_at;
        if ($validated['status'] === 'published' && !$publishedAt) {
            $publishedAt = now();
        } elseif ($validated['status'] === 'scheduled') {
            $publishedAt = $validated['published_at'];
        }

        $readingTime = Article::calculateReadingTime($validated['content']);

        $article->update([
            'title' => $validated['title'],
            'slug' => Str::slug($validated['slug']),
            'category_id' => $validated['category_id'],
            'content' => $validated['content'],
            'excerpt' => $validated['excerpt'] ?? null,
            'featured_image' => $featuredImage,
            'status' => $validated['status'],
            'country_rule' => $validated['country_rule'] ?? 'all',
            'countries' => !empty($validated['countries']) ? array_values(array_unique($validated['countries'])) : null,
            'restriction_fallback_message' => $validated['restriction_fallback_message'] ?? null,
            'master_article_id' => $validated['master_article_id'] ?? null,
            'variant_country' => !empty($validated['variant_country']) ? strtoupper(trim($validated['variant_country'])) : null,
            'is_featured' => $request->has('is_featured'),
            'reading_time' => $readingTime,
            'published_at' => $publishedAt,
        ]);

        $article->tags()->sync($validated['tags'] ?? []);

        // Update SEO Meta
        $article->seoMeta()->updateOrCreate(
            [],
            [
                'meta_title' => $validated['meta_title'] ?? ($article->title . ' | ' . config('app.name')),
                'meta_description' => $validated['meta_description'] ?? $article->excerpt,
                'focus_keyword' => $validated['focus_keyword'] ?? null,
                'canonical_url' => $validated['canonical_url'] ?? url('/article/' . $article->slug),
                'og_image' => $article->featured_image,
                'robots' => $validated['robots'] ?? 'index,follow',
            ]
        );

        // Auto-purge CDN cache for updated article if enabled
        if (\App\Models\Setting::get('cdn_auto_purge') === '1') {
            try {
                $cdn = app(\App\Services\CloudflareCdnService::class);
                if ($cdn->isConfigured()) {
                    $cdn->purgeUrls([
                        url('/article/' . $article->slug),
                        url('/'),
                        route('feed.index'),
                    ]);
                }
            } catch (\Throwable $e) {
                // Silently ignore background purge failure to not disrupt editorial workflow
            }
        }

        return redirect()->route('admin.articles.index')->with('success', 'Article successfully updated.');
    }

    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'action' => 'required|in:delete,publish,draft',
            'selected_ids' => 'required|array',
            'selected_ids.*' => 'exists:articles,id',
        ]);

        $user = auth()->user();
        $canPublish = $user->can('publish articles') || $user->hasRole(['Super Admin', 'super-admin', 'Admin', 'Editor']);

        if ($validated['action'] === 'publish' && !$canPublish) {
            abort(403, 'You are not authorized to publish articles.');
        }

        $ids = $validated['selected_ids'];

        // Restrict authors to only their own articles
        if (!$canPublish) {
            $ids = Article::whereIn('id', $ids)->where('author_id', $user->id)->pluck('id')->toArray();
        }

        if (empty($ids)) {
            return back()->with('error', 'No eligible articles selected for this action.');
        }

        if ($validated['action'] === 'delete') {
            Article::whereIn('id', $ids)->delete();
            return back()->with('success', count($ids) . ' articles deleted.');
        }

        if ($validated['action'] === 'publish') {
            Article::whereIn('id', $ids)->update([
                'status' => 'published',
                'published_at' => now(),
            ]);
            return back()->with('success', count($ids) . ' articles published.');
        }

        if ($validated['action'] === 'draft') {
            Article::whereIn('id', $ids)->update([
                'status' => 'draft',
            ]);
            return back()->with('success', count($ids) . ' articles moved to draft.');
        }

        return back();
    }

    public function destroy(Article $article)
    {
        $user = auth()->user();
        if (!$user->can('publish articles') && !$user->hasRole(['Super Admin', 'super-admin', 'Admin', 'Editor'])) {
            if ($article->author_id !== $user->id) {
                abort(403, 'You are only authorized to delete your own articles.');
            }
        }

        $article->delete();
        return redirect()->route('admin.articles.index')->with('success', 'Article deleted.');
    }
}
